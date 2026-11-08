<?php
// 어드민 > 블로그 > 검색 관리 — 이표기 묶음(term_aliases) 추가·삭제, 결과 0건 검색어(search_logs) 보기
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/admin_guard.php';
require_admin_role('s');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../lib/meta.php'; ?>
    <?php meta_tags(); ?>
<?php css_tag('/src/css/dashboard.css'); ?>
<?php css_tag('/src/css/admin.css'); ?>
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
    <style>
        .bsr-wrap { max-width:900px; display:grid; gap:28px; }
        .bsr-h { font-size:15px; font-weight:700; color:var(--text); margin:0 0 6px; }
        .bsr-desc { font-size:13px; color:var(--text-muted); margin:0 0 12px; line-height:1.6; }
        .bsr-card { border:1px solid var(--border); border-radius:var(--r); background:var(--bg); overflow:hidden; }
        .bsr-table { width:100%; border-collapse:collapse; font-size:14px; }
        .bsr-table th { padding:10px 12px; text-align:left; font-weight:600; color:var(--text); border-bottom:2px solid var(--border); }
        .bsr-table td { padding:9px 12px; border-bottom:1px solid var(--border); color:var(--text); vertical-align:middle; }
        .bsr-table tr:last-child td { border-bottom:none; }
        .bsr-group td { font-weight:600; }
        .bsr-chip { display:inline-flex; align-items:center; gap:6px; margin:2px 6px 2px 0; padding:3px 6px 3px 10px; border:1px solid var(--border); border-radius:999px; font-size:13px; font-weight:400; }
        .bsr-chip button { border:none; background:none; color:var(--text-muted); padding:0 4px; cursor:pointer; line-height:1; }
        .bsr-chip button:hover { color:var(--danger); }
        .bsr-add { display:flex; gap:8px; align-items:center; flex-wrap:wrap; padding:14px; border-top:1px solid var(--border); }
        .bsr-add input { border:1px solid var(--border); border-radius:6px; padding:7px 10px; font-size:14px; width:180px; background:color-mix(in srgb, var(--bg) 55%, #fff); color:var(--text); }
        .bsr-btn { border:none; border-radius:6px; padding:7px 16px; font-size:13px; font-weight:600; cursor:pointer; background:var(--accent); color:var(--bg); }
        .bsr-btn-sm { padding:4px 10px; font-size:12px; background:var(--bg); color:var(--text); border:1px solid var(--border); }
        .bsr-status { font-size:12px; } .bsr-status.ok { color:var(--accent); } .bsr-status.err { color:var(--danger); }
        .bsr-empty { padding:20px; text-align:center; color:var(--text-muted); font-size:14px; }
        .bsr-num { text-align:right; white-space:nowrap; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<?php include __DIR__ . '/../components/admin_sidenav.php'; ?>

<div class="db-page" id="bsrAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="bsrPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span><a href="/src/admin/blog.php">블로그</a><span class="adm-breadcrumb-sep">/</span>검색 관리</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-search me-2"></i>블로그 검색 관리</h1>
        <a href="/blog/search" target="_blank" rel="noopener" class="bsr-btn bsr-btn-sm" style="text-decoration:none;">검색 페이지 열기</a>
    </div>

    <div class="bsr-wrap">
        <section>
            <h2 class="bsr-h">이표기 묶음</h2>
            <p class="bsr-desc">
                같은 것을 다르게 쓰는 말을 묶어 두면, 그중 하나로 검색해도 묶음 전체를 함께 찾습니다(예: '소슬'로 찾으면 '솟을'이 든 글도 나옴).
                대표 표기 하나에 다른 표기를 여럿 붙일 수 있습니다.<br>
                <b>서로 다른 것으로 정리된 말은 넣지 마세요</b> — 예: 갑창과 두껍닫이는 다른 것(12화 「겹으로 다는 창」).
            </p>
            <div class="bsr-card">
                <table class="bsr-table">
                    <thead><tr><th style="width:30%;">대표 표기</th><th>다른 표기</th></tr></thead>
                    <tbody id="bsrAliasBody"><tr><td colspan="2" class="bsr-empty">불러오는 중…</td></tr></tbody>
                </table>
                <div class="bsr-add">
                    <input id="bsrAlias" placeholder="다른 표기 (예: 소슬)" maxlength="50">
                    <span>→</span>
                    <input id="bsrCanonical" placeholder="대표 표기 (예: 솟을)" maxlength="50">
                    <button class="bsr-btn" onclick="bsrAddAlias()">추가</button>
                    <span id="bsrAddStatus" class="bsr-status"></span>
                </div>
            </div>
        </section>

        <section>
            <h2 class="bsr-h">결과 0건 검색어</h2>
            <p class="bsr-desc">
                방문자가 찾았지만 맞는 글이 없었던 말입니다(많이 찾은 순). 관리자·봇의 검색은 기록하지 않습니다.
                표기만 다른 말이면 위 이표기 묶음에 넣고, 아니면 새 글 주제로 참고하세요.
                <span id="bsrRecent"></span>
            </p>
            <div class="bsr-card" style="overflow-x:auto;">
                <table class="bsr-table">
                    <thead><tr><th>검색어</th><th class="bsr-num" style="width:80px;">횟수</th><th style="width:150px;">마지막 검색</th><th style="width:110px;"></th></tr></thead>
                    <tbody id="bsrZeroBody"><tr><td colspan="4" class="bsr-empty">불러오는 중…</td></tr></tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<script src="/src/js/admin/blog_search.js?v=<?= md5_file(__DIR__ . '/../js/admin/blog_search.js') ?>"></script>
</body>
</html>
