@component('mail::message')
    # หนังสือสพจ. ที่ {{ $document->number }}/{{ $document->year }} เรื่อง {{ $document->title }}

    ## Signature Request Rejected

    เอกสารถูกปฏิเสธการลงลายมือชื่อ กรุณาติดต่อกรรมการสโมสรที่เกี่ยวข้อง

    Signature request rejected. Please contact the related union committee member.

    @component('mail::button', ['url' => 'https://dochub.com'])
        Goto DocHub.com
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
