<!DOCTYPE html>
<html lang="ko">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:var(--bg);font-family:'Apple SD Gothic Neo','Malgun Gothic',sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:var(--bg);padding:40px 0;">
  <tr><td align="center">
    <table width="560" cellpadding="0" cellspacing="0" style="background:var(--bg);border-radius:12px;overflow:hidden;max-width:560px;">

      <!-- 헤더 -->
      <tr>
        <td style="background:var(--accent);padding:28px 32px;text-align:center;">
          <span style="color:var(--bg);font-size:22px;font-weight:700;letter-spacing:-0.5px;">평목</span>
          <span style="color:rgba(var(--bg-rgb), 0.6);font-size:12px;margin-left:8px;letter-spacing:0.1em;">PYEONGMOK</span>
        </td>
      </tr>

      <!-- 본문 -->
      <tr>
        <td style="padding:36px 32px 24px;">
          <p style="font-size:20px;font-weight:700;color:var(--text);margin:0 0 16px;letter-spacing:-0.5px;">비밀번호 재설정</p>
          <p style="font-size:14px;color:var(--text-muted);line-height:1.8;margin:0 0 28px;">
            안녕하세요,<br>
            아래 버튼을 클릭하여 비밀번호를 재설정해 주세요.<br>
            링크는 <strong style="color:var(--text);">1시간</strong> 동안 유효합니다.
          </p>
          <table cellpadding="0" cellspacing="0"><tr><td>
            <a href="<?= SITE_URL ?>/?reset=<?= urlencode($token) ?>"
               style="display:inline-block;background:var(--accent);color:var(--bg);text-decoration:none;padding:13px 28px;border-radius:6px;font-size:14px;font-weight:600;letter-spacing:-0.2px;">
              비밀번호 재설정하기
            </a>
          </td></tr></table>
          <p style="font-size:12px;color:var(--text-muted);margin:20px 0 0;line-height:1.7;">
            이 메일을 요청하지 않으셨다면 무시하셔도 됩니다.
          </p>
        </td>
      </tr>

      <!-- 구분선 -->
      <tr><td style="padding:0 32px;"><hr style="border:none;border-top:1px solid var(--accent-tint);margin:0;"></td></tr>

      <!-- 푸터 -->
      <tr>
        <td style="padding:20px 32px 28px;">
          <p style="font-size:11px;color:var(--text-muted);line-height:1.7;margin:0;">
            본 메일은 비밀번호 재설정 요청 시 자동으로 발송됩니다.<br>
            문의사항은 <a href="<?= SITE_URL ?>/src/company/" style="color:var(--accent);text-decoration:none;">평목 문의 페이지</a>를 이용해 주세요.
          </p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>
