<?php
// 로그인 필요 페이지에 include
// nav.php 보다 뒤에 위치해야 auth_modal.php 가 먼저 로드됨
?>
<script>
(function guardCheck() {
    if (localStorage.getItem('pmok_auth_token')) return;

    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('authModal');
        if (!modal) { location.href = '/'; return; }

        // 모달 열기
        bootstrap.Modal.getOrCreateInstance(modal).show();

        // 로그인 없이 닫으면 메인으로
        modal.addEventListener('hidden.bs.modal', function () {
            if (!localStorage.getItem('pmok_auth_token')) {
                location.href = '/';
            }
        });
    });
})();
</script>
