<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class CommentAddedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Comment $comment,
        public User $commenter
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
            if ($notifiable->wantsNotification('comment_added', 'database')) {
                $channels[] = 'database';
            }
            if ($notifiable->wantsNotification('comment_added', 'mail')) {
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
        $task = $this->comment->task;
        $url = route('tasks.show', $task);

        return (new MailMessage)
            ->subject("[DevLoop] Comment on: {$task->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->commenter->name} posted a comment on **{$task->title}**:")
            ->line('"'.Str::limit($this->comment->content, 250).'"')
            ->action('View Comment', $url)
            ->line('Keep up the great collaboration on DevLoop!');
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $task = $this->comment->task;

        return [
            'type' => 'comment_added',
            'title' => 'New Comment',
            'message' => "{$this->commenter->name} commented on task: {$task->title}",
            'task_id' => $task->id,
            'comment_id' => $this->comment->id,
            'snippet' => Str::limit($this->comment->content, 80),
            'url' => route('tasks.show', $task),
            'actor_name' => $this->commenter->name,
        ];
    }
}
