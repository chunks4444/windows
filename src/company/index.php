<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="평목은 전통창호와 한국 목공예의 미를 현대 공간에 담아내는 창호디자인 스튜디오입니다.">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;600;700;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/src/css/company.css">
    
</head>
<body>

<?php include __DIR__ . '/../components/nav.php'; ?>

<!-- HERO (full-width, outside container) -->
<section class="ab-hero">
    <div class="ab-hero-bg"></div>
    <div class="ab-hero-overlay"></div>
    <div class="ab-hero-content">
        <div class="container">
            <p class="ab-hero-label">About 평목</p>
            <h2 class="ab-hero-title">나무로 만드는<br>빛과 바람의 길,<em>평목</em></h2>
            <p class="ab-hero-desc">평목(平木)은 전통창호의 아름다움을 현대 공간에 담아내는 창호디자인 스튜디오입니다. <br>
            수백 년을 이어온 문살 기법을 디지털 도구로 재해석하여, 누구나 자신만의 창호를 <br>
            직접 설계하고 제작까지 연결할 수 있는 환경을 만들어갑니다.</p>
        </div>
    </div>
</section>

<!-- PHILOSOPHY — full-width box -->
<div class="ab-phil-box">
    <div class="container">
        <p class="ab-section-label">Philosophy</p>
        <div class="ab-phil-cols">
            <div class="ab-phil-left">
                <h2 class="ab-section-title">평목(平木)<br><span style="font-size:0.6em;font-weight:400;color:var(--text-3);letter-spacing:0.05em;">workgroup pyeongmok</span></h2>
                <div class="ab-phil-text">
                    <p>곡식을 '되'나 '말' 단위로 깎아낼 때 쓰는<br>도구를 '평미레'라고 하는데<br>여기에 해당하는 한자어가 '평목(平木)'입니다.</p>
                    <p>평목은 넘쳐도 아니 되고,<br>모자라도 아니 되는,<br>균형을 잡아 주는 일을 합니다.</p>
                    <p>견고한 나무를 다듬는 평목의 목수들은<br>시간에 마모되지 않고 시간 속에서<br>품격이 더해지는 창호와 가구를 만들고자 합니다.</p>
                </div>
            </div>
            <div class="ab-phil-right">
                <div class="ab-phil-grid">
                    <div class="ab-phil-item">
                        <p class="ab-phil-num">01</p>
                        <h3 class="ab-phil-name">오랜 시간 축적된 지혜와 다듬어진 원리를 공간에 담습니다.</h3>
                        <p class="ab-phil-desc">어금육모, 솟을살, 아자살 등 수백 년을 이어온 전통 문살 기법을 현대적 감각으로 재해석합니다. <br>형태는 단순해지고, 정신은 깊어집니다.</p>
                    </div>
                    <div class="ab-phil-item">
                        <p class="ab-phil-num">02</p>
                        <h3 class="ab-phil-name">실시간 설계</h3>
                        <p class="ab-phil-desc">치수와 비율을 조정하는 순간 도면이 실시간으로 완성됩니다. <br>설계와 제작 사이의 거리를 최소화하여 아이디어가 바로 현실이 됩니다.</p>
                    </div>
                    <div class="ab-phil-item">
                        <p class="ab-phil-num">03</p>
                        <h3 class="ab-phil-name">맞춤 제작</h3>
                        <p class="ab-phil-desc">모든 창호는 공간과 사람에 맞게 설계됩니다. <br>완성된 도면은 실제 제작으로 이어지며, 세상에 하나뿐인 창호가 탄생합니다.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- STUDIO -->
