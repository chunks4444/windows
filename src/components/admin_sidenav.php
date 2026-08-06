<?php
// 어드민 좌측 메뉴 트리 — 각 어드민 페이지 <body> 상단(nav.php 다음)에 include
// 새 어드민 페이지 추가 시 이 목록에도 같이 추가할 것 (/src/admin/은 stats.php로 리다이렉트되는 진입점)
require_once __DIR__ . '/../lib/meta.php';
css_tag('/src/css/admin_sidenav.css');

$admSidenavCurrent = basename($_SERVER['PHP_SELF']);
$admSidenavSections = require __DIR__ . '/../lib/admin_sidenav_data.php';
?>
<aside class="adm-sidenav" id="admSidenav">
    <?php foreach ($admSidenavSections as $admSec):
        $admSecActive = false;
        foreach ($admSec['items'] as $admIt) {
            if ($admIt[0] === $admSidenavCurrent) { $admSecActive = true; break; }
        }
    ?>
    <details class="adm-sidenav-group"<?= $admSecActive ? ' open' : '' ?>>
        <summary><?= htmlspecialchars($admSec['title']) ?></summary>
        <?php foreach ($admSec['items'] as $admIt): ?>
        <a href="/src/admin/<?= $admIt[0] ?>" class="adm-sidenav-link<?= $admIt[0] === $admSidenavCurrent ? ' active' : '' ?>">
            <i class="bi <?= $admIt[1] ?>"></i><span><?= htmlspecialchars($admIt[2]) ?></span>
        </a>
        <?php endforeach; ?>
    </details>
    <?php endforeach; ?>
</aside>
