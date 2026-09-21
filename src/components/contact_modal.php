<?php require_once __DIR__ . '/../lib/i18n.php'; ?>
<!-- CONTACT MODAL -->
<div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content" id="contactModalContent">
            <div class="modal-header">
                <h5 class="modal-title"><?= htmlspecialchars(t('ct_title')) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size:11px;"></button>
            </div>
            <div class="modal-body">
                <div id="ctError" class="ct-error" style="display:none;"></div>
                <div id="ctSuccess" class="ct-success" style="display:none;">
                    <strong><?= htmlspecialchars(t('ct_success_strong')) ?></strong>
                    <?= htmlspecialchars(t('ct_success_body')) ?><br>
                    <small style="color:var(--text-muted);font-size:12px;">pyeongmok@gmail.com</small>
                </div>
                <form id="contactForm" onsubmit="ctSubmit(event)">
                    <!-- 허니팟 — 사람에겐 안 보이고 봇만 채워 넣는 미끼 필드 -->
                    <input type="text" id="ctWebsite" name="website" autocomplete="off" tabindex="-1"
                           style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;" aria-hidden="true">
                    <div class="ct-row-2">
                        <div class="ct-field">
                            <label><?= htmlspecialchars(t('ct_name')) ?></label>
                            <input type="text" id="ctName" placeholder="<?= htmlspecialchars(t('ct_name_ph')) ?>" required maxlength="50">
                        </div>
                        <div class="ct-field">
                            <label><?= htmlspecialchars(t('ct_email')) ?></label>
                            <input type="email" id="ctEmail" placeholder="example@email.com" required>
                        </div>
                    </div>
                    <div class="ct-field">
                        <label><?= htmlspecialchars(t('ct_subject')) ?></label>
                        <input type="text" id="ctSubject" placeholder="<?= htmlspecialchars(t('ct_subject_ph')) ?>" required maxlength="100">
                    </div>
                    <div class="ct-field">
                        <label><?= htmlspecialchars(t('ct_message')) ?></label>
                        <textarea id="ctMessage" rows="6" placeholder="<?= htmlspecialchars(t('ct_message_ph')) ?>" required maxlength="2000"></textarea>
                    </div>
                    <div class="ct-field">
                        <label><?= htmlspecialchars(t('ct_file')) ?></label>
                        <input type="file" id="ctFile" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.zip,.dwg,.dxf,.doc,.docx,.xls,.xlsx,.hwp">
                    </div>
                    <button type="submit" class="ct-submit" id="ctBtn"><?= htmlspecialchars(t('ct_submit')) ?></button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
const CT_T = <?= json_encode(['fileSize' => t('ct_err_file_size'), 'sendFail' => t('ct_msg_send_fail'), 'serverError' => t('auth_msg_server_error')], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
let ctOpenedAt = 0;
document.getElementById('contactModal').addEventListener('shown.bs.modal', function () {
    ctOpenedAt = Date.now();
});
async function ctSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('ctBtn');
    btn.disabled = true;
    document.getElementById('ctError').style.display = 'none';
    try {
        const fd = new FormData();
        fd.append('name', document.getElementById('ctName').value);
        fd.append('email', document.getElementById('ctEmail').value);
        fd.append('subject', document.getElementById('ctSubject').value);
        fd.append('message', document.getElementById('ctMessage').value);
        fd.append('website', document.getElementById('ctWebsite').value);
        fd.append('opened_at', ctOpenedAt);
        const fileEl = document.getElementById('ctFile');
        if (fileEl.files[0]) {
            if (fileEl.files[0].size > 10 * 1024 * 1024) {
                const el = document.getElementById('ctError');
                el.textContent = CT_T.fileSize;
                el.style.display = '';
                return;
            }
            fd.append('file', fileEl.files[0]);
        }

        const res  = await fetch('/src/api/contact/send.php', {
            method: 'POST',
            body: fd,
        });
        const data = await res.json();
        if (!res.ok) {
            const el = document.getElementById('ctError');
            el.textContent = data.error || CT_T.sendFail;
            el.style.display = '';
            return;
        }
        document.getElementById('contactForm').style.display = 'none';
        document.getElementById('ctSuccess').style.display = '';
    } catch {
        const el = document.getElementById('ctError');
        el.textContent = CT_T.serverError;
        el.style.display = '';
    } finally {
        btn.disabled = false;
    }
}
document.getElementById('contactModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('contactForm').reset();
    document.getElementById('contactForm').style.display = '';
    document.getElementById('ctSuccess').style.display = 'none';
    document.getElementById('ctError').style.display = 'none';
    document.getElementById('ctBtn').disabled = false;
});
</script>
