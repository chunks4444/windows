<?php
// 로그인 필요 페이지에 include
// nav.php 보다 뒤에 위치해야 auth_modal.php 가 먼저 로드됨
?>
<script>
window.__pmokGuardedPage = true; // 로그인 후 이 페이지에 머물도록 표시

(function guardCheck() {
    if (localStorage.getItem('pmok_auth_token')) return;

    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('authModal');
        if (!modal) { location.href = '/'; return; }

        bootstrap.Modal.getOrCreateInstance(modal).show();

        modal.addEventListener('hidden.bs.modal', function () {
            if (!localStorage.getItem('pmok_auth_token')) {
                location.href = '/';
            }
        });
    });
})();
</script>
