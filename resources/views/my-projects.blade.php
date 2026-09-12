@php
    /** @var \App\Models\User $user */
    /** @var \Illuminate\Support\Carbon $date */
    $date ??= \Illuminate\Support\Carbon::now();
@endphp
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
            margin: 0.7in 0.7in 0.7in 0.9in;
            @bottom-left {
                content: "ระเบียนประวัติการเข้าร่วมกิจกรรมนอกหลักสูตร {{ $user->name }}";
                font-family: "TH Sarabun New", sans-serif;
                font-size: 0.8em;
                border-top: 0.05em solid black;
            }
            @bottom-center {
                content: "หน้าที่ " counter(page) " จาก " counter(pages);
                font-family: "TH Sarabun New", sans-serif;
                font-size: 0.8em;
                border-top: 0.05em solid black;
            }
            @bottom-right {
                content: "{{ $date->clone()->addYears(543)->translatedFormat('j F Y') }}";
                font-family: "TH Sarabun New", sans-serif;
                font-size: 0.8em;
                border-top: 0.05em solid black;
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
        <img src="/assets/phrakiao.svg" alt="Logo" style="width: 1.3cm"/>
        {{-- <img src="/assets/mdcu.svg" alt="Logo" style="width: 2.2cm"/> --}}
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
        <th style="border-bottom: 0.05em solid black"></th>
        <th style="border-bottom: 0.05em solid black" colspan="2">โครงการ</th>
        <th style="border-bottom: 0.05em solid black; white-space: nowrap;">จัดเมื่อ</th>
        <th style="border-bottom: 0.05em solid black; white-space: nowrap;">ระยะเวลา (ชม.)</th>
    </tr>
    </thead>
    @php
        $roleGroupLabels = [
            'organizer' => 'โครงการที่นิสิตรับผิดชอบ',
            'staff' => 'โครงการที่นิสิตปฏิบัติงาน',
            'attendee' => 'โครงการที่นิสิตเข้าร่วม',
            'other' => 'ตำแหน่ง/กิจกรรมอื่น',
        ];
        $activities = $user->getActivityTranscript()->filter(fn($item) => $item['approve_status'] >= 1);
        $groupedActivities = $activities
            ->groupBy(fn($item) => $item['role'] ?? 'other');
    @endphp
    @foreach($roleGroupLabels as $roleKey => $roleLabel)
        @continue($groupedActivities->get($roleKey, collect())->isEmpty())
        <tbody>
        <tr>
            <td colspan="6" style="font-weight: bold; padding-top: 0.6em">{{ $roleLabel }}</td>
        </tr>
        </tbody>
        @foreach($groupedActivities->get($roleKey) as $i => $item)
            @php
                $startMonthYear = $item['period_start'] ? str($item['period_start'])->explode(' ')->skip(1)->implode(' ') : null;
                $endMonthYear = $item['period_end'] ? str($item['period_end'])->explode(' ')->skip(1)->implode(' ') : null;
                $periodDisplay = match(true) {
                    !$endMonthYear => '-',
                    !$startMonthYear || $startMonthYear === $endMonthYear => $endMonthYear,
                    default => $startMonthYear.' - '.$endMonthYear,
                };
            @endphp
            <tbody>
            <tr style="font-weight: bold; line-height: 1em">
                <td style="white-space: nowrap; text-align: right;padding:0.3em 0.3em 0">{{ $i+1 }}.</td>
                <td colspan="4" style="padding-top:0.3em">{{ $item['name'] }}</td>
            </tr>
            <tr class="subtext">
                <td></td>
                <td>หน่วยงาน</td>
                <td>
                    @if(empty($item['department']) or $item['department'] == 'อื่น ๆ/ไม่มีสังกัด')
                        อื่น ๆ
                    @else
                        {{ $item['department'] }}
                    @endif
                </td>
                <td class="subcell">{{ $periodDisplay }}</td>
                <td class="subcell">{{ ($item['duration'] && is_int($item['duration'])) ? round($item['duration']) : ($item['duration'] ?: '-') }}</td>
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
    @endforeach
</table>
<div>
    @if(empty($draft))
        <div style="display: flex; margin: 0 auto; padding-top:4em">
            <div style="width: 10cm;text-align: center; line-height: 1em">
                (ผศ. นพ.อติคุณ ธนกิจ)<br/>
                รองคณบดีด้านกิจการนิสิต
            </div>
            <div style="width: 10cm;text-align: center; line-height: 1em">
                (รศ. ดร. นพ.จิรุตม์ ศรีรัตนบัลล์)<br/>
                คณบดี
            </div>
        </div>
    @endif
    <div style="display: flex; margin: 0.5em auto;border-top: 0.15em solid black; padding-top: 0.5em">
        <div style="flex-grow: 1; line-height: 1.1em; padding-top: 0.2em;">
            @if (substr($user->student_id, 0, 2) < 67)
                ข้อมูลตั้งแต่เดือนมีนาคม พ.ศ. 2567 เป็นต้นไป
            @endif
            รวม {{ $activities->count() }} รายการ<br/>
            บทบาทของนิสิตในกิจกรรม ได้แก่ (1) ผู้รับผิดชอบ (2) ผู้ปฏิบัติงาน หรือ (3) ผู้เข้าร่วม<br/>
            @if (isset($draft) and $draft and Auth::check())
                พิมพ์จากระบบอิเล็กทรอนิกส์เมื่อวันที่ {{ $date->clone()->addYears(543)->translatedFormat('j F Y') }}
                โดย {{ \Illuminate\Support\Facades\Auth::user()->name }}
            @else
                ออกให้ ณ วันที่ {{ $date->clone()->addYears(543)->translatedFormat('j F Y') }}
            @endif
            @if (isset($link))
                <br/>ผู้รับสามารถตรวจสอบความถูกต้องของเอกสาร โดยสแกนคิวอาร์โค้ดเพื่อดูข้อมูลปัจจุบันจากเว็บไซต์
            @endif
        </div>
        @if (isset($link))
            <div style="text-align: right;">
                <a target="_blank" href="{{ $link }}">{{ $qrCode }}</a>
            </div>
        @endif
    </div>
</div>
<script>
    window.print();
</script>
</body>
</html>
