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
class ClosureRemarkNotification extends Notification implements ShouldQueue {
    use Queueable;

    public function __construct(public Project $project, public string $remark) {
    }

    public function via($notifiable): array {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage {
        return (new MailMessage)
            ->subject('มีเพิ่มเติมหมายเหตุในการพิจารณาอนุมัติรายงานผล โครงการที่ '.$this->project->year.'-'.$this->project->number.' '.$this->project->name)
            ->greeting('เรียน  นิสิตผู้รับผิดชอบโครงการ')
            ->line('อาจารย์ฝ่ายกิจการนิสิต ได้เพิ่มเติมหมายเหตุในการพิจารณาอนุมัติรายงานผล โครงการที่ '.$this->project->year.'-'.$this->project->number.' '.$this->project->name.' ดังนี้')
            ->line('"'.$this->project->closure_approved_message.'"')
            ->action('ดูข้อมูลโครงการเพิ่มเติม', route('projects.show', ['project' => $this->project->id]));
    }

    public function toArray($notifiable): array {
        return ['มีเพิ่มเติมหมายเหตุในการพิจารณา โครงการที่ '.$this->project->year.'-'.$this->project->number.' '.$this->project->name.' แล้ว!'];
    }

    /**
     * Determine the notification's delivery delay.
     *
     * @return array<string, \Illuminate\Support\Carbon>
     */
    public function withDelay(object $notifiable): array {
        return [
            'mail' => now()->addMinutes(10),
        ];
    }

    /**
     * Determine if the notification should be sent.
     */
    public function shouldSend(object $notifiable, string $channel): bool {
        // Do not send notification if the remark is edited after the notification is queued.
        return $this->project->closure_approved_message == $this->remark;
    }
}
