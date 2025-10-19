<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent to project organizers when the faculty staff updates the remark on project closure.
 */
class ClosureApprovalNotification extends Notification implements ShouldQueue {
    use NotifyParticipantsTrait, Queueable;

    public function __construct(public Project $project) {
    }

    public function via($notifiable): array {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage {
        return (new MailMessage)
            ->subject('อนุมัติรายงานผล โครงการที่ '.$this->project->year.'-'.$this->project->number.' '.$this->project->name)
            ->greeting('เรียน  นิสิตผู้รับผิดชอบโครงการ')
            ->line('อาจารย์งานกิจการนิสิต ได้รายงานผล โครงการที่ '.$this->project->year.'-'.$this->project->number.' '.$this->project->name.' แล้ว')
            ->action('ดูข้อมูลโครงการเพิ่มเติม', route('projects.show', ['project' => $this->project->id]));
    }

    public function toArray($notifiable): array {
        return ['อนุมัติรายงานผล โครงการที่ '.$this->project->year.'-'.$this->project->number.' '.$this->project->name.' แล้ว!'];
    }

    /**
     * Determine the notification's delivery delay.
     *
     * @return array<string, \Illuminate\Support\Carbon>
     */
    public function withDelay(object $notifiable): array {
        return [
            'mail' => now()->addMinutes(5),
        ];
    }
}
