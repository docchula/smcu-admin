@php    /** @var \App\Models\User $user */ @endphp
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap">
<style>
    body {
        font-family: "Sarabun", sans-serif;
        font-weight: 400;
    }

    th, td {
        vertical-align: top;
        word-break: break-all;
    }
</style>
<table style="width: 100%">
    <tr>
        @if(empty($draft))
            <td style="vertical-align: middle;text-align: center">
                <img src="/phrakiao.jpg" alt="Logo" style="width: 50px"/>
            </td>
        @endif
        <td>
            @if(!empty($draft))
                <h2 style="color:red">ร่าง</h2>
            @endif
            <h3>ระเบียนประวัติการเข้าร่วมกิจกรรมนอกหลักสูตร</h3>
            <p>คณะแพทยศาสตร์ จุฬาลงกรณ์มหาวิทยาลัย</p>
            <p>
                <span style="margin-right: 4rem">{{ $user->name }}</span>
                เลขประจำตัวนิสิต&nbsp;{{ $user->student_id }}
            </p>
        </td>
    </tr>
</table>
<hr/>
<table style="width: 100%">
    <tr style="font-size: 0.85em">
        <th>โครงการ</th>
        <th>หน่วยงาน</th>
        <th style="width: 5em">จัดเมื่อ</th>
        <th style="width: 4.5em">ระยะเวลา (ชม.)</th>
        <th style="width: 4em">บทบาท</th>
    </tr>
    @foreach($user->getActivityTranscript() as $item)
        <tr>
            <td>{{ $item['name'] }}</td>
            <td>{{ $item['department'] }}</td>
            <td>{{ str($item['period_end'])->explode(' ')->skip(1)->implode(' ') }}</td>
            <td style="text-align: center">{{ $item['duration'] }}</td>
            <td style="text-align: center">
                {{ ['organizer' => 'ร', 'staff' => 'ป', 'attendee' => 'ข'][$item['role']] ?? $item['role'] }}
            </td>
        </tr>
    @endforeach
</table>
<p style="margin-top:2em; font-size:0.9em; color:gray">บทบาท : ร = ผู้รับผิดชอบ / ป = ผู้ปฏิบัติงาน / ข = ผู้เข้าร่วม</p>
<p style="margin-top:2em; font-size:0.7em; color:lightgray; text-align: right">พิมพ์เมื่อ {{ date('F j, Y') }}</p>
<script>
    window.print();
    window.onafterprint = window.close;
</script>
