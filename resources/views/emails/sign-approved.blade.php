<h2 style="color: black">
    หนังสือสพจ. ที่ {{ $document->number }}/{{ $document->year }} เรื่อง {{ $document->title }}
    <span style="color:green"> ถูกลงลายมือชื่อเรียบร้อยแล้ว</span>
</h2>

เอกสารของท่านได้รับการลงลายมือชื่ออิเล็กทรอนิกส์เรียบร้อยแล้ว<br/>
Signature request approved.<br/>
<br/>
<a href="{{ route('documents.show', ['document' => $document->id ]) }}"
   class="button button-primary" target="_b=lank" rel="noopener"
   style="box-sizing: border-box; position: relative; -webkit-text-size-adjust: none; border-radius: 4px; color: #fff; display: inline-block; overflow: hidden; text-decoration: none; background-color: #2d3748; border-bottom: 8px solid #2d3748; border-left: 18px solid #2d3748; border-right: 18px solid #2d3748; border-top: 8px solid #2d3748;">
    ดูเอกสารใน SMCU Admin
</a><br/>
<br/>
<a href="{{ route('documents.show', ['document' => $document->id ]) }}" style="color: #555555; text-decoration: none"><b><span style="color: green">SMCU</span>
        Admin</b> | ระบบบริหารงานสโมสร สโมสรนิสิตคณะแพทยศาสตร์ จุฬาลงกรณ์มหาวิทยาลัย</a>

