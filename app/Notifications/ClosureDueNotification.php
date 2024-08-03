<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\ProjectParticipant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClosureDueNotification extends Notification implements ShouldQueue {
    use Queueable;

    public function __construct(public Project $project, public ProjectParticipant $participant) {
    }

    public function via($notifiable): array {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage {
        return (new MailMessage)
            ->subject('กรุณาบันทึกรายงานผล โครงการที่ '.$this->project->year.'-'.$this->project->number.' '.$this->project->name)
            ->greeting('ได้เวลาบันทึกรายงานผล โครงการที่ '.$this->project->year.'-'.$this->project->number.' '.$this->project->name.' แล้ว!')
            ->line('เรียน นิสิตผู้รับผิดชอบโครงการ')
            ->line('เมื่อเสร็จสิ้นโครงการแล้ว ให้รายงานผลการดำเนินโครงการ และส่งเบิกค่าใช้จ่าย (ถ้ามี) ให้เรียบร้อยโดยเร็ว')
            ->lineIf($this->participant->type != 'organizer', 'คุณไม่ใช่ผู้รับผิดชอบโครงการ กรุณาแจ้งนิสิตผู้รับผิดชอบโครงการให้ดำเนินการต่อไป')
            ->action('ดูข้อมูลโครงการเพิ่มเติม', route('projects.show', ['project' => $this->project->id]))
            ->line('โครงการที่จะบันทึกเป็นส่วนหนึ่งของ Activity Transcript (Student Profile) ต้องบันทึกรายงานผลการดำเนินโครงการ และกดยืนยันการส่ง ภายใน 30 วัน
นับจากวันที่สิ้นสุดกิจกรรม ('.$this->project->period_end->format('j M Y').')');
    }

    public function toArray($notifiable): array {
        return ['ได้เวลาบันทึกรายงานผล โครงการที่ '.$this->project->year.'-'.$this->project->number.' '.$this->project->name.' แล้ว!'];
    }
}
