<?php
// 사용법: include 전에 $authRequireRole 변수 설정 가능 (예: 's', 'm' 등)
// 미설정 시 로그인 여부만 확인
// $authGuardSkip = true 로 설정하면 가드 자체를 건너뜀 (예: 공유 링크로 들어온 비로그인 뷰어)
$_guardRole = $authRequireRole ?? null;
$_guardSkip = $authGuardSkip ?? false;
?>
<?php if (!$_guardSkip): ?>
<script>
window.addEventListener('load', function () {
    var token = localStorage.getItem('pmok_auth_token');

    <?php if ($_guardRole): ?>
    // 역할 필요 페이지 — 페이지가 다 그려진 뒤 검사, 토큰 없거나 역할 불일치 시 홈으로
    if (!token) { location.replace('/'); return; }
    try {
        var _u = JSON.parse(localStorage.getItem('pmok_auth_user') || 'null');
        if (!_u || _u.role !== '<?= htmlspecialchars($_guardRole, ENT_QUOTES) ?>') { location.replace('/'); }
    } catch (e) { location.replace('/'); }

    <?php else: ?>
    // 로그인 필요 페이지 — 모달을 띄우고, 로그인하면 이 페이지로 그대로 복귀
    if (!token) {
        try { sessionStorage.setItem('pmok_return_url', location.href); } catch (e) {}
        var el = document.getElementById('authModal');
        if (el && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(el).show();
        }
    }
    <?php endif; ?>
});
</script>
<?php endif; ?>
