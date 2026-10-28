/**
 * DevLoop Client Error Listener & Telemetry SDK
 * Ultra-lightweight, zero-dependency browser error tracker and ingest client.
 * 
 * Usage 1 (Auto-init via script tag):
 *   <script src="/js/devloop-tracker.js" data-key="YOUR_KEY" data-environment="production"></script>
 * 
 * Usage 2 (Programmatic):
 *   DevLoop.init({ key: 'YOUR_KEY', endpoint: '/api/v1/ingest', environment: 'production' });
 */
(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define([], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = factory();
    } else {
        root.DevLoop = factory();
        root.DevLoopTracker = root.DevLoop;
    }
}(typeof self !== 'undefined' ? self : this, function () {
    'use strict';

    var config = {
        key: null,
        endpoint: '/api/v1/ingest',
        environment: 'production',
        release: null,
        debug: false,
        maxQueueSize: 20,
        flushInterval: 3000,
        async: true,
        user: null,
        context: {},
        breadcrumbs: []
    };

    var queue = [];
    var recentErrors = {};
    var flushTimer = null;
    var isInitialized = false;

    function log(msg, extra) {
        if (config.debug && typeof console !== 'undefined' && console.log) {
            console.log('[DevLoop Tracker] ' + msg, extra || '');
        }
    }

    function generateSimpleHash(str) {
        var hash = 0;
        for (var i = 0; i < str.length; i++) {
            hash = ((hash << 5) - hash) + str.charCodeAt(i);
            hash |= 0;
        }
        return String(hash);
    }

    function addBreadcrumb(category, message, data) {
        try {
            config.breadcrumbs.push({
                timestamp: new Date().toISOString(),
                category: category || 'general',
                message: message,
                data: data || null
            });
            if (config.breadcrumbs.length > 15) {
                config.breadcrumbs.shift();
            }
        } catch (e) {}
    }

    function sendPayload(payloadList, useBeacon) {
        if (!config.key || payloadList.length === 0) return;

        var url = config.endpoint;
        if (url.indexOf('?') === -1) {
            url += '?key=' + encodeURIComponent(config.key);
        } else {
            url += '&key=' + encodeURIComponent(config.key);
        }

        var bodyData = JSON.stringify(
            payloadList.length === 1 ? payloadList[0] : { events: payloadList }
        );

        // Try sendBeacon on unload/pagehide if supported
        if (useBeacon && typeof navigator !== 'undefined' && typeof navigator.sendBeacon === 'function') {
            try {
                var blob = new Blob([bodyData], { type: 'application/json' });
                var success = navigator.sendBeacon(url, blob);
                if (success) {
                    log('Sent via sendBeacon', payloadList.length);
                    return;
                }
            } catch (e) {
                log('sendBeacon failed, falling back to fetch', e);
            }
        }

        // Standard fetch
        if (typeof fetch === 'function') {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-DevLoop-Key': config.key,
                    'X-DevLoop-Async': config.async ? '1' : '0'
                },
                body: bodyData,
                keepalive: true
            }).then(function (res) {
                log('Payload dispatched. Status:', res.status);
            }).catch(function (err) {
                log('Dispatch error:', err);
            });
        } else {
            // XMLHttpRequest fallback
            try {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', url, true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.setRequestHeader('X-DevLoop-Key', config.key);
                xhr.setRequestHeader('X-DevLoop-Async', config.async ? '1' : '0');
                xhr.send(bodyData);
            } catch (e) {}
        }
    }

    function flush(useBeacon) {
        if (queue.length === 0) return;
        var toSend = queue.splice(0, config.maxQueueSize);
        sendPayload(toSend, useBeacon);
    }

    function scheduleFlush() {
        if (flushTimer) return;
        flushTimer = setTimeout(function () {
            flushTimer = null;
            flush(false);
        }, config.flushInterval);
    }

    function recordEvent(eventData) {
        if (!config.key) {
            log('Skipping event: DevLoop is not configured with an Ingest API Key.');
            return;
        }

        // De-duplication check: suppress rapid repeated crashes within 4 seconds
        var sig = generateSimpleHash((eventData.message || '') + (eventData.stack || ''));
        var now = Date.now();
        if (recentErrors[sig] && (now - recentErrors[sig]) < 4000) {
            log('Suppressed duplicate event within debounce threshold:', eventData.message);
            return;
        }
        recentErrors[sig] = now;

        // Auto-enrich payload with browser telemetry
        var enriched = {
            message: String(eventData.message || 'Unknown Error').substring(0, 5000),
            stack: eventData.stack ? String(eventData.stack).substring(0, 50000) : null,
            severity: eventData.severity || 'high',
            environment: config.environment || 'production',
            url: eventData.url || (typeof window !== 'undefined' ? window.location.href : null),
            user_agent: typeof navigator !== 'undefined' ? navigator.userAgent : null,
            handled: !!eventData.handled,
            context: Object.assign({}, config.context, {
                release: config.release || null,
                user: config.user || null,
                viewport: (typeof window !== 'undefined') ? (window.innerWidth + 'x' + window.innerHeight) : null,
                breadcrumbs: config.breadcrumbs.slice(-10)
            }, eventData.context || {})
        };

        queue.push(enriched);
        log('Queued event:', enriched.message);

        if (queue.length >= config.maxQueueSize) {
            flush(false);
        } else {
            scheduleFlush();
        }
    }

    function hookGlobalListeners() {
        if (typeof window === 'undefined') return;

        // Uncaught Runtime Errors
        window.addEventListener('error', function (event) {
            try {
                var message = event.message || (event.error && event.error.message) || 'Script Error';
                var stack = event.error ? (event.error.stack || null) : null;

                if (!stack && event.filename) {
                    stack = 'at ' + event.filename + ':' + (event.lineno || 0) + ':' + (event.colno || 0);
                }

                recordEvent({
                    message: message,
                    stack: stack,
                    severity: 'high',
                    handled: false
                });
            } catch (e) {}
        });

        // Unhandled Promise Rejections (e.g. async/await or failed fetch)
        window.addEventListener('unhandledrejection', function (event) {
            try {
                var reason = event.reason;
                var message = 'Unhandled Promise Rejection';
                var stack = null;

                if (reason instanceof Error) {
                    message = reason.message || message;
                    stack = reason.stack;
                } else if (typeof reason === 'string') {
                    message = reason;
                } else if (reason && typeof reason === 'object') {
                    try {
                        message = JSON.stringify(reason);
                    } catch (e) {
                        message = String(reason);
                    }
                }

                recordEvent({
                    message: message,
                    stack: stack,
                    severity: 'critical',
                    handled: false,
                    context: {
                        rejection_type: typeof reason
                    }
                });
            } catch (e) {}
        });

        // Flush on tab close / reload
        window.addEventListener('pagehide', function () {
            flush(true);
        });
        window.addEventListener('beforeunload', function () {
            flush(true);
        });
    }

    var DevLoop = {
        init: function (options) {
            options = options || {};
            for (var key in options) {
                if (options.hasOwnProperty(key)) {
                    config[key] = options[key];
                }
            }

            if (!isInitialized) {
                hookGlobalListeners();
                isInitialized = true;
                log('Initialized for environment:', config.environment);
            }

            return DevLoop;
        },

        captureException: function (err, options) {
            options = options || {};
            var message = 'Captured Error';
            var stack = null;

            if (err instanceof Error) {
                message = err.message || err.name || message;
                stack = err.stack;
            } else if (typeof err === 'string') {
                message = err;
            } else if (err && typeof err === 'object') {
                try {
                    message = JSON.stringify(err);
                } catch (e) {
                    message = String(err);
                }
            }

            recordEvent({
                message: message,
                stack: stack,
                severity: options.severity || 'high',
                handled: true,
                context: options.context || {}
            });
        },

        captureMessage: function (message, severity, context) {
            recordEvent({
                message: String(message),
                stack: null,
                severity: severity || 'medium',
                handled: true,
                context: context || {}
            });
        },

        setUser: function (user) {
            config.user = user;
            return DevLoop;
        },

        setContext: function (key, value) {
            config.context[key] = value;
            return DevLoop;
        },

        addBreadcrumb: function (category, message, data) {
            addBreadcrumb(category, message, data);
            return DevLoop;
        },

        flush: function () {
            flush(false);
        },

        getConfig: function () {
            return Object.assign({}, config);
        }
    };

    // Auto-initialize if loaded via <script> tag with attributes
    if (typeof document !== 'undefined') {
        var currentScript = document.currentScript || (function () {
            var scripts = document.getElementsByTagName('script');
            return scripts[scripts.length - 1];
        })();

        if (currentScript) {
            var keyAttr = currentScript.getAttribute('data-key');
            var endpointAttr = currentScript.getAttribute('data-endpoint');
            var envAttr = currentScript.getAttribute('data-environment');
            var releaseAttr = currentScript.getAttribute('data-release');

            if (keyAttr) {
                DevLoop.init({
                    key: keyAttr,
                    endpoint: endpointAttr || '/api/v1/ingest',
                    environment: envAttr || 'production',
                    release: releaseAttr || null
                });
            }
        }
    }

    return DevLoop;
}));
