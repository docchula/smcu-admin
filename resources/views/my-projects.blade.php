@php
    /** @var \App\Models\User $user */
@endphp
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap">
<style>
    body {
        font-family: "Sarabun", sans-serif;
        font-weight: 400;
    }

    h3, h4 {
        margin: 0.5rem 0;
    }
</style>
<h2>ประวัติการเข้าร่วมกิจกรรม (Activity Transcript)</h2>
<h4>สโมสรนิสิตคณะแพทยศาสตร์ จุฬาลงกรณ์มหาวิทยาลัย</h4>
<p>
    <span style="margin-right: 4rem">{{ $user->name }}</span>
    เลขประจำตัวนิสิต&nbsp;{{ $user->student_id }}
</p>
<hr/>
<table style="width: 100%">
    <tr>
        <th>โครงการ</th>
        <th>บทบาท</th>
        <th>หน้าที่</th>
    </tr>
    @foreach($user->participantAndProjects() as $participant)
        <tr>
            <td>({{ $participant->project->year }}-{{ $participant->project->number }}) {{ $participant->project->name }}</td>
            <td>{{ ['organizer' => 'ผู้รับผิดชอบ', 'staff' => 'ผู้จัดกิจกรรม', 'attendee' => 'ผู้เข้าร่วม'][$participant->type] ?? $participant->type }}</td>
            <td>{{ $participant->title }}</td>
        </tr>
    @endforeach
</table>
<p style="margin-top:2rem; font-size:0.8rem; color:gray; text-align: right">พิมพ์เมื่อ {{ date('F j, Y') }}</p>
