<?php
// 사용법: include 전에 $authRequireRole 변수 설정 가능 (예: 's', 'm' 등)
// 미설정 시 로그인 여부만 확인
$_guardRole = $authRequireRole ?? null;
?>
<script>
(function () {
    if (!localStorage.getItem('pmok_auth_token')) { location.replace('/'); return; }
    <?php if ($_guardRole): ?>
    try {
        var _u = JSON.parse(localStorage.getItem('pmok_auth_user') || 'null');
        if (!_u || _u.role !== '<?= htmlspecialchars($_guardRole, ENT_QUOTES) ?>') { location.replace('/'); }
    } catch (e) { location.replace('/'); }
    <?php endif; ?>
})();
</script>
