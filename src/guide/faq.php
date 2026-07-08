<?php
$guide_current = 'faq.php';
$guide_title   = '자주 묻는 질문 (FAQ)';
$guide_prev    = ['href' => 'delivery.php', 'title' => '배송 안내'];
$guide_next    = null;
include __DIR__ . '/_head.php';

require_once __DIR__ . '/../lib/db.php';
$faqs = db()->query('SELECT * FROM faqs WHERE is_active=1 ORDER BY sort_order, id')->fetchAll();
?>

<h1>자주 묻는 질문</h1>
<p class="guide-lead">
    평목 스튜디오 사용 중 자주 나오는 질문들을 모았습니다.
    원하는 답변을 찾지 못하셨다면 <a href="/company/#contact">공방 문의</a>를 이용해 주세요.
</p>

<?php if (empty($faqs)): ?>
<p>등록된 FAQ가 없습니다.</p>
<?php else: ?>
<div class="accordion faq-guide-accordion" id="guideAccordion">
    <?php foreach ($faqs as $i => $faq): ?>
    <div class="accordion-item faq-guide-item">
        <h3 class="accordion-header">
            <button class="accordion-button faq-guide-btn<?= $i > 0 ? ' collapsed' : '' ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#gfaq<?= (int)$faq['id'] ?>"
                    aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>">
                <?= htmlspecialchars($faq['question']) ?>
            </button>
        </h3>
        <div id="gfaq<?= (int)$faq['id'] ?>"
             class="accordion-collapse collapse<?= $i === 0 ? ' show' : '' ?>"
             data-bs-parent="#guideAccordion">
            <div class="faq-guide-body">
                <?= $faq['answer'] ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<style>
.faq-guide-accordion {
    margin-top: 24px;
    --bs-accordion-btn-padding-y: 18px;
    --bs-accordion-btn-padding-x: 20px;
}
.faq-guide-item h3 { margin: 0; }
.faq-guide-item {
    border: 1px solid var(--bs-border-color, var(--border)) !important;
    border-radius: 8px !important;
    margin-bottom: 8px;
    overflow: hidden;
}
.faq-guide-btn {
    font-size: 15px;
    font-weight: 600;
    background: var(--bg);
    color: var(--text);
    padding: var(--bs-accordion-btn-padding-y) var(--bs-accordion-btn-padding-x) !important;
    box-shadow: none !important;
}
.faq-guide-btn:not(.collapsed) {
    background: var(--bg);
    color: var(--accent);
}
.faq-guide-btn::after {
    filter: none;
}
.faq-guide-btn:not(.collapsed)::after {
    filter: none;
}
.faq-guide-body {
    padding: 16px 20px 22px;
    font-size: 14px;
    line-height: 1.75;
    color: var(--text);
    border-top: 1px solid var(--bg);
}
.faq-guide-body p { margin-bottom: 8px; }
.faq-guide-body p:last-child { margin-bottom: 0; }
.faq-guide-body ul, .faq-guide-body ol { padding-left: 20px; margin-bottom: 8px; }
</style>

<?php include __DIR__ . '/_foot.php'; ?>
