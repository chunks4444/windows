<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../lib/admin_guard.php';
require_admin_role('s');
require_once __DIR__ . '/../lib/db.php';
try {
    $studioCards = db()->query('SELECT engine_key, title FROM studio_cards WHERE is_active=1 ORDER BY sort_order, id')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $studioCards = [
        ['engine_key'=>'classic',  'title'=>'Classic Lattice'],
        ['engine_key'=>'square',   'title'=>'Square Lattice'],
        ['engine_key'=>'cross',    'title'=>'Cross Lattice'],
        ['engine_key'=>'diamond',  'title'=>'Diamond Lattice'],
        ['engine_key'=>'triangle', 'title'=>'Triangle Lattice'],
        ['engine_key'=>'hexagon',  'title'=>'Hexagon Lattice'],
        ['engine_key'=>'mondrian', 'title'=>'Mondrian'],
    ];
}
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
    <?php css_tag('/src/css/users.css'); ?>
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
    <?php css_tag('/src/css/admin/cost_table.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>
<?php include __DIR__ . '/../components/admin_sidenav.php'; ?>

<div class="db-page" id="wtAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="wtPage" style="display:none;">
    <div class="adm-breadcrumb"><a href="/src/admin/">어드민</a><span class="adm-breadcrumb-sep">/</span>원가 테이블</div>
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-calculator me-2"></i>원가 테이블</h1>
        <button class="adm-edit-btn" style="height:32px;padding:0 14px;" onclick="openModal()">
            <i class="bi bi-plus-lg"></i> 추가
        </button>
    </div>

    <!-- 탭 -->
    <div class="adm-tab-bar" id="wtTabs" style="margin-bottom:16px;">
        <button class="adm-tab-btn active" data-tab="wood">목재</button>
        <button class="adm-tab-btn" data-tab="finish">마감</button>
        <button class="adm-tab-btn" data-tab="hardware">철물</button>
        <button class="adm-tab-btn" data-tab="labor">인건비</button>
        <button class="adm-tab-btn" data-tab="overhead">간접비</button>
        <button class="adm-tab-btn" data-tab="method">계산 방법</button>
    </div>

    <!-- 인건비 탭 — 인라인 편집 -->
    <div id="wtLaborPanel" style="display:none;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <select id="wtEngineSelect" class="form-select form-select-sm" style="width:200px;">
                <?php foreach ($studioCards as $c): ?>
                <option value="<?= htmlspecialchars($c['engine_key']) ?>"><?= htmlspecialchars($c['title']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="adm-edit-btn" id="btnLaborSave" style="height:32px;padding:0 16px;">저장</button>
        </div>
        <div class="wt-dim-grid">
            <!-- 기본 단가 (고정) -->
            <div class="wt-card">
                <div class="wt-card-title">기본 단가</div>
                <div class="adm-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>항목</th>
                                <th style="width:160px;text-align:right;">단가</th>
                                <th style="width:80px;">단위</th>
                            </tr>
                        </thead>
                        <tbody id="wtLaborBody"></tbody>
                    </table>
                </div>
            </div>
            <!-- 엔진별 작업 시간 -->
            <div class="wt-card">
                <div class="wt-card-title">엔진별 작업 시간</div>
                <p style="font-size:12px;color: var(--text);margin:0 0 12px;line-height:1.6;">
                    제작비 = (교차점 × 짝 × 교차점당 시간<br>
                    + 짝 × (울거미 + 다듬기) + 문틀) ÷ 60 × 시간당 공임
                </p>
                <div class="adm-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>항목</th>
                                <th style="width:100px;text-align:right;">시간 (분)</th>
                                <th style="width:40px;"></th>
                            </tr>
                        </thead>
                        <tbody id="wtLaborEngineBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 간접비 탭 — 인라인 편집 -->
    <div id="wtOverheadPanel" style="display:none;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <button class="adm-edit-btn" id="btnOverheadSave" style="height:32px;padding:0 16px;">저장</button>
        </div>
        <div class="wt-card" style="max-width:440px;">
            <div class="wt-card-title">비율 설정</div>
            <p style="font-size:12px;color: var(--text);margin:0 0 12px;line-height:1.6;">
                판매가 = 원가 합계 × (1 + 간접비율 + 이익률)
            </p>
            <div class="adm-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>항목</th>
                            <th style="width:120px;text-align:right;">비율 (%)</th>
                            <th style="width:60px;"></th>
                        </tr>
                    </thead>
                    <tbody id="wtOverheadBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 계산 방법 탭 — 읽기 전용 안내. 공식이 바뀌면 이 내용도 함께 고칠 것:
         공통식 src/lib/engine_settings.php compute_price_estimate(), 엔진별 부재·짜임 수 src/engine/{엔진}/api/geometry.php -->
    <div id="wtMethodPanel" style="display:none;">
        <div class="wt-method-grid">
            <div class="wt-card wt-method-wide">
                <div class="wt-card-title">공통 계산 순서 (7개 엔진 동일)</div>
                <ol class="wt-method-list">
                    <li><b>목재비</b> = (문 목재 재수 + 문틀 목재 재수) × 수종 무게 계수 × 수종 1재 단가. 1재 = 33×33×3600mm 부피로 환산하고, 문 목재는 문짝 수만큼 곱합니다.</li>
                    <li><b>제작비</b> = (짜임 수 × 짝 × 짜임당 시간 + 짝 × (울거미 + 다듬기 시간) + 문틀 시간) ÷ 60 × 시간당 공임. 시간은 인건비 탭의 엔진별 값을 씁니다. 화면에 보이는 작업 시간과 납기에는 엔진별 최소 작업 시간이 하한으로 적용됩니다.</li>
                    <li><b>부자재</b> = 짝 × 선택한 철물 단가</li>
                    <li><b>마감</b> = 도장 면적(살 길이를 면적으로 환산) × 도포 횟수 × 마감재 ㎡ 단가 + 마감 작업 시간 × 마감 공임</li>
                    <li><b>판매가</b> = (목재비 + 제작비 + 부자재 + 마감) × (1 + 간접비율 + 이익률)</li>
                </ol>
                <p class="wt-method-note">울거미: 세로 2개 × (외경 높이 + 살 두께×2), 가로 2개(풍판 사용 시 3개) × (외경 폭 + 살 두께×2) — 모든 엔진 동일. 살 길이에는 양 끝 장부(살 두께만큼)가 더해집니다.</p>
                <p class="wt-method-note"><b>예상원가에 반영하지 않는 것:</b> 캔버스에서 선 추가·삭제로 편집한 내용(몬드리안 제외)은 예상원가에 들어가지 않습니다. 견적 요청을 검토할 때 도면을 보고 사람이 판단합니다.</p>
            </div>

            <div class="wt-card">
                <div class="wt-card-title">세살 (classic)</div>
                <table class="wt-method-table"><tbody>
                    <tr><th>살</th><td>가로살 (상 + 중 + 하 가로살 개수)개 × 내경 폭, 세로살 (가로 칸수 − 1)개 × 내경 높이</td></tr>
                    <tr><th>짜임 수</th><td>가로 칸수 × (가로살로 나뉜 세로 구간 수)</td></tr>
                </tbody></table>
            </div>
            <div class="wt-card">
                <div class="wt-card-title">정자살 (square)</div>
                <table class="wt-method-table"><tbody>
                    <tr><th>살</th><td>가로살 (세로 칸수 − 1)개 × 내경 폭, 세로살 (가로 칸수 − 1)개 × 내경 높이</td></tr>
                    <tr><th>짜임 수</th><td>가로 칸수 × 세로 칸수 (칸 수)</td></tr>
                </tbody></table>
            </div>
            <div class="wt-card">
                <div class="wt-card-title">빗살 (cross)</div>
                <table class="wt-method-table"><tbody>
                    <tr><th>살</th><td>45° 사선살만 사용. 방향별(↘·↗)로 사선마다 지나가는 칸 수 × 칸 대각선 길이 + 장부로 길이를 구해, 길이별 개수를 합산</td></tr>
                    <tr><th>짜임 수</th><td>가로 칸수 × 세로 칸수 (칸 수)</td></tr>
                </tbody></table>
            </div>
            <div class="wt-card">
                <div class="wt-card-title">격자빗살 (diamond)</div>
                <table class="wt-method-table"><tbody>
                    <tr><th>살</th><td>정자살과 같은 가로살·세로살 + 45° 사선살(길이별 개수 합산)</td></tr>
                    <tr><th>짜임 수</th><td>가로 칸수 × 세로 칸수 (칸 수)</td></tr>
                </tbody></table>
            </div>
            <div class="wt-card">
                <div class="wt-card-title">세모솟을살 (triangle)</div>
                <table class="wt-method-table"><tbody>
                    <tr><th>살</th><td>기준 방향 살(패턴 세로 방향이면 세로살 (가로 칸수 − 1)개 × 내경 높이, 아니면 가로살 (세로 칸수 − 1)개 × 내경 폭) + 60°·120° 사선살(길이별 개수 합산)</td></tr>
                    <tr><th>짜임 수</th><td>가로 칸수 × 세로 칸수</td></tr>
                </tbody></table>
            </div>
            <div class="wt-card">
                <div class="wt-card-title">육모솟을살 (hexagon)</div>
                <table class="wt-method-table"><tbody>
                    <tr><th>살</th><td>세로살 (가로 칸수 − 1)개 × 내경 높이 + 사선살(길이별 개수 합산)</td></tr>
                    <tr><th>짜임 수</th><td>가로 칸수 × 세로 칸수</td></tr>
                </tbody></table>
            </div>
            <div class="wt-card">
                <div class="wt-card-title">몬드리안 (mondrian)</div>
                <table class="wt-method-table"><tbody>
                    <tr><th>살</th><td>캔버스에 실제로 그려진 살(랜덤 생성 + 직접 그은 선 − 삭제한 선)의 개수와 길이 합 + 살마다 양 끝 장부</td></tr>
                    <tr><th>짜임 수</th><td>살 개수 × 2. 살이 서로 교차하지 않고 양 끝이 다른 살이나 울거미에 맞물리기 때문</td></tr>
                    <tr><th>비고</th><td>선 편집이 예상원가에 바로 반영되는 유일한 엔진. 선을 드래그하는 동안에는 계산을 멈추고 놓을 때 다시 계산합니다.</td></tr>
                </tbody></table>
            </div>
        </div>
    </div>

    <!-- 나머지 탭 공용 테이블 -->
    <div id="wtTableWrap" class="adm-table-wrap">
        <table id="wtTable">
            <thead>
                <tr id="wtColHeads"></tr>
            </thead>
            <tbody id="wtBody"></tbody>
        </table>
    </div>
</div>

<!-- 편집 모달 -->
<div class="adm-modal-overlay" id="wtModalOverlay">
    <div class="adm-modal" style="max-width:420px;">
        <div class="adm-modal-head">
            <h3 id="wtModalTitle">항목 추가</h3>
            <button class="adm-modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="adm-modal-body">
            <input type="hidden" id="wtId">
            <input type="hidden" id="wtCategory">
            <div class="adm-mfield">
                <label>항목</label>
                <input id="wtName" type="text" placeholder="예: 홍송" maxlength="100">
            </div>
            <div id="wtPriceRow" class="wt-price-row">
                <div class="adm-mfield wt-price-field">
                    <label>단가</label>
                    <input id="wtUnitPrice" type="number" min="0" step="1" placeholder="12000" class="num-input">
                </div>
                <div class="adm-mfield wt-unit-field">
                    <label>단위</label>
                    <input id="wtUnit" type="text" placeholder="사이, 분, %…" maxlength="30">
                </div>
                <div class="adm-mfield wt-unit-field">
                    <label>단위명</label>
                    <input id="wtUnitName" type="text" placeholder="재(才)…" maxlength="50">
                </div>
            </div>
            <div id="wtFinishRow" style="display:none;">
                <div style="display:flex;gap:8px;margin-bottom:0;">
                    <div class="adm-mfield" style="flex:1;">
                        <label>작업 시간 <small style="color: var(--text);font-weight:400;">(분/㎡)</small></label>
                        <input id="wtWorkTimeMin" type="number" min="0" step="1" placeholder="10" class="num-input">
                    </div>
                    <div class="adm-mfield" style="flex:1;">
                        <label>도포 횟수 <small style="color: var(--text);font-weight:400;">(회)</small></label>
                        <input id="wtCoatCount" type="number" min="1" step="1" placeholder="2" class="num-input">
                    </div>
                </div>
            </div>
            <div id="wtWeightRow" class="adm-mfield">
                <label>가중치</label>
                <input id="wtWeight" type="number" min="0" step="0.01" placeholder="1.00" class="num-input">
            </div>
            <div class="adm-mfield" id="wtEngineField" style="display:none;">
                <label>엔진 <small style="color: var(--text);font-weight:400;">(비우면 공통)</small></label>
                <select id="wtEngine">
                    <option value="">— 공통 —</option>
                    <?php foreach ($studioCards as $c): ?>
                    <option value="<?= htmlspecialchars($c['engine_key']) ?>"><?= htmlspecialchars($c['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="adm-mfield">
                <label>메모</label>
                <textarea id="wtNotes" rows="3"
                    style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:var(--r-sm);background:var(--bg);font-family:inherit;font-size:13px;color:var(--text);outline:none;resize:vertical;"></textarea>
            </div>
        </div>
        <div class="adm-modal-foot">
            <button class="adm-btn-cancel" onclick="closeModal()">취소</button>
            <button class="adm-btn-save" onclick="saveItem()">저장</button>
        </div>
    </div>
</div>

<script>
window.__pmokEngineLabels = <?= json_encode(array_column($studioCards, 'title', 'engine_key'), JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="/src/js/admin/cost_table.js"></script>
</body>
</html>
