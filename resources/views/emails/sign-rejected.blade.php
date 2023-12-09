<h2 style="color: black">
    หนังสือสพจ. ที่ {{ $document->number }}/{{ $document->year }} เรื่อง {{ $document->title }}
    <span style="color:red">ถูกปฏิเสธการลงลายมือชื่อ</span>
</h2>

เอกสารถูกปฏิเสธการลงลายมือชื่อ กรุณาติดต่อกรรมการสโมสรที่เกี่ยวข้อง<br/>
Signature request rejected. Please contact the related union committee member.<br/>
<br/>
<a href="https://dochub.com" class="button button-primary" target="_b=
lank" rel="noopener"
   style="box-sizing: border-box; position: relative; -webkit-text-size-adjust: none; border-radius: 4px; color: #fff; display: inline-block; overflow: hidden; text-decoration: none; background-color: #2d3748; border-bottom: 8px solid #2d3748; border-left: 18px solid #2d3748; border-right: 18px solid #2d3748; border-top: 8px solid #2d3748;">ดูเอกสารข้อมูลเพิ่มเติมที่
    DocHub.com</a><br/>
<br/>
<a href="{{ route('documents.show', ['document' => $document->id ]) }}" style="color: #555555; text-decoration: none"><b><span style="color: green">SMCU</span>
        Admin</b> | ระบบบริหารงานสโมสร สโมสรนิสิตคณะแพทยศาสตร์ จุฬาลงกรณ์มหาวิทยาลัย</a>
