<?php

namespace App\Notifications;

use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class DiscussionRepliedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Discussion $discussion,
        public DiscussionReply $reply,
        public User $replier
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if (method_exists($notifiable, 'wantsNotification')) {
            if ($notifiable->wantsNotification('discussion_replied', 'database')) {
                $channels[] = 'database';
            }
            if ($notifiable->wantsNotification('discussion_replied', 'mail')) {
                $channels[] = 'mail';
            }
        } else {
            $channels = ['database', 'mail'];
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $project = $this->discussion->project;
        $url = route('projects.discussions.show', [$project, $this->discussion]);

        return (new MailMessage)
            ->subject("[DevLoop] Reply to: {$this->discussion->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->replier->name} replied to your discussion in **{$project->name}**:")
            ->line('"' . Str::limit($this->reply->content, 250) . '"')
            ->action('View Discussion', $url)
            ->line('Join the conversation on DevLoop!');
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $project = $this->discussion->project;

        return [
            'type' => 'discussion_replied',
            'title' => 'Discussion Reply',
            'message' => "{$this->replier->name} replied to: {$this->discussion->title}",
            'discussion_id' => $this->discussion->id,
            'reply_id' => $this->reply->id,
            'project_id' => $project->id,
            'snippet' => Str::limit($this->reply->content, 80),
            'url' => route('projects.discussions.show', [$project, $this->discussion]),
            'actor_name' => $this->replier->name,
        ];
    }
}
