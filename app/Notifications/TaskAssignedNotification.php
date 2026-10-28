<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Task $task,
        public User $assigner
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
            if ($notifiable->wantsNotification('task_assigned', 'database')) {
                $channels[] = 'database';
            }
            if ($notifiable->wantsNotification('task_assigned', 'mail')) {
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
        $url = route('tasks.show', $this->task);
        $projectName = $this->task->project->name;

        $mail = (new MailMessage)
            ->subject("[DevLoop] Assigned: {$this->task->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->assigner->name} has assigned you to a task in **{$projectName}**.")
            ->line("**Task:** {$this->task->title}");

        if ($this->task->priority) {
            $priorityValue = is_object($this->task->priority) ? $this->task->priority->value : $this->task->priority;
            $mail->line('**Priority:** '.ucfirst($priorityValue));
        }

        if ($this->task->due_date) {
            $mail->line('**Due Date:** '.$this->task->due_date->format('M d, Y'));
        }

        return $mail
            ->action('View Task', $url)
            ->line('Thank you for collaborating on DevLoop!');
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_assigned',
            'title' => 'Task Assigned',
            'message' => "{$this->assigner->name} assigned you to task: {$this->task->title}",
            'task_id' => $this->task->id,
            'project_id' => $this->task->project_id,
            'project_name' => $this->task->project->name,
            'url' => route('tasks.show', $this->task),
            'actor_name' => $this->assigner->name,
        ];
    }
}
