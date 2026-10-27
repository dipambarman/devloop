<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberAddedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Project $project,
        public User $actor,
        public string $role
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
            if ($notifiable->wantsNotification('member_added', 'database')) {
                $channels[] = 'database';
            }
            if ($notifiable->wantsNotification('member_added', 'mail')) {
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
        $url = route('projects.show', $this->project);

        return (new MailMessage)
            ->subject("[DevLoop] Added to: {$this->project->name}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->actor->name} added you as **{$this->role}** to project **{$this->project->name}**.")
            ->action('Open Project', $url)
            ->line('Welcome aboard! Let\'s build something great.');
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'member_added',
            'title' => 'Project Invitation',
            'message' => "{$this->actor->name} added you to project: {$this->project->name} ({$this->role})",
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'role' => $this->role,
            'url' => route('projects.show', $this->project),
            'actor_name' => $this->actor->name,
        ];
    }
}
