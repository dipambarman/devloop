<section>
    <header>
        <h2 class="text-lg font-semibold text-primary-text">
            {{ __('Notification Preferences') }}
        </h2>
        <p class="mt-1 text-sm text-secondary-text">
            {{ __('Choose how you receive alerts across in-app activity feeds and email notifications.') }}
        </p>
    </header>

    @php
        $prefs = $user->getNotificationPreferences();
        $notificationTypes = [
            'task_assigned' => [
                'title' => 'Task Assignments',
                'desc' => 'Alerts when someone assigns you to a task or updates assignment.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />'
            ],
            'comment_added' => [
                'title' => 'Task Comments',
                'desc' => 'Alerts when someone comments on tasks you created or are assigned to.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />'
            ],
            'discussion_replied' => [
                'title' => 'Discussion Replies',
                'desc' => 'Alerts when teammates respond to discussions you started.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />'
            ],
            'member_added' => [
                'title' => 'Project Invitations',
                'desc' => 'Alerts when you are added or invited to a new project team.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />'
            ],
        ];
    @endphp

    <form method="post" action="{{ route('profile.notifications.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div class="divide-y divide-border rounded-xl border border-border bg-background/50 overflow-hidden">
            @foreach($notificationTypes as $type => $info)
                <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-surface/30 transition-colors">
                    <div class="flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-lg bg-surface border border-border flex items-center justify-center text-primary shrink-0 mt-0.5">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                {!! $info['icon'] !!}
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-primary-text">{{ $info['title'] }}</h4>
                            <p class="text-xs text-secondary-text mt-0.5">{{ $info['desc'] }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-6 self-end sm:self-auto pl-12 sm:pl-0">
                        <!-- In-App Notification Toggle -->
                        <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-medium text-secondary-text hover:text-primary-text transition-colors">
                            <input type="checkbox"
                                   name="preferences[{{ $type }}][database]"
                                   value="1"
                                   {{ !empty($prefs[$type]['database']) ? 'checked' : '' }}
                                   class="rounded border-border bg-background text-primary focus:ring-primary h-4 w-4 transition-colors">
                            <span>In-App</span>
                        </label>

                        <!-- Email Notification Toggle -->
                        <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-medium text-secondary-text hover:text-primary-text transition-colors">
                            <input type="checkbox"
                                   name="preferences[{{ $type }}][mail]"
                                   value="1"
                                   {{ !empty($prefs[$type]['mail']) ? 'checked' : '' }}
                                   class="rounded border-border bg-background text-primary focus:ring-primary h-4 w-4 transition-colors">
                            <span>Email</span>
                        </label>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save Preferences') }}</x-primary-button>

            @if (session('status') === 'notifications-updated')
                <p x-data="{ show: true }"
                   x-show="show"
                   x-transition
                   x-init="setTimeout(() => show = false, 2500)"
                   class="text-sm text-success font-medium">
                    {{ __('Preferences saved successfully.') }}
                </p>
            @endif
        </div>
    </form>
</section>
