<!DOCTYPE html>
<html lang="ko">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f2f3f4;font-family:'Apple SD Gothic Neo','Malgun Gothic',sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f2f3f4;padding:40px 0;">
  <tr><td align="center">
    <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;max-width:560px;">

      <!-- 헤더 -->
      <tr>
        <td style="background:#3A8C82;padding:28px 32px;text-align:center;">
          <span style="color:#ffffff;font-size:22px;font-weight:700;letter-spacing:-0.5px;">평목</span>
          <span style="color:rgba(255,255,255,0.6);font-size:12px;margin-left:8px;letter-spacing:0.1em;">PYEONGMOK</span>
        </td>
      </tr>

      <!-- 본문 -->
      <tr>
        <td style="padding:36px 32px 24px;">
          <p style="font-size:20px;font-weight:700;color:#1A1F1E;margin:0 0 20px;letter-spacing:-0.5px;">새 문의가 도착했습니다</p>
          <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
            <tr>
              <td style="width:72px;padding:10px 0;border-bottom:1px solid #f0f2f1;font-size:12px;color:#97A8A6;font-weight:600;vertical-align:top;">이름</td>
              <td style="padding:10px 0 10px 16px;border-bottom:1px solid #f0f2f1;font-size:14px;color:#1A1F1E;"><?= htmlspecialchars($name) ?></td>
            </tr>
            <tr>
              <td style="width:72px;padding:10px 0;border-bottom:1px solid #f0f2f1;font-size:12px;color:#97A8A6;font-weight:600;vertical-align:top;">이메일</td>
              <td style="padding:10px 0 10px 16px;border-bottom:1px solid #f0f2f1;font-size:14px;color:#1A1F1E;">
                <a href="mailto:<?= htmlspecialchars($email) ?>" style="color:#3A8C82;text-decoration:none;"><?= htmlspecialchars($email) ?></a>
              </td>
            </tr>
            <tr>
              <td style="width:72px;padding:10px 0;border-bottom:1px solid #f0f2f1;font-size:12px;color:#97A8A6;font-weight:600;vertical-align:top;">제목</td>
              <td style="padding:10px 0 10px 16px;border-bottom:1px solid #f0f2f1;font-size:14px;color:#1A1F1E;"><?= htmlspecialchars($subject) ?></td>
            </tr>
            <tr>
              <td style="width:72px;padding:14px 0;font-size:12px;color:#97A8A6;font-weight:600;vertical-align:top;">내용</td>
              <td style="padding:14px 0 0 16px;font-size:14px;color:#1A1F1E;line-height:1.8;white-space:pre-wrap;"><?= htmlspecialchars($message) ?></td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- 구분선 -->
      <tr><td style="padding:0 32px;"><hr style="border:none;border-top:1px solid #e8ecea;margin:0;"></td></tr>

      <!-- 푸터 -->
      <tr>
        <td style="padding:20px 32px 28px;">
          <p style="font-size:11px;color:#97A8A6;line-height:1.7;margin:0;">
            <?= SITE_NAME ?> 문의 폼에서 자동 발송된 메일입니다.
          </p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>
