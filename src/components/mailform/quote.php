<?php // 고객에게 보내는 견적서 메일 — 본문이 견적서 문서 그 자체(고객용 웹 열람 화면 없음). $quote = quote_by_id() 결과 ?>
<!DOCTYPE html>
<html lang="ko">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#F4F2EE;font-family:'Apple SD Gothic Neo','Malgun Gothic',sans-serif;color:#23262A;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F4F2EE;padding:32px 0;">
  <tr><td align="center">
    <table width="760" cellpadding="0" cellspacing="0" style="max-width:760px;width:100%;background:#FFFFFF;border-radius:10px;">
      <tr><td style="padding:24px 28px 8px;font-size:14px;line-height:1.7;color:#23262A;">
        안녕하세요, 워크그룹 평목입니다.<br>
        요청하신 창호 제작 견적서를 보내드립니다. 내용 확인 후 문의 사항은 이 메일에 회신해 주세요.
      </td></tr>
      <tr><td style="padding:16px 28px 28px;">
        <?php $quoteShowThumb = false; include __DIR__ . '/../quote_document.php'; ?>
      </td></tr>
    </table>
    <p style="font-size:11px;color:#6B6A66;margin:14px 0 0;">워크그룹 평목 · pyeongmok.com</p>
  </td></tr>
</table>
</body>
</html>
