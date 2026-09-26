<?php
// 영문 본문. 한글 원문은 ../faq.php
// 질문·답변은 DB(faqs 테이블)에서 온다 — question_en/answer_en이 있으면 그걸, 없으면
// 한글 원문으로 폴백한다(db_field(), src/lib/i18n.php 참고).
$guide_current = 'faq.php';
$guide_title   = 'Frequently Asked Questions';
$guide_prev    = ['href' => 'delivery.php', 'title' => 'Delivery Guide'];
$guide_next    = null;
include __DIR__ . '/../_head.php';

require_once __DIR__ . '/../../lib/db.php';
$faqs = db()->query('SELECT * FROM faqs WHERE is_active=1 ORDER BY sort_order, id')->fetchAll();
?>

<h1>Frequently Asked Questions</h1>
<p class="guide-lead">
    Common questions that come up while using Workgroup Pyeongmok.
    If you can't find the answer you need, please <a href="<?= lang_href('/company/') ?>#contact">contact the workshop</a>.
</p>

<?php if (empty($faqs)): ?>
<p>No questions have been posted yet.</p>
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
                <?= htmlspecialchars(db_field($faq, 'question')) ?>
            </button>
        </h3>
        <div id="gfaq<?= (int)$faq['id'] ?>"
             class="accordion-collapse collapse<?= $i === 0 ? ' show' : '' ?>"
             data-bs-parent="#guideAccordion">
            <div class="faq-guide-body">
                <?= db_field($faq, 'answer') ?>
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
    background: var(--bg);
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
    background: transparent;
    border-top: 1px solid var(--border);
}
.faq-guide-body p { margin-bottom: 8px; }
.faq-guide-body p:last-child { margin-bottom: 0; }
.faq-guide-body ul, .faq-guide-body ol { padding-left: 20px; margin-bottom: 8px; }
</style>

<?php include __DIR__ . '/../_foot.php'; ?>
