<?php
// 사용법: include 전에 $authRequireRole 변수 설정 가능 (예: 's', 'm' 등)
// 미설정 시 로그인 여부만 확인
$_guardRole = $authRequireRole ?? null;
?>
<script>
(function () {
    var token = localStorage.getItem('pmok_auth_token');

    <?php if ($_guardRole): ?>
    // 역할 필요 페이지 — 토큰 없거나 역할 불일치 시 항상 홈으로
    if (!token) { location.replace('/'); return; }
    try {
        var _u = JSON.parse(localStorage.getItem('pmok_auth_user') || 'null');
        if (!_u || _u.role !== '<?= htmlspecialchars($_guardRole, ENT_QUOTES) ?>') { location.replace('/'); }
    } catch (e) { location.replace('/'); }

    <?php else: ?>
    // 로그인 필요 페이지 — 외부/직접 접근이면 홈으로, 내부 접근이면 모달
    if (!token) {
        var ref = document.referrer;
        var isInternal = ref && (function () {
            try { return new URL(ref).origin === location.origin; } catch (e) { return false; }
        })();
        if (!isInternal) {
            location.replace('/');
        } else {
            window.__pmokGuardedPage = true;
            document.addEventListener('DOMContentLoaded', function () {
                var el = document.getElementById('authModal');
                if (el && window.bootstrap) {
                    bootstrap.Modal.getOrCreateInstance(el).show();
                }
            });
        }
    }
    <?php endif; ?>
})();
</script>
