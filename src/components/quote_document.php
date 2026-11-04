<?php
// 견적서 문서 본체 — 관리자 견적서 화면(src/quote/view.php)과 고객 메일 본문(components/mailform/quote.php)이 함께 쓴다.
// 메일 프로그램은 <style>·CSS 변수를 대부분 무시하므로 표 레이아웃 + 인라인 스타일 + 색상값 직접 지정으로만 그린다.
// 입력: $quote (src/lib/quote.php quote_by_id() 결과). 도면 그림은 넣지 않는다(품목이 여러 개면 복잡해서 — 2026-11-04 사용자 결정).
require_once __DIR__ . '/../lib/quote.php';

$__q    = $quote;
$__c    = json_decode($__q['customer_json'], true) ?: [];
$__s    = json_decode($__q['supplier_json'], true) ?: [];
$__items = json_decode($__q['items_json'], true) ?: [];
$__h    = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$__won  = fn($n) => number_format((int) $n);
$__qty  = fn($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
$__ink  = '#23262A';
$__line = '#C9C6BF';
$__soft = '#F4F2EE';
$__muted = '#6B6A66';
$__th   = "padding:6px 8px;border:1px solid $__line;background:$__soft;font-size:12px;font-weight:600;color:$__ink;white-space:nowrap;";
$__td   = "padding:6px 8px;border:1px solid $__line;font-size:13px;color:$__ink;";
$__recipient = trim(($__c['company'] ?? '') !== '' ? $__c['company'] : ($__c['name'] ?? ''));
$__supplierRows = [
    ['등록번호', $__s['biz_no'] ?? ''],
    ['상호', $__s['name'] ?? ''],
    ['대표자', $__s['ceo'] ?? ''],
    ['주소', $__s['address'] ?? ''],
    ['업태 / 종목', trim(($__s['biz_type'] ?? '') . ((($__s['biz_type'] ?? '') !== '' && ($__s['biz_item'] ?? '') !== '') ? ' / ' : '') . ($__s['biz_item'] ?? ''))],
    ['연락처', trim(($__s['phone'] ?? '') . ((($__s['phone'] ?? '') !== '' && ($__s['email'] ?? '') !== '') ? ' · ' : '') . ($__s['email'] ?? ''))],
];
?>
<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;max-width:760px;margin:0 auto;background:#FFFFFF;color:<?= $__ink ?>;font-family:'Apple SD Gothic Neo','Malgun Gothic','Noto Sans KR',sans-serif;">
  <tr>
    <td style="padding:0 0 18px;text-align:center;border-bottom:2px solid <?= $__ink ?>;">
      <div style="font-size:30px;font-weight:800;letter-spacing:14px;padding-left:14px;">견 적 서</div>
    </td>
  </tr>
  <tr>
    <td style="padding:10px 0 16px;font-size:12px;color:<?= $__muted ?>;">
      <table width="100%" cellpadding="0" cellspacing="0"><tr>
        <td style="font-size:12px;color:<?= $__muted ?>;">견적번호 <strong style="color:<?= $__ink ?>;"><?= $__h($__q['quote_no']) ?></strong><?= (int) $__q['revision'] > 1 ? ' (' . (int) $__q['revision'] . '차)' : '' ?></td>
        <td style="font-size:12px;color:<?= $__muted ?>;text-align:right;">견적일자 <strong style="color:<?= $__ink ?>;"><?= $__h(date('Y년 n월 j일', strtotime($__q['issued_at']))) ?></strong></td>
      </tr></table>
    </td>
  </tr>

  <!-- 수신 / 공급자 -->
  <tr>
    <td style="padding:0 0 16px;">
      <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
        <tr>
          <td width="44%" valign="top" style="padding:0 16px 0 0;">
            <div style="font-size:20px;font-weight:700;padding:4px 0 8px;border-bottom:1px solid <?= $__ink ?>;"><?= $__h($__recipient ?: '고객') ?> <span style="font-size:14px;font-weight:400;"><?= ($__c['company'] ?? '') !== '' ? '귀중' : '귀하' ?></span></div>
            <table cellpadding="0" cellspacing="0" style="margin-top:8px;font-size:12px;color:<?= $__muted ?>;line-height:1.7;">
              <?php if (($__c['company'] ?? '') !== '' && ($__c['name'] ?? '') !== ''): ?><tr><td style="padding-right:10px;">담당</td><td style="color:<?= $__ink ?>;"><?= $__h($__c['name']) ?></td></tr><?php endif; ?>
              <?php if (($__c['biz_no'] ?? '') !== ''): ?><tr><td style="padding-right:10px;">사업자번호</td><td style="color:<?= $__ink ?>;"><?= $__h($__c['biz_no']) ?></td></tr><?php endif; ?>
              <?php if (($__c['phone'] ?? '') !== ''): ?><tr><td style="padding-right:10px;">연락처</td><td style="color:<?= $__ink ?>;"><?= $__h($__c['phone']) ?></td></tr><?php endif; ?>
              <?php if (($__c['email'] ?? '') !== ''): ?><tr><td style="padding-right:10px;">이메일</td><td style="color:<?= $__ink ?>;"><?= $__h($__c['email']) ?></td></tr><?php endif; ?>
              <?php if (($__c['address'] ?? '') !== ''): ?><tr><td style="padding-right:10px;vertical-align:top;">주소</td><td style="color:<?= $__ink ?>;"><?= $__h($__c['address']) ?></td></tr><?php endif; ?>
            </table>
            <div style="margin-top:14px;font-size:13px;">아래와 같이 견적합니다.</div>
          </td>
          <td width="56%" valign="top">
            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
              <tr>
                <td rowspan="<?= count($__supplierRows) ?>" style="<?= $__th ?>width:22px;text-align:center;line-height:1.4;white-space:normal;">공<br>급<br>자</td>
                <td style="<?= $__th ?>width:70px;"><?= $__supplierRows[0][0] ?></td>
                <td style="<?= $__td ?>font-weight:600;"><?= $__h($__supplierRows[0][1]) ?></td>
              </tr>
              <?php foreach (array_slice($__supplierRows, 1) as [$__k, $__v]): ?>
              <tr><td style="<?= $__th ?>"><?= $__k ?></td><td style="<?= $__td ?>font-size:12px;"><?= $__h($__v) ?: '&nbsp;' ?></td></tr>
              <?php endforeach; ?>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- 합계금액 -->
  <tr>
    <td style="padding:0 0 16px;">
      <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
        <?php if (trim((string) ($__q['title'] ?? '')) !== ''): ?>
        <tr>
          <td style="<?= $__th ?>width:90px;text-align:center;font-size:13px;">건명</td>
          <td style="<?= $__td ?>font-size:14px;"><?= $__h($__q['title']) ?></td>
        </tr>
        <?php endif; ?>
        <tr>
          <td style="<?= $__th ?>width:90px;text-align:center;font-size:13px;">합계금액</td>
          <td style="<?= $__td ?>font-size:15px;font-weight:700;">일금 <?= $__h(quote_amount_korean((int) $__q['total_amount'])) ?>원정
            <span style="font-weight:400;font-size:13px;">(₩<?= $__won($__q['total_amount']) ?>)</span>
            <span style="font-weight:400;font-size:11px;color:<?= $__muted ?>;"> · 부가세 포함</span></td>
        </tr>
      </table>
    </td>
  </tr>


  <!-- 품목 -->
  <tr>
    <td style="padding:0 0 12px;">
      <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
        <tr>
          <td style="<?= $__th ?>width:28px;text-align:center;">No</td>
          <td style="<?= $__th ?>text-align:center;">품명</td>
          <td style="<?= $__th ?>text-align:center;">규격</td>
          <td style="<?= $__th ?>width:44px;text-align:center;">수량</td>
          <td style="<?= $__th ?>width:92px;text-align:center;">단가</td>
          <td style="<?= $__th ?>width:100px;text-align:center;">공급가액</td>
        </tr>
        <?php foreach ($__items as $__i => $__it): ?>
        <tr>
          <td style="<?= $__td ?>text-align:center;color:<?= $__muted ?>;"><?= $__i + 1 ?></td>
          <td style="<?= $__td ?>"><?= $__h($__it['name']) ?></td>
          <td style="<?= $__td ?>font-size:12px;"><?= $__h($__it['spec'] ?? '') ?></td>
          <td style="<?= $__td ?>text-align:right;"><?= $__qty($__it['qty']) ?></td>
          <td style="<?= $__td ?>text-align:right;"><?= $__won($__it['unit_price']) ?></td>
          <td style="<?= $__td ?>text-align:right;"><?= $__won($__it['amount']) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr>
          <td colspan="5" style="<?= $__th ?>text-align:right;">공급가액</td>
          <td style="<?= $__td ?>text-align:right;"><?= $__won($__q['supply_amount']) ?></td>
        </tr>
        <tr>
          <td colspan="5" style="<?= $__th ?>text-align:right;">세액 (10%)</td>
          <td style="<?= $__td ?>text-align:right;"><?= $__won($__q['vat_amount']) ?></td>
        </tr>
        <tr>
          <td colspan="5" style="<?= $__th ?>text-align:right;font-size:13px;">합계</td>
          <td style="<?= $__td ?>text-align:right;font-weight:700;"><?= $__won($__q['total_amount']) ?></td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- 조건·비고 -->
  <tr>
    <td style="padding:0 0 8px;">
      <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
        <?php if (!empty($__q['valid_until'])): ?>
        <tr><td style="<?= $__th ?>width:90px;">유효기한</td><td style="<?= $__td ?>"><?= $__h(date('Y년 n월 j일', strtotime($__q['valid_until']))) ?>까지</td></tr>
        <?php endif; ?>
        <?php if (!empty($__q['lead_time'])): ?>
        <tr><td style="<?= $__th ?>">납기</td><td style="<?= $__td ?>"><?= $__h($__q['lead_time']) ?></td></tr>
        <?php endif; ?>
        <?php if (($__s['bank'] ?? '') !== ''): ?>
        <tr><td style="<?= $__th ?>">입금계좌</td><td style="<?= $__td ?>"><?= $__h($__s['bank']) ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($__q['note'])): ?>
        <tr><td style="<?= $__th ?>vertical-align:top;">비고</td><td style="<?= $__td ?>line-height:1.6;"><?= nl2br($__h($__q['note'])) ?></td></tr>
        <?php endif; ?>
      </table>
    </td>
  </tr>
  <tr>
    <td style="padding:14px 0 0;text-align:right;font-size:13px;font-weight:600;"><?= $__h($__s['name'] ?? '워크그룹 평목') ?></td>
  </tr>
</table>
