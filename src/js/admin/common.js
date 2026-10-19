// 어드민 페이지 공용 헬퍼 — src/components/admin_sidenav.php가 페이지 맨 위에서 불러온다.
// 페이지별 JS(src/js/admin/*.js)와 페이지 안 <script>는 전역 스코프를 같이 쓰므로,
// 여기 있는 이름(token, esc)을 다시 선언하지 말 것 — 특히 const/let으로 선언하면 페이지 전체 스크립트가 멈춘다.

// 로그인할 때 auth_modal.php가 저장해 두는 토큰 (API 호출 Authorization 헤더용)
function token() { return localStorage.getItem('pmok_auth_token'); }

// HTML 본문·속성값에 넣을 문자열 이스케이프 (null/undefined는 빈 문자열)
function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
