@php    /** @var \App\Models\User $user */ @endphp
    <!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap">
<style>
    @page {
        size: A4 portrait;
        margin: 0.5in 0.5in 0.3in 0.5in;
    }

    body {
        font-family: "Sarabun", sans-serif;
        font-weight: 400;
    }

    tbody {
        break-inside: avoid
    }

    th, td {
        vertical-align: top;
        word-break: break-all;
    }

    .header-margin {
        margin: 0.5rem 0;
    }

    .subtext {
        color: gray;
        font-size: 0.9rem;
        margin-top: 0;
    }
</style>
<table style="width: 100%;border-bottom: 0.15em solid black">
    <tr>
        <td style="vertical-align: middle;text-align: center">
            @if(empty($draft))
                <img src="/phrakiao.jpg" alt="Logo" style="width: 50px"/>
            @else
                <h2 class="header-margin" style="color:red">ร่าง</h2>
            @endif
        </td>
        <td>
            <h3 class="header-margin" style="margin-top:0">ระเบียนประวัติการเข้าร่วมกิจกรรมนอกหลักสูตร</h3>
            <p class="header-margin">คณะแพทยศาสตร์ จุฬาลงกรณ์มหาวิทยาลัย</p>
            <p class="header-margin">
                <span style="margin-right: 4rem">{{ $user->name }}</span>
                เลขประจำตัวนิสิต&nbsp;{{ $user->student_id }}
            </p>
        </td>
    </tr>
</table>
<table style="width: 100%">
    <thead>
    <tr style="font-size: 0.85em">
        <th>ที่</th>
        <th>โครงการ/หน่วยงาน</th>
        <th style="white-space: nowrap;">จัดเมื่อ</th>
        <th style="white-space: nowrap;">ระยะเวลา (ชม.)</th>
        <th style="white-space: nowrap;">บทบาท</th>
    </tr>
    </thead>
    @foreach($user->getActivityTranscript()->filter(fn($item) => $item['approve_status'] >= 1) as $i => $item)
        <tbody>
        <tr>
            <td style="white-space: nowrap; text-align: right">{{ $i + 1 }}.</td>
            <td colspan="4">{{ $item['name'] }}</td>
        </tr>
        <tr class="subtext">
            <td></td>
            <td>
                {{ $item['department'] }}
                @if(!empty($item['title']))
                    <br/> ตำแหน่ง : {{ $item['title'] }}
                @endif
            </td>
            <td style="white-space: nowrap;">{{ str($item['period_end'])->explode(' ')->skip(1)->implode(' ') }}</td>
            <td style="text-align: center; ">{{ ($item['duration'] && is_int($item['duration'])) ? round($item['duration']) : $item['duration'] }}</td>
            <td style="text-align: center; font-size: 0.9rem; white-space: nowrap">
                {{ ['organizer' => 'รับผิดชอบ', 'staff' => 'ปฏิบัติงาน', 'attendee' => 'เข้าร่วม'][$item['role']] ?? $item['role'] ?? '-' }}
            </td>
        </tr>
        </tbody>
    @endforeach
    <tfoot>
    <tr style="color: gray; font-size:0.75em">
        <td colspan="3" style="padding-top:1.5em">ระเบียนประวัติการเข้าร่วมกิจกรรมนอกหลักสูตร {{ $user->name }}</td>
        <td colspan="2"
            style="text-align: right;padding-top:1.5em">{{ \Illuminate\Support\Carbon::now()->addYears(543)->translatedFormat('j F Y') }}</td>
    </tr>
    </tfoot>
</table>
<p style="font-size:0.8em; color:gray;">
    บทบาท ได้แก่ ผู้รับผิดชอบ ผู้ปฏิบัติงาน หรือผู้เข้าร่วม
</p>
<script>
    window.print();
    window.onafterprint = window.close;
</script>
