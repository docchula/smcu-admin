@php    /** @var \App\Models\User $user */ @endphp
    <!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @font-face {
            font-family: 'TH Sarabun New';
            src: url("/assets/THSarabunNew.ttf");
        }

        @font-face {
            font-family: 'TH Sarabun New';
            font-weight: bold;
            src: url("/assets/THSarabunNew Bold.ttf");
        }

        @page {
            size: A4 portrait;
            margin: 0.7in 0.7in 0.9in 0.9in;
            @bottom-left {
                content: "ระเบียนประวัติการเข้าร่วมกิจกรรมนอกหลักสูตร {{ $user->name }}";
                font-family: "TH Sarabun New", sans-serif;
                font-size: 0.8em;
            }
            @bottom-center {
                content: "หน้าที่ " counter(page) " จาก " counter(pages);
                font-family: "TH Sarabun New", sans-serif;
                font-size: 0.8em;
            }
            @bottom-right {
                content: "{{ \Illuminate\Support\Carbon::now()->addYears(543)->translatedFormat('j F Y') }}";
                font-family: "TH Sarabun New", sans-serif;
                font-size: 0.8em;
            }
            @unless(empty($draft))
                @top-center {
                content: "ฉบับร่าง";
                font-family: "TH Sarabun New", sans-serif;
                color: red;
            }
        @endunless





        }

        body {
            font-family: "TH Sarabun New", sans-serif;
            font-size: 14pt;
            max-width: 21cm;
        }

        th, td {
            vertical-align: top;
        }

        tbody {
            break-inside: avoid;
        }

        .header-margin {
            margin: 0.5rem 0;
        }

        .subtext {
            color: #222222;
            font-size: 0.9em;
            line-height: 0.9em;
            margin-top: 0;

            .subcell {
                text-align: center;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>
<section style="display:flex;width: 100%;border-bottom: 0.15em solid black">
    <div style="text-align: center">
        @if(empty($draft))
            <img src="https://upload.wikimedia.org/wikipedia/commons/0/06/Logo_of_Chulalongkorn_University_Colored.svg"
                 alt="Logo" style="width: 1.3cm"/>
        @else
            <h2 class="header-margin" style="color:red">ร่าง</h2>
        @endif
    </div>
    <div style="flex-grow: 1; text-align: center; line-height: 1em; font-size: 1.2em">
        <h3 class="header-margin" style="margin-top:0">ระเบียนประวัติการเข้าร่วมกิจกรรมนอกหลักสูตร</h3>
        <p class="header-margin" style="font-size: 0.9em">คณะแพทยศาสตร์ จุฬาลงกรณ์มหาวิทยาลัย</p>
        <p class="header-margin">
            <span style="margin-right: 4rem">{{ $user->name }}</span>
            เลขประจำตัวนิสิต&nbsp;{{ $user->student_id }}
        </p>
    </div>
</section>
<table style="width: 100%">
    <thead>
    <tr style="font-size: 0.85em">
        <th>ที่</th>
        <th colspan="2">โครงการ</th>
        <th style="white-space: nowrap;">จัดเมื่อ</th>
        <th style="white-space: nowrap;">ระยะเวลา (ชม.)</th>
        <th style="white-space: nowrap;">บทบาท</th>
    </tr>
    </thead>
    @foreach($user->getActivityTranscript()->filter(fn($item) => $item['approve_status'] >= 1) as $i => $item)
        <tbody>
        <tr style="font-weight: bold; line-height: 1em">
            <td style="white-space: nowrap; text-align: right;padding:0.3em 0.3em 0">{{ $i + 1 }}.</td>
            <td colspan="5" style="padding-top:0.3em">{{ $item['name'] }}</td>
        </tr>
        <tr class="subtext">
            <td></td>
            <td>หน่วยงาน</td>
            <td>{{ $item['department'] }}</td>
            <td class="subcell">{{ str($item['period_end'])->explode(' ')->skip(1)->implode(' ') }}</td>
            <td class="subcell">{{ ($item['duration'] && is_int($item['duration'])) ? round($item['duration']) : ($item['duration'] ?: '-') }}</td>
            <td class="subcell">
                {{ ['organizer' => 'รับผิดชอบ', 'staff' => 'ปฏิบัติงาน', 'attendee' => 'เข้าร่วม'][$item['role']] ?? $item['role'] ?? '-' }}
            </td>
        </tr>
        @if(!empty($item['title']))
            <tr class="subtext">
                <td></td>
                <td>ตำแหน่ง</td>
                <td colspan="4">{{ $item['title'] }}</td>
            </tr>
        @endif
        </tbody>
    @endforeach
</table>
@if(empty($draft))
    <section style="display: flex; margin: 0 auto;">
        <div style="width: 10cm"></div>
        <div style="width: 10cm;text-align: center; line-height: 0.9em; padding-top:1.5em">
            <p style="color:red;font-weight: bold;font-size:2.2em; margin: 0 0 0.3em">Signature</p>
            (ผศ.นพ.อติคุณ ธนกิจ)<br/>
            รองคณบดีด้านกิจการนิสิต
        </div>
    </section>
@endif
<section style="display: flex; margin: 0 auto;">
    <p style="flex-grow: 1;font-size:0.9em; line-height: 1em">
        บทบาทของนิสิตในกิจกรรม ได้แก่ (1) ผู้รับผิดชอบ (2) ผู้ปฏิบัติงาน หรือ (3) ผู้เข้าร่วม<br/>
        @if (isset($link))
            เอกสารนี้ลงลายมือชื่ออิเล็กทรอนิกส์ เมื่อวันที่ {{ \Illuminate\Support\Carbon::now()->addYears(543)->translatedFormat('j F Y เวลา H.i') }}
            น.<br/>
            ผู้รับสามารถตรวจสอบความถูกต้องของเอกสาร โดยเปิดไฟล์ด้วยโปรแกรม Adobe Acrobat หรือโปรแกรมที่รองรับ<br/>
            หรือโดยสแกนคิวอาร์โค้ดเพื่อดูข้อมูลปัจจุบันจากเว็บไซต์
        @endif
    </p>
    @if (isset($link))
        <div style="text-align: right;">
            <a target="_blank" href="{{ $link }}">{{ $qrCode }}</a>
        </div>
    @endif
</section>
<script>
    window.print();
</script>
</body>
</html>
