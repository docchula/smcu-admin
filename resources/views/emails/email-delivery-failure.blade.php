@component('mail::message')
    # หนังสือสพจ. ที่ {{ $document->number }}/{{ $document->year }} เรื่อง {{ $document->title }}

    ## Email delivery failure

    ไม่สามารถส่งอีเมลที่ระบุไว้ได้

    One or more emails could not be sent. Either the address does not exist, or it cannot receive emails at this time.

    @component('mail::button', ['url' => 'https://dochub.com'])
        Goto DocHub.com
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
