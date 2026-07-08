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
          <p style="font-size:14px;font-weight:600;color:var(--text-muted);margin:0 0 4px;letter-spacing:-0.2px;">새 견적요청이 접수되었습니다</p>
          <p style="font-size:34px;font-weight:800;color:var(--accent);margin:0 0 20px;letter-spacing:-0.5px;">주문번호 #<?= (int)$orderId ?></p>
          <?php if (!empty($thumbnail)): ?>
          <img src="<?= htmlspecialchars($thumbnail) ?>" alt="도면 미리보기" style="display:block;width:100%;max-width:496px;border-radius:8px;margin-bottom:20px;background:var(--bg);">
          <?php endif; ?>
          <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
            <tr>
              <td style="width:72px;padding:10px 0;border-bottom:1px solid var(--bg);font-size:12px;color:var(--text-muted);font-weight:600;vertical-align:top;">요청일</td>
              <td style="padding:10px 0 10px 16px;border-bottom:1px solid var(--bg);font-size:14px;color:var(--text);"><?= htmlspecialchars($orderDate) ?></td>
            </tr>
            <tr>
              <td style="width:72px;padding:10px 0;border-bottom:1px solid var(--bg);font-size:12px;color:var(--text-muted);font-weight:600;vertical-align:top;">이름</td>
              <td style="padding:10px 0 10px 16px;border-bottom:1px solid var(--bg);font-size:14px;color:var(--text);"><?= htmlspecialchars($name) ?></td>
            </tr>
            <tr>
              <td style="width:72px;padding:10px 0;border-bottom:1px solid var(--bg);font-size:12px;color:var(--text-muted);font-weight:600;vertical-align:top;">연락처</td>
              <td style="padding:10px 0 10px 16px;border-bottom:1px solid var(--bg);font-size:14px;color:var(--text);"><?= htmlspecialchars($phone) ?></td>
            </tr>
            <?php if (!empty($company)): ?>
            <tr>
              <td style="width:72px;padding:10px 0;border-bottom:1px solid var(--bg);font-size:12px;color:var(--text-muted);font-weight:600;vertical-align:top;">회사명</td>
              <td style="padding:10px 0 10px 16px;border-bottom:1px solid var(--bg);font-size:14px;color:var(--text);"><?= htmlspecialchars($company) ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($email)): ?>
            <tr>
              <td style="width:72px;padding:10px 0;border-bottom:1px solid var(--bg);font-size:12px;color:var(--text-muted);font-weight:600;vertical-align:top;">이메일</td>
              <td style="padding:10px 0 10px 16px;border-bottom:1px solid var(--bg);font-size:14px;color:var(--text);">
                <a href="mailto:<?= htmlspecialchars($email) ?>" style="color:var(--accent);text-decoration:none;"><?= htmlspecialchars($email) ?></a>
              </td>
            </tr>
            <?php endif; ?>
            <tr>
              <td style="width:72px;padding:10px 0;border-bottom:1px solid var(--bg);font-size:12px;color:var(--text-muted);font-weight:600;vertical-align:top;">도면</td>
              <td style="padding:10px 0 10px 16px;border-bottom:1px solid var(--bg);font-size:14px;color:var(--text);">
                <?= htmlspecialchars($title ?: '(제목 없음)') ?> <span style="color:var(--text-muted);">(<?= htmlspecialchars($engine) ?><?= !empty($version) ? ' · ' . htmlspecialchars($version) : '' ?>)</span>
              </td>
            </tr>
            <tr>
              <td style="width:72px;padding:10px 0;border-bottom:1px solid var(--bg);font-size:12px;color:var(--text-muted);font-weight:600;vertical-align:top;">납기 희망일</td>
              <td style="padding:10px 0 10px 16px;border-bottom:1px solid var(--bg);font-size:14px;color:var(--text);"><?= htmlspecialchars($dueDate) ?></td>
            </tr>
            <tr>
              <td style="width:72px;padding:10px 0;border-bottom:1px solid var(--bg);font-size:12px;color:var(--text-muted);font-weight:600;vertical-align:top;">배송지</td>
              <td style="padding:10px 0 10px 16px;border-bottom:1px solid var(--bg);font-size:14px;color:var(--text);">
                <?= !empty($shipZip) ? '(' . htmlspecialchars($shipZip) . ') ' : '' ?><?= htmlspecialchars($shipAddr) ?>
              </td>
            </tr>
            <tr>
              <td style="width:72px;padding:10px 0;border-bottom:1px solid var(--bg);font-size:12px;color:var(--text-muted);font-weight:600;vertical-align:top;">배송지 연락처</td>
              <td style="padding:10px 0 10px 16px;border-bottom:1px solid var(--bg);font-size:14px;color:var(--text);"><?= htmlspecialchars($shipPhone) ?></td>
            </tr>
            <?php if (!empty($memo)): ?>
            <tr>
              <td style="width:72px;padding:14px 0;font-size:12px;color:var(--text-muted);font-weight:600;vertical-align:top;">요청사항</td>
              <td style="padding:14px 0 0 16px;font-size:14px;color:var(--text);line-height:1.8;white-space:pre-wrap;"><?= htmlspecialchars($memo) ?></td>
            </tr>
            <?php endif; ?>
          </table>
        </td>
      </tr>

      <!-- 구분선 -->
      <tr><td style="padding:0 32px;"><hr style="border:none;border-top:1px solid var(--accent-tint);margin:0;"></td></tr>

      <!-- 푸터 -->
      <tr>
        <td style="padding:20px 32px 28px;">
          <p style="font-size:11px;color:var(--text-muted);line-height:1.7;margin:0;">
            <?= SITE_NAME ?> 도면 설계기에서 자동 발송된 견적요청 메일입니다.
          </p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>
