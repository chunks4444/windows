<!-- CONTACT MODAL -->
<div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content" id="contactModalContent">
            <div class="modal-header">
                <h5 class="modal-title">이메일 문의</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size:11px;"></button>
            </div>
            <div class="modal-body">
                <div id="ctError" class="ct-error" style="display:none;"></div>
                <div id="ctSuccess" class="ct-success" style="display:none;">
                    <strong>문의가 접수되었습니다.</strong>
                    확인 후 빠르게 답변 드리겠습니다.<br>
                    <small style="color:var(--text-muted);font-size:12px;">pyeongmok@gmail.com</small>
                </div>
                <form id="contactForm" onsubmit="ctSubmit(event)">
                    <!-- 허니팟 — 사람에겐 안 보이고 봇만 채워 넣는 미끼 필드 -->
                    <input type="text" id="ctWebsite" name="website" autocomplete="off" tabindex="-1"
                           style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;" aria-hidden="true">
                    <div class="ct-row-2">
                        <div class="ct-field">
                            <label>이름</label>
                            <input type="text" id="ctName" placeholder="홍길동" required maxlength="50">
                        </div>
                        <div class="ct-field">
                            <label>이메일 (답변 받을 주소)</label>
                            <input type="email" id="ctEmail" placeholder="example@email.com" required>
                        </div>
                    </div>
                    <div class="ct-field">
                        <label>제목</label>
                        <input type="text" id="ctSubject" placeholder="문의 제목" required maxlength="100">
                    </div>
                    <div class="ct-field">
                        <label>내용</label>
                        <textarea id="ctMessage" rows="6" placeholder="문의 내용을 입력해주세요." required maxlength="2000"></textarea>
                    </div>
                    <div class="ct-field">
                        <label>첨부파일 (선택, 최대 10MB)</label>
                        <input type="file" id="ctFile" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.zip,.dwg,.dxf,.doc,.docx,.xls,.xlsx,.hwp">
                    </div>
                    <button type="submit" class="ct-submit" id="ctBtn">보내기</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
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
                el.textContent = '첨부파일은 10MB 이하만 가능합니다.';
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
            el.textContent = data.error || '전송 실패';
            el.style.display = '';
            return;
        }
        document.getElementById('contactForm').style.display = 'none';
        document.getElementById('ctSuccess').style.display = '';
    } catch {
        const el = document.getElementById('ctError');
        el.textContent = '서버 오류가 발생했습니다.';
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
