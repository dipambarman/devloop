<?php

namespace App\Services;

class ErrorFingerprintService
{
    /**
     * Regexes applied in order to normalize error message.
     */
    protected const UUID_RE = '/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i';

    protected const HEX_0X_RE = '/\b0x[0-9a-f]+\b/i';

    protected const HEX_LONG_RE = '/\b(?=[0-9a-f]*[0-9])[0-9a-f]{8,}\b/i';

    protected const LINE_COL_RE = '/\b\d+:\d+\b/';

    protected const INTEGER_RE = '/\b\d+\b/';

    protected const WS_RE = '/\s+/';

    /**
     * Normalize an error message by stripping volatile tokens
     * (UUIDs, hex addresses, line/col numbers, standalone numbers).
     */
    public function normalizeMessage(?string $raw): string
    {
        $msg = $raw ?? '';
        $msg = preg_replace(self::UUID_RE, '', $msg);
        $msg = preg_replace(self::HEX_0X_RE, '', $msg);
        $msg = preg_replace(self::HEX_LONG_RE, '', $msg);
        $msg = preg_replace(self::LINE_COL_RE, '', $msg);
        $msg = preg_replace(self::INTEGER_RE, '', $msg);
        $msg = trim(preg_replace(self::WS_RE, ' ', $msg));

        return $msg;
    }

    /**
     * Extract error type from message or exception name.
     * Examples:
     * - "TypeError: Cannot read properties" -> "TypeError"
     * - "Illuminate\Database\QueryException: SQLSTATE[42S02]" -> "QueryException"
     */
    public function extractErrorType(?string $message): string
    {
        if (empty($message)) {
            return 'UnknownError';
        }

        // Match PHP namespaced exceptions: e.g. App\Exceptions\PaymentException
        if (preg_match('/([A-Za-z0-9_]+Error|[A-Za-z0-9_]+Exception)\b/', $message, $matches)) {
            return $matches[1];
        }

        return 'UnknownError';
    }

    /**
     * Extract the file basename handling both Unix and Windows path separators.
     */
    public function fileBasename(string $filePath): string
    {
        $parts = preg_split('#[/\\\\]#', $filePath);

        return end($parts) ?: $filePath;
    }

    /**
     * Normalize function / method name.
     */
    protected function cleanFunc(?string $name): string
    {
        if (empty($name) || in_array($name, ['<anonymous>', 'Object.<anonymous>', '{main}'])) {
            return 'anonymous';
        }

        $cleaned = preg_replace('/^Object\./', '', $name);

        return trim($cleaned);
    }

    /**
     * Parse the first recognizable stack frame from a stack trace string.
     * Supports Node.js V8, Firefox/Safari, and PHP trace formats.
     * Returns "function@basename" or "anonymous".
     */
    public function parseTopFrame(?string $rawStack): string
    {
        if (empty($rawStack)) {
            return 'anonymous';
        }

        $lines = explode("\n", $rawStack);

        foreach ($lines as $line) {
            $t = trim($line);
            if (empty($t)) {
                continue;
            }

            // Node/V8 with function: "at funcName (/path/to/file.js:42:13)"
            if (preg_match('/^at\s+(.+?)\s+\((.+?)(?::\d+(?::\d+)?)?\)$/', $t, $m)) {
                $fn = $this->cleanFunc($m[1]);
                $file = $this->fileBasename($m[2]);

                return ($fn === 'anonymous' && $file === 'anonymous') ? 'anonymous' : "{$fn}@{$file}";
            }

            // Node/V8 anonymous: "at /path/to/file.js:42:13"
            if (preg_match('/^at\s+([^(]+?)(?::\d+(?::\d+)?)?$/', $t, $m)) {
                $file = $this->fileBasename(trim($m[1]));

                return ($file === 'anonymous' || $file === '<anonymous>') ? 'anonymous' : "anonymous@{$file}";
            }

            // Firefox/Safari: "funcName@/path/to/file.js:42:13"
            if (preg_match('/^(.+?)@(.+?)(?::\d+(?::\d+)?)?$/', $t, $m)) {
                $fn = $this->cleanFunc($m[1]);
                $file = $this->fileBasename($m[2]);

                return ($fn === 'anonymous' && $file === 'anonymous') ? 'anonymous' : "{$fn}@{$file}";
            }

            // PHP Stack Trace: "#0 /app/Services/OrderService.php(42): App\Services\PaymentGateway->charge()"
            if (preg_match('/^#\d+\s+(.+?)(?:\(\d+\))?:\s+(.+)$/', $t, $m)) {
                $file = $this->fileBasename(trim($m[1]));
                $fn = $this->cleanFunc(trim($m[2]));

                return "{$fn}@{$file}";
            }
        }

        return 'anonymous';
    }

    /**
     * Compute a deterministic 16-hex-char SHA-1 fingerprint for an error.
     *
     * Canonical input: "{normalizedMessage}|{errorType}|{topFrame}"
     */
    public function computeFingerprint(string $rawMessage, ?string $errorType = null, ?string $rawStack = null): string
    {
        $type = $errorType ?: $this->extractErrorType($rawMessage);
        $topFrame = $this->parseTopFrame($rawStack);
        $normalizedMsg = $this->normalizeMessage($rawMessage);

        $canonical = "{$normalizedMsg}|{$type}|{$topFrame}";

        return substr(sha1($canonical), 0, 16);
    }
}
