<?php
// 상단 네비게이션 공통 컴포넌트
// 포함하는 페이지에서 Bootstrap CSS/JS가 없으면 자동으로 로드합니다.
if (!defined('BOOTSTRAP_LOADED')) {
    define('BOOTSTRAP_LOADED', true);
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">';
}
?>
<style>
/* ── 고정 네비 여백 ─────────────────────────────────── */
body { padding-top: 68px; }


/* ── NAV (공통) ───────────────────────────────────── */
.pm-navbar {
    background: linear-gradient(to bottom, rgba(255,255,255,0.92) 0%, rgba(255,255,255,0) 100%);
    font-family: 'Inter', -apple-system, sans-serif;
}
.pm-navbar .navbar-brand { text-decoration: none; color: inherit; }
.pm-nav-logo {
    height: 32px;
    filter: brightness(0);
    opacity: 0.85;
}
.pm-nav-tagline {
    font-size: 12px;
    font-weight: 600;
    line-height: 1.6;
    border-left: 1px solid rgba(0,0,0,0.3);
    padding-left: 12px;
    margin-left: 12px;
}
.pm-navbar .nav-link {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.02em;
    color: #000 !important;
}
.pm-navbar .dropdown-menu {
    min-width: 180px;
    border: 1px solid rgba(12,12,11,0.1);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    border-radius: 0;
    padding: 14px 0 6px;
}
.pm-navbar .dropdown-item {
    font-size: 12px;
    font-weight: 400;
    padding: 10px 18px;
}
.pm-navbar .dropdown-item svg { transition: transform .5s cubic-bezier(.25,.46,.45,.94); }
.pm-navbar .dropdown-item:hover svg { transform: rotate(45deg); }
.pm-navbar .dropdown-item:hover { background: rgba(12,12,11,0.04); color: #cc2200; }
.pm-navbar .dropdown-item.active { font-weight: 600; color: #cc2200; background: none; }
@media (max-width: 768px) {
    .pm-navbar { background: rgba(255,255,255,0.97); }
}
</style>

<?php
// 현재 페이지 파일명으로 active 항목 판별
$currentFile = basename($_SERVER['PHP_SELF']);
$isSabunteok  = (stripos($currentFile, 'Sabunteok') !== false);
$isSambuntok  = (stripos($currentFile, 'sambuntok') !== false);
$isIndex      = ($currentFile === 'index.php' || $_SERVER['PHP_SELF'] === '/');
?>
<nav class="pm-navbar navbar navbar-expand-lg fixed-top px-4 py-3">
    <a href="/" class="navbar-brand d-flex align-items-center">
        <img src="https://pyeongmok.com/wp-content/uploads/2024/01/2024_MYEONGMOK_LOGO-e1705317786643.png"
             alt="평목" class="pm-nav-logo">
        <span class="pm-nav-tagline">시간 속에서 품격이<br>더해지는</span>
    </a>
    <button class="navbar-toggler border-0" type="button"
            data-bs-toggle="collapse" data-bs-target="#pmNavMenu"
            aria-controls="pmNavMenu" aria-expanded="false">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="pmNavMenu">
        <ul class="navbar-nav gap-3">
            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Generator</a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item <?= $isSabunteok ? 'active' : '' ?> d-flex align-items-center gap-2"
                           href="/src/engine/Sabunteok/Sabunteok.php">
                        <svg width="27" height="27" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                            <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/>
                            <rect fill="currentColor" x="148" y="317" width="384" height="46" rx="23"/>
                            <g transform="rotate(45 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
                            <g transform="rotate(135 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
                        </svg>
                        사분턱
                    </a></li>
                    <li><a class="dropdown-item <?= $isSambuntok ? 'active' : '' ?> d-flex align-items-center gap-2"
                           href="/src/engine/sambuntok/sambuntok.php">
                        <svg width="27" height="27" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                            <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/>
                            <g transform="rotate(60 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
                            <g transform="rotate(120 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
                        </svg>
                        삼분턱
                    </a></li>
                </ul>
            </li>
            <li class="nav-item"><a href="#" class="nav-link">Pattern Library</a></li>
            <li class="nav-item"><a href="#" class="nav-link">Works</a></li>
            <?php if ($isIndex): ?>
            <li class="nav-item"><a href="#" class="nav-link">Login</a></li>
            <li class="nav-item"><a href="#" class="nav-link">Sign In</a></li>
            <?php else: ?>
            <li class="nav-item"><a href="#" class="nav-link">My page</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<?php if (!defined('BOOTSTRAP_JS_LOADED')): define('BOOTSTRAP_JS_LOADED', true); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
<?php endif; ?>
