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
                    <button type="submit" class="ct-submit" id="ctBtn">보내기</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
async function ctSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('ctBtn');
    btn.disabled = true;
    document.getElementById('ctError').style.display = 'none';
    try {
        const res  = await fetch('/src/api/contact/send.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name:    document.getElementById('ctName').value,
                email:   document.getElementById('ctEmail').value,
                subject: document.getElementById('ctSubject').value,
                message: document.getElementById('ctMessage').value,
            }),
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
