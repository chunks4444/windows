<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>내 도면 — 평목</title>
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/src/css/dashboard.css">
</head>
<body>

<?php include __DIR__ . '/src/components/nav.php'; ?>

<div class="db-page" id="dbPage" style="display:none;">
    <div class="db-header">
        <h1 class="db-title">내 도면</h1>
    </div>
    <div id="dbContent"></div>
</div>

<!-- 비로그인 -->
<div class="db-page" id="dbAuthWall" style="display:none;">
    <div class="db-auth-banner">
        <p>도면을 저장하고 관리하려면 로그인이 필요합니다.</p>
        <button class="db-auth-btn" data-bs-toggle="modal" data-bs-target="#authModal">
            <i class="bi bi-person-circle"></i> 로그인
        </button>
    </div>
</div>

<script>
// 타입별 설정
const TYPE_CONFIG = {
    'sambuntok': {
        label: '삼분턱',
        editorUrl: '/src/engine/sambuntok/sambuntok.php',
        titleKey: 'pmok_sambuntok_current_title',
        icon: `<svg class="db-section-icon" viewBox="0 0 680 680" fill="none" xmlns="http://www.w3.org/2000/svg">
                 <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/>
                 <g transform="rotate(60 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
                 <g transform="rotate(120 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
               </svg>`,
        newUrl: '/src/engine/sambuntok/sambuntok.php',
    },
    'Sabunteok': {
        label: '사분턱',
        editorUrl: '/src/engine/Sabunteok/Sabunteok.php',
        titleKey: 'pmok_sabunteok_current_title',
        icon: `<svg class="db-section-icon" viewBox="0 0 680 680" fill="none" xmlns="http://www.w3.org/2000/svg">
                 <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/>
                 <rect fill="currentColor" x="148" y="317" width="384" height="46" rx="23"/>
                 <g transform="rotate(45 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
                 <g transform="rotate(135 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
               </svg>`,
        newUrl: '/src/engine/Sabunteok/Sabunteok.php',
    },
};

function fmtDate(ts) {
    const d  = new Date(ts);
    const yy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${yy}.${mm}.${dd}`;
}

function fmtWorkTime(sec) {
    if (!sec || sec < 60)  return '1분 미만';
    const h = Math.floor(sec / 3600);
    const m = Math.floor((sec % 3600) / 60);
    if (h > 0) return m > 0 ? `${h}시간 ${m}분` : `${h}시간`;
    return `${m}분`;
}

function openDrawing(type, title) {
    const cfg = TYPE_CONFIG[type];
    if (!cfg) return;
    localStorage.setItem(cfg.titleKey, title);
    location.href = cfg.editorUrl;
}

function renderSection(type, drawings) {
    const cfg = TYPE_CONFIG[type];
    if (!cfg) return '';

    const cards = drawings.length === 0
        ? `<div class="db-empty">저장된 도면이 없습니다.<br>에디터에서 도면을 저장하면 여기에 표시됩니다.</div>`
        : drawings.map(d => {
            const thumb = d.thumbnail
                ? `<img src="${escAttr(d.thumbnail)}" alt="${escAttr(d.title)}" loading="lazy">`
                : `<div class="db-thumb-placeholder">
                     <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                         <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
                     </svg>
                   </div>`;
            return `
                <div class="db-card" onclick="openDrawing('${escAttr(type)}', '${escAttr(d.title)}')">
                    <div class="db-thumb">${thumb}</div>
                    <div class="db-card-body">
                        <div class="db-card-title">${escHtml(d.title)}</div>
                        <div class="db-card-meta">
                            <div class="db-card-meta-row">
                                <i class="bi bi-clock"></i>
                                <span>작업 <strong>${fmtWorkTime(d.work_time_sec)}</strong></span>
                            </div>
                            <div class="db-card-meta-row">
                                <i class="bi bi-pencil"></i>
                                <span>수정 <strong>${fmtDate(new Date(d.updated_at).getTime())}</strong></span>
                            </div>
                        </div>
                    </div>
                </div>`;
        }).join('');

    return `
        <div class="db-section">
            <div class="db-section-header">
                ${cfg.icon}
                <span class="db-section-title">${cfg.label}</span>
                <span class="db-section-count">${drawings.length}개</span>
                <a href="${cfg.newUrl}" class="db-new-btn ms-auto" onclick="localStorage.removeItem('${cfg.titleKey}')">
                    <i class="bi bi-plus-lg"></i> 새 도면
                </a>
            </div>
            <div class="db-grid">${cards}</div>
        </div>`;
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function escAttr(str) {
    return String(str).replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

async function loadDashboard() {
    const token = localStorage.getItem('pmok_auth_token');
    if (!token) {
        document.getElementById('dbAuthWall').style.display = '';
        return;
    }

    document.getElementById('dbPage').style.display = '';
    document.getElementById('dbContent').innerHTML = '<div class="db-loading">불러오는 중…</div>';

    try {
        const res  = await fetch('/src/api/drawings/dashboard.php', {
            headers: { 'Authorization': 'Bearer ' + token }
        });
        const data = await res.json();

        if (!res.ok || data.error) {
            document.getElementById('dbContent').innerHTML = '<div class="db-loading">불러오기 실패</div>';
            return;
        }

        // 타입별로 그룹핑
        const grouped = {};
        (data.drawings || []).forEach(d => {
            if (!grouped[d.type]) grouped[d.type] = [];
            grouped[d.type].push(d);
        });

        // 알려진 타입 순서대로, 없는 타입도 빈 섹션으로
        const knownTypes = Object.keys(TYPE_CONFIG);
        const html = knownTypes.map(type =>
            renderSection(type, grouped[type] || [])
        ).join('');

        document.getElementById('dbContent').innerHTML = html || '<div class="db-loading">도면이 없습니다.</div>';
    } catch (e) {
        document.getElementById('dbContent').innerHTML = '<div class="db-loading">오류가 발생했습니다.</div>';
    }
}

document.addEventListener('DOMContentLoaded', loadDashboard);

// 로그인 완료 이벤트 수신
window.addEventListener('pmokAuthChanged', () => {
    document.getElementById('dbAuthWall').style.display = 'none';
    loadDashboard();
});
</script>

</body>
</html>