<div class="container">
    <section class="ab-section">
        <p class="ab-section-label">Studio</p>
        <h2 class="ab-section-title">직접 설계해 보세요.</h2>
        <p class="ab-section-body">평목 스튜디오는 브라우저에서 바로 사용할 수 있는 창호 설계 도구입니다. 문틀 크기, 살 간격, 패턴을 조정하며 나만의 창호를 완성해 보세요.</p>
        <div class="ab-tools-grid">
            <a href="/src/engine/Sabunteok/Sabunteok.php" class="ab-tool-card">
                <svg class="ab-tool-icon" width="48" height="48" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                    <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/>
                    <rect fill="currentColor" x="148" y="317" width="384" height="46" rx="4"/>
                    <g transform="rotate(45 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/></g>
                    <g transform="rotate(135 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/></g>
                </svg>
                <div>
                    <p class="ab-tool-name">Square Grid (사분턱)</p>
                    <p class="ab-tool-desc">가로살과 세로살이 직각으로 교차하는 정방형 문살 패턴. 단아하고 절제된 아름다움을 표현합니다.</p>
                </div>
            </a>
            <a href="/src/engine/sambuntok/sambuntok.php" class="ab-tool-card">
                <svg class="ab-tool-icon" width="48" height="48" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                    <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/>
                    <g transform="rotate(60 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/></g>
                    <g transform="rotate(120 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/></g>
                </svg>
                <div>
                    <p class="ab-tool-name">Triangle Grid (삼분턱)</p>
                    <p class="ab-tool-desc">세 방향의 살이 60° 각도로 교차하는 삼각형 문살 패턴. 역동적이고 세련된 인상을 공간에 더합니다.</p>
                </div>
            </a>
        </div>
    </section>
</div>

<!-- INFO — full-width box -->
<div class="ab-contact-box">
    <div class="container">
        <p class="ab-section-label">Contact</p>
        <h2 class="ab-section-title">함께 만들어가요.</h2>
        <p class="ab-section-body">설계 문의, 제작 상담, 협업 제안 모두 환영합니다.</p>
        <button class="ab-contact-btn" data-bs-toggle="modal" data-bs-target="#contactModal">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            이메일 문의하기
        </button>

        <div class="ab-contact-cols">
            <div class="ab-contact-info">
                <dl class="ab-contact-dl">
                    <div class="ab-contact-row">
                        <dt>주소</dt>
                        <dd>경기도 양평군 양서면 도곡리 107-2</dd>
                    </div>
                    <div class="ab-contact-row">
                        <dt>전화</dt>
                        <dd><a href="tel:+82705124458">070-5124-4568</a></dd>
                    </div>
                    <div class="ab-contact-row">
                        <dt>이메일</dt>
                        <dd><a href="mailto:pyeongmok@gmail.com">pyeongmok@gmail.com</a></dd>
                    </div>
                    <div class="ab-contact-row">
                        <dt>Instagram</dt>
                        <dd><a href="https://instagram.com/pyeongmok_1" target="_blank">@pyeongmok_1</a></dd>
                    </div>
                </dl>
            </div>
            <div class="ab-contact-map">
                <iframe
                    src="https://maps.google.com/maps?q=경기도+양평군+양서면+도곡리+107-2&output=embed&z=15"
                    width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>

        <div class="ab-gallery">
            <div class="ab-gallery-item">
                <img src="/src/img/web/IMG_0313.jpeg" alt="공방 사진 1">
            </div>
            <div class="ab-gallery-item">
                <img src="/src/img/web/IMG_0315.jpeg" alt="공방 사진 2">
            </div>
            <div class="ab-gallery-item">
                <img src="/src/img/web/IMG_0889.jpeg" alt="공방 사진 4">
            </div>
        </div>
    </div>
</div>

<!-- FOOTER -->
<div class="container">
    <div class="ab-footer">
        <p class="ab-footer-copy">© <?= date('Y') ?> 평목(平木). All rights reserved.</p>
        <a href="https://pyeongmok.com" class="ab-footer-link">pyeongmok.com</a>
    </div>
</div>

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
                    <small style="color:var(--text-3);font-size:12px;">pyeongmok@gmail.com</small>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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

// 모달 닫힐 때 폼 초기화
document.getElementById('contactModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('contactForm').reset();
    document.getElementById('contactForm').style.display = '';
    document.getElementById('ctSuccess').style.display = 'none';
    document.getElementById('ctError').style.display = 'none';
    document.getElementById('ctBtn').disabled = false;
});
</script>
</body>
</html>
