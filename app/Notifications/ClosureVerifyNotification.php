<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClosureVerifyNotification extends Notification implements ShouldQueue {
    use Queueable;

    public function __construct(public Project $project) {
    }

    public function via(): array {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage {
        return (new MailMessage)
            ->subject('กรุณารับรองรายชื่อนิสิต โครงการที่ '.$this->project->year.'-'.$this->project->number.' '.$this->project->name)
            ->greeting('เรียน '.$notifiable->name)
            ->line('กรุณารับรองรายชื่อนิสิตผู้เกี่ยวข้อง ของโครงการที่ '.$this->project->year.'-'.$this->project->number.' '.$this->project->name)
            ->line('โครงการที่จะบันทึกเป็นส่วนหนึ่งของ Activity Transcript ต้องผ่านการรับรองรายชื่อนิสิตผู้เกี่ยวข้อง โดยนิสิตผู้รับผิดชอบและผู้ปฏิบัติงานทุกคนภายใน 60 วัน นับจากสิ้นสุดกิจกรรม ('.$this->project->period_end->format('j M Y').')')
            ->action('รับรองรายชื่อนิสิตผู้เกี่ยวข้อง', route('projects.closureVerifyForm', ['project' => $this->project->id]));
    }

    public function toArray(): array {
        return ['กรุณารับรองรายชื่อนิสิต โครงการที่ '.$this->project->year.'-'.$this->project->number.' '.$this->project->name];
    }
}
