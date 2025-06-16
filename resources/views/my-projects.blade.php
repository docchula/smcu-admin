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

    .department-name {
        color: gray;
        font-size: 0.9rem;
        margin-top: 0;
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
    <thead>
    <tr style="font-size: 0.85em">
        <th>ที่</th>
        <th>โครงการ</th>
        <th style="width: 5em">จัดเมื่อ</th>
        <th style="width: 4.5em">ระยะเวลา (ชม.)</th>
        <th style="width: 4em">บทบาท</th>
    </tr>
    </thead>
    <tbody>
    @foreach($user->getActivityTranscript()->filter(fn($item) => $item['approve_status'] >= 1) as $i => $item)
        <tr>
            <td style="white-space: nowrap">{{ $i + 1 }}.</td>
            <td>
                {{ $item['name'] }}
                <p class="department-name">
                    หน่วยงาน : {{ $item['department'] }}
                    @if(!empty($item['title'])) &emsp; ตำแหน่ง : {{ $item['title'] }} @endif
                </p>
            </td>
            <td>{{ str($item['period_end'])->explode(' ')->skip(1)->implode(' ') }}</td>
            <td style="text-align: center">{{ ($item['duration'] && is_int($item['duration'])) ? round($item['duration']) : $item['duration'] }}</td>
            <td style="text-align: center; font-size: 0.9rem; white-space: nowrap">
                {{ ['organizer' => 'รับผิดชอบ', 'staff' => 'ปฏิบัติงาน', 'attendee' => 'เข้าร่วม'][$item['role']] ?? $item['role'] ?? '-' }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@if(empty($draft))
    <div style="height:6rem">
        {{-- space for signature --}}
    </div>
@endif
<p style="margin-top:2em; font-size:0.7em; color:gray;">
    ระเบียนประวัติการเข้าร่วมกิจกรรมนอกหลักสูตร {{ $user->name }}<br/>
    พิมพ์เมื่อ {{ \Illuminate\Support\Carbon::now()->addYears(543)->translatedFormat('j F Y') }}<br/>
    บทบาท ได้แก่ ผู้รับผิดชอบ ผู้ปฏิบัติงาน หรือผู้เข้าร่วม
</p>
<script>
    window.print();
    window.onafterprint = window.close;
</script>
