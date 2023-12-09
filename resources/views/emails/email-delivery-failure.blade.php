<h2 style="color: black">
    หนังสือสพจ. ที่ {{ $document->number }}/{{ $document->year }} เรื่อง {{ $document->title }}
    <span style="color:red">ไม่สามารถส่งไปยังอีเมลที่ระบุไว้ได้</span>
</h2>

ไม่สามารถส่งอีเมลตามที่ระบุไว้ได้ กรุณาตรวจสอบที่อยู่อีเมลอีกครั้ง โดยท่านสามารถแก้ไขอีเมลในระบบ DocHub ได้โดยกด Reassign Email Address (<a
    href="https://helpdesk.dochub.com/hc/en-us/articles/360019832113-Editing-a-Sign-Request-after-it-s-been-sent#h_65fb7b62-8188-461d-a6f4-5f8f8169b740">คู่มือ</a>)
<br/>
Signature request email could not be sent. Either the address does not exist, or it cannot receive emails at this time.<br/>
<br/>
<a href="https://dochub.com" class="button button-primary" target="_b=
lank" rel="noopener"
   style="box-sizing: border-box; position: relative; -webkit-text-size-adjust: none; border-radius: 4px; color: #fff; display: inline-block; overflow: hidden; text-decoration: none; background-color: #2d3748; border-bottom: 8px solid #2d3748; border-left: 18px solid #2d3748; border-right: 18px solid #2d3748; border-top: 8px solid #2d3748;">ดูเอกสารข้อมูลเพิ่มเติมที่
    DocHub.com</a><br/>
<br/>
<a href="{{ route('documents.show', ['document' => $document->id ]) }}" style="color: #555555; text-decoration: none"><b><span style="color: green">SMCU</span>
        Admin</b> | ระบบบริหารงานสโมสร สโมสรนิสิตคณะแพทยศาสตร์ จุฬาลงกรณ์มหาวิทยาลัย</a>

