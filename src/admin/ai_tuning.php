<?php
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../lib/meta.php'; meta_tags(); ?>
    <?php css_tag('/src/css/dashboard.css'); ?>
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
<style>
:root { --teal: var(--accent); --teal-light: var(--accent-tint); }

.at-wrap { max-width: 860px; margin: 0 auto; padding: 32px 20px 60px; }
.at-breadcrumb { font-size: 12px; color: var(--text); margin-bottom: 20px; }
.at-breadcrumb a { color: var(--text); text-decoration: none; }
.at-breadcrumb a:hover { color: var(--accent); }
.at-breadcrumb-sep { margin: 0 6px; }

.at-header { display: flex; align-items: center; gap: 12px; margin-bottom: 32px; }
.at-header-icon { width: 44px; height: 44px; border-radius: 12px; background: var(--accent); display: flex; align-items: center; justify-content: center; color: var(--bg); font-size: 20px; flex-shrink: 0; }
.at-title { font-size: 22px; font-weight: 700; color: var(--text); margin: 0; }
.at-subtitle { font-size: 13px; color: var(--text); margin: 2px 0 0; }

.at-section { background: var(--bg); border-radius: 14px; border: 1px solid var(--accent-tint); margin-bottom: 20px; overflow: hidden; }
.at-section-head { padding: 18px 24px; border-bottom: 1px solid var(--bg); display: flex; align-items: center; gap: 10px; }
.at-section-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
.at-section-title { font-size: 15px; font-weight: 700; color: var(--text); }
.at-section-desc { font-size: 12px; color: var(--text); margin-left: auto; }
.at-section-body { padding: 20px 24px; }

.at-engine-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.at-engine-row { display: flex; flex-direction: column; gap: 5px; }
.at-engine-label { display: flex; align-items: center; gap: 7px; }
.at-engine-key { font-size: 10px; font-weight: 700; letter-spacing: .05em; background: var(--text); color: var(--bg); border-radius: 4px; padding: 2px 6px; font-family: monospace; }
.at-engine-name { font-size: 12px; font-weight: 600; color: var(--text); }
.at-engine-title-input { border: none; background: transparent; font-size: 13px; font-weight: 700; color: var(--accent-hover); outline: none; width: 90px; padding: 1px 4px; cursor: default; pointer-events: none; }
.at-engine-input { border: 1.5px solid var(--accent-tint); border-radius: 8px; padding: 8px 10px; font-size: 12px; color: var(--text); width: 100%; outline: none; transition: border-color .15s; font-family: inherit; }
.at-engine-input:focus { border-color: var(--accent); background: var(--teal-light); }
.at-engine-input::placeholder { color: var(--border); }

.at-textarea { width: 100%; border: 1.5px solid var(--accent-tint); border-radius: 8px; padding: 12px 14px; font-size: 13px; font-family: 'Noto Sans KR', monospace; color: var(--text); resize: vertical; outline: none; transition: border-color .15s; line-height: 1.6; }
.at-textarea:focus { border-color: var(--accent); background: var(--teal-light); }
.at-textarea::placeholder { color: var(--border); }
.at-hint { font-size: 11px; color: var(--text); margin-top: 6px; }

/* 테스트 패널 */
.at-test-row { display: flex; gap: 10px; align-items: center; }
.at-test-select { border: 1.5px solid var(--accent-tint); border-radius: 8px; padding: 9px 12px; font-size: 13px; outline: none; background: var(--bg); color: var(--text); cursor: pointer; flex-shrink: 0; }
.at-test-input { flex: 1; border: 1.5px solid var(--accent-tint); border-radius: 8px; padding: 9px 14px; font-size: 13px; outline: none; transition: border-color .15s; font-family: inherit; }
.at-test-input:focus { border-color: var(--accent); background: var(--teal-light); }
.at-test-btn { background: var(--accent); color: var(--bg); border: none; border-radius: 8px; padding: 9px 20px; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; transition: opacity .15s; }
.at-test-btn:hover { opacity: .85; }
.at-test-btn:disabled { opacity: .45; cursor: default; }

.at-result { margin-top: 16px; border-radius: 10px; background: var(--text); padding: 16px; display: none; }
.at-result-label { font-size: 10px; color: var(--text); letter-spacing: .08em; text-transform: uppercase; margin-bottom: 8px; }
.at-result-reply { font-size: 14px; color: var(--accent-tint); font-weight: 600; margin-bottom: 10px; }
.at-result-params { font-size: 12px; color: var(--text); font-family: monospace; white-space: pre-wrap; }
.at-result-error { font-size: 13px; color: var(--danger); }

/* 도움말 */
.at-help { border-radius: 12px; border: 1.5px solid var(--accent-tint); background: var(--bg); margin-bottom: 16px; overflow: hidden; }
.at-help-head { display: flex; align-items: center; gap: 8px; padding: 13px 18px; cursor: pointer; user-select: none; font-size: 13px; font-weight: 600; color: var(--accent); }
.at-help-icon { font-size: 15px; color: var(--accent); }
.at-help-chevron { font-size: 12px; transition: transform .2s; }
.at-help.open .at-help-chevron { transform: rotate(180deg); }
.at-help-body { display: none; padding: 0 18px 16px; border-top: 1px solid var(--accent-tint); }
.at-help.open .at-help-body { display: block; }
.at-help-group { margin-top: 14px; }
.at-help-label { font-size: 11px; font-weight: 700; color: var(--text); letter-spacing: .06em; text-transform: uppercase; margin-bottom: 8px; }
.at-help-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.at-help-table tr td { padding: 5px 10px 5px 0; vertical-align: top; }
.at-help-table tr td:first-child { color: var(--accent-hover); white-space: nowrap; padding-right: 16px; width: 44%; }
.at-help-table tr td:last-child { color: var(--text); }
.at-help-table tr:not(:last-child) td { border-bottom: 1px solid var(--accent-tint); }
.at-help-list { margin: 0; padding-left: 18px; }
.at-help-list li { font-size: 12.5px; color: var(--accent-hover); line-height: 1.7; }
.at-help-list code { background: var(--accent-tint); color: var(--accent); border-radius: 4px; padding: 1px 5px; font-size: 11.5px; }
.at-help-table code { background: var(--accent-tint); color: var(--accent); border-radius: 4px; padding: 1px 5px; font-size: 11px; }
/* 메인 설정 탭 */
.at-mtabs { display: flex; border-bottom: 1px solid var(--accent-tint); padding: 0 20px; }
.at-mtab { border: none; background: none; font-size: 13px; font-weight: 600; color: var(--text); padding: 13px 14px; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -1px; display: flex; align-items: center; gap: 6px; transition: color .12s, border-color .12s; }
.at-mtab:hover { color: var(--accent-hover); }
.at-mtab.active { color: var(--accent); border-bottom-color: var(--accent); }
.at-mtab-pane { display: none; }
.at-mtab-pane.active { display: block; }

.at-htabs { display: flex; gap: 4px; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid var(--accent-tint); }
.at-htab { border: 1.5px solid var(--accent-tint); background: var(--bg); color: var(--text); font-size: 12px; font-weight: 600; border-radius: 7px; padding: 5px 13px; cursor: pointer; transition: all .12s; }
.at-htab:hover { border-color: var(--accent); color: var(--accent); }
.at-htab.active { background: var(--accent); border-color: var(--accent); color: var(--bg); }
.at-htab-pane { display: none; }
.at-htab-pane.active { display: block; }
.at-help-links { display: flex; flex-direction: column; gap: 6px; }
.at-help-link { display: inline-flex; align-items: center; gap: 7px; font-size: 12.5px; color: var(--accent); text-decoration: none; padding: 6px 10px; border-radius: 7px; border: 1px solid var(--accent-tint); background: var(--bg); transition: background .12s, border-color .12s; width: fit-content; }
.at-help-link:hover { background: var(--accent-tint); border-color: var(--accent); color: var(--accent); }

/* 저장 버튼 */
.at-save-row { display: flex; align-items: center; gap: 14px; padding: 4px 0; }
.at-save-btn { background: var(--text); color: var(--bg); border: none; border-radius: 10px; padding: 12px 36px; font-size: 14px; font-weight: 700; cursor: pointer; transition: opacity .15s; }
.at-save-btn:hover { opacity: .8; }
.at-save-msg { font-size: 13px; color: var(--accent); font-weight: 600; display: none; }
.at-stats-link { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: var(--accent); text-decoration: none; border: 1.5px solid var(--accent-tint); border-radius: 8px; padding: 6px 14px; background: var(--bg); transition: background .12s; }
.at-stats-link:hover { background: var(--accent-tint); }

.at-instr-row { display: flex; align-items: center; gap: 8px; }
.at-instr-num { font-size: 11px; font-weight: 700; color: var(--border); width: 18px; flex-shrink: 0; text-align: right; }
.at-instr-input { flex: 1; border: 1.5px solid var(--accent-tint); border-radius: 8px; padding: 8px 12px; font-size: 13px; font-family: inherit; color: var(--text); outline: none; transition: border-color .15s; }
.at-instr-input:focus { border-color: var(--accent); background: var(--teal-light); }
.at-instr-input::placeholder { color: var(--border); }
.at-instr-del { border: none; background: none; color: var(--border); font-size: 16px; cursor: pointer; padding: 4px; border-radius: 6px; flex-shrink: 0; line-height: 1; transition: color .12s, background .12s; }
.at-instr-del:hover { color: var(--danger); background: var(--danger-tint); }
.at-add-btn { border: 1.5px dashed var(--border); background: none; border-radius: 8px; padding: 8px 16px; font-size: 13px; color: var(--text); cursor: pointer; width: 100%; transition: border-color .15s, color .15s; }
.at-add-btn:hover { border-color: var(--accent); color: var(--accent); }
</style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div id="authWall" style="display:none;padding:60px 24px;text-align:center;color: var(--text);">슈퍼 권한이 필요합니다.</div>

<div class="at-wrap" id="mainPage" style="display:none;">
    <div class="at-breadcrumb"><a href="/src/admin/">어드민</a><span class="at-breadcrumb-sep">/</span>AI 튜닝</div>

    <div class="at-header">
        <div class="at-header-icon"><i class="bi bi-stars"></i></div>
        <div>
            <div class="at-title">AI 설계 도우미 튜닝</div>
            <div class="at-subtitle">엔진 별칭·지시사항을 설정하면 AI가 즉시 반영합니다</div>
        </div>
        <a href="/src/admin/ai_stats.php" class="at-stats-link ms-auto">
            <i class="bi bi-bar-chart-line"></i> 사용 통계
        </a>
    </div>

    <!-- 도움말 -->
    <div class="at-help">
        <div class="at-help-head" onclick="this.parentElement.classList.toggle('open')">
            <i class="bi bi-lightbulb at-help-icon"></i>
            <span>도움말</span>
            <i class="bi bi-chevron-down at-help-chevron ms-auto"></i>
        </div>
        <div class="at-help-body">
            <div class="at-htabs">
                <button class="at-htab active" data-tab="how">튜닝 방법</button>
                <button class="at-htab" data-tab="space">공간별 패턴</button>
                <button class="at-htab" data-tab="tip">작성 팁</button>
                <button class="at-htab" data-tab="links">콘솔 링크</button>
            </div>
            <div class="at-htab-pane active" data-pane="how">
                <ol class="at-help-list" style="padding-left:20px;">
                    <li><strong>Workbench에서 실험</strong> — 콘솔 Workbench에서 시스템 프롬프트를 자유롭게 테스트해 좋은 지시문 문구를 찾습니다.</li>
                    <li><strong>추가 지시사항에 입력</strong> — 찾은 지시문을 이 페이지 "추가 지시사항"에 항목으로 추가합니다.</li>
                    <li><strong>테스트 패널로 확인</strong> — 이 페이지 하단 "프롬프트 테스트"에서 실제 동작을 바로 확인합니다.</li>
                    <li><strong>저장</strong> — 저장 버튼을 누르면 즉시 반영됩니다. 별도 배포 불필요.</li>
                </ol>
            </div>
            <div class="at-htab-pane" data-pane="space">
                <table class="at-help-table">
                    <tr><td>음접실·접견실·회의실·공공기관</td><td><code>classic</code> — 격식 있고 절제된 느낌, doorType=swing</td></tr>
                    <tr><td>거실·침실·한옥 인테리어</td><td><code>square</code> — 아늑하고 전통적인 느낌</td></tr>
                    <tr><td>카페·레스토랑·상업 공간</td><td><code>hexagon</code> 또는 <code>diamond</code> — 현대적·개성 있는 느낌</td></tr>
                    <tr><td>욕실·화장실·파티션</td><td><code>cross</code> — 경쾌하고 가벼운 느낌</td></tr>
                    <tr><td>서재·작업실</td><td><code>classic</code> 또는 <code>triangle</code> — 차분하고 집중되는 느낌</td></tr>
                </table>
            </div>
            <div class="at-htab-pane" data-pane="tip">
                <ul class="at-help-list">
                    <li>공간 추천을 활성화하려면 "공간별 패턴" 탭 내용을 "추가 지시사항"에 항목으로 추가하세요.</li>
                    <li>예: <code>사용자가 공간 용도를 언급하며 패턴을 묻는 경우, 음접실→classic / 거실→square / 카페→hexagon 순으로 추천하라</code></li>
                    <li>어휘 해석: <code>'촘촘하게' = cols +3</code>, <code>'넓게' = cols -2</code> 등 구체적으로 쓸수록 정확합니다.</li>
                    <li>치수 단위를 생략하면 mm로 간주 등 예외 규칙도 추가할 수 있습니다.</li>
                </ul>
            </div>
            <div class="at-htab-pane" data-pane="links">
                <div class="at-help-links">
                    <a href="https://console.anthropic.com" target="_blank" class="at-help-link">
                        <i class="bi bi-box-arrow-up-right"></i> Anthropic Console — API 키·사용량 확인
                    </a>
                    <a href="https://console.anthropic.com/settings/usage" target="_blank" class="at-help-link">
                        <i class="bi bi-bar-chart-line"></i> 사용량 통계
                    </a>
                    <a href="https://console.anthropic.com/workbench" target="_blank" class="at-help-link">
                        <i class="bi bi-cpu"></i> Workbench — 프롬프트 직접 테스트
                    </a>
                    <a href="https://docs.anthropic.com/ko/docs/about-claude/models" target="_blank" class="at-help-link">
                        <i class="bi bi-book"></i> 모델 목록 및 사양
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 설정 탭 -->
    <div class="at-section">
        <div class="at-mtabs">
            <button class="at-mtab active" data-mtab="alias"><i class="bi bi-diagram-3"></i> 엔진 별칭</button>
            <button class="at-mtab" data-mtab="instr"><i class="bi bi-card-text"></i> 추가 지시사항</button>
            <button class="at-mtab" data-mtab="param"><i class="bi bi-code-slash"></i> 파라미터 설명</button>
            <button class="at-mtab" data-mtab="samples"><i class="bi bi-chat-quote"></i> 홈 샘플 프롬프트</button>
        </div>

        <div class="at-mtab-pane active at-section-body" data-mpane="alias">
            <div class="at-section-desc" style="margin-bottom:14px;">비워두면 기본값 사용</div>
            <div class="at-engine-grid">
                <?php
                $engines = [
                    'classic'  => '예: 띠살, 용자살, 가로세로살',
                    'square'   => '예: 亞자살, 방형살',
                    'cross'    => '예: 귀살, 사선살, 빗살문',
                    'diamond'  => '예: 능형살, 다이아살',
                    'triangle' => '예: 세살, 세모살',
                    'hexagon'  => '예: 어금육모, 벌집살',
                ];
                foreach ($engines as $key => $placeholder): ?>
                <div class="at-engine-row">
                    <div class="at-engine-label">
                        <span class="at-engine-key"><?= $key ?></span>
                        <input type="text" class="at-engine-title-input" data-engine="<?= $key ?>"
                               id="title_<?= $key ?>" readonly tabindex="-1">
                    </div>
                    <input type="text" class="at-engine-input" data-engine="<?= $key ?>"
                           id="alias_<?= $key ?>" placeholder="<?= $placeholder ?>">
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="at-mtab-pane at-section-body" data-mpane="instr">
            <div class="at-section-desc" style="margin-bottom:14px;">운영하며 발견한 예외 케이스를 항목별로 추가</div>
            <div id="instrList" style="display:flex;flex-direction:column;gap:8px;margin-bottom:10px;"></div>
            <button class="at-add-btn" onclick="addInstr()">
                <i class="bi bi-plus-lg"></i> 항목 추가
            </button>
            <div class="at-hint" style="margin-top:10px;">각 항목이 AI 시스템 프롬프트에 번호 목록으로 추가됩니다.</div>
        </div>

        <div class="at-mtab-pane at-section-body" data-mpane="param">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                <span id="paramDescStatus" style="font-size:12px;color: var(--text);"></span>
                <button id="btnParamReset" onclick="resetParamDesc()" style="font-size:11px;color: var(--text);border:1.5px solid var(--border);background:var(--bg);border-radius:7px;padding:4px 12px;cursor:pointer;display:none;">기본값으로 초기화</button>
            </div>
            <textarea id="aiParamDesc" class="at-textarea" rows="16" oninput="onParamDescInput()"></textarea>
            <div class="at-hint" style="margin-top:6px;">AI 시스템 프롬프트에 포함되는 파라미터 목록입니다. 새 파라미터 추가 시 수정하세요.</div>
        </div>

        <div class="at-mtab-pane at-section-body" data-mpane="samples">
            <div class="at-section-desc" style="margin-bottom:14px;">홈 화면 프롬프트 입력창 아래 노출되는 샘플 문구 — 클릭하면 입력창에 그대로 채워집니다</div>
            <div id="samplesList" style="display:flex;flex-direction:column;gap:8px;margin-bottom:10px;"></div>
            <button class="at-add-btn" onclick="addSample()">
                <i class="bi bi-plus-lg"></i> 항목 추가
            </button>
            <div class="at-hint" style="margin-top:10px;">보통 8~12개 정도가 적당합니다.</div>
        </div>
    </div>

    <!-- 테스트 -->
    <div class="at-section">
        <div class="at-section-head">
            <div class="at-section-icon" style="background:var(--warning-tint);color:var(--warning);"><i class="bi bi-send-check"></i></div>
            <div class="at-section-title">프롬프트 테스트</div>
            <div class="at-section-desc">저장 전 바로 테스트</div>
        </div>
        <div class="at-section-body">
            <div class="at-test-row">
                <select id="testEngine" class="at-test-select">
                    <option value="classic">classic</option>
                    <option value="square">square</option>
                    <option value="cross">cross</option>
                    <option value="diamond">diamond</option>
                    <option value="triangle">triangle</option>
                    <option value="hexagon">hexagon</option>
                </select>
                <input type="text" id="testMessage" class="at-test-input"
                       placeholder="예: 빗살 미서기문 가로 900 세로 1800으로 바꿔줘"
                       onkeydown="if(event.key==='Enter') runTest()">
                <button id="testBtn" class="at-test-btn" onclick="runTest()">
                    <i class="bi bi-send-fill me-1"></i>전송
                </button>
            </div>
            <div class="at-result" id="testResult">
                <div class="at-result-label">AI 응답</div>
                <div class="at-result-reply" id="testReply"></div>
                <div class="at-result-params" id="testParams"></div>
                <div class="at-result-error" id="testError" style="display:none;"></div>
            </div>
        </div>
    </div>

    <div class="at-save-row">
        <button class="at-save-btn" onclick="save()">저장</button>
        <span class="at-save-msg" id="saveMsg">✓ 저장되었습니다.</span>
    </div>
</div>

<script>
// 메인 설정 탭
document.querySelectorAll('.at-mtab').forEach(btn => {
    btn.addEventListener('click', () => {
        const pane = btn.dataset.mtab;
        document.querySelectorAll('.at-mtab').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.at-mtab-pane').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.querySelector(`[data-mpane="${pane}"]`).classList.add('active');
    });
});

// 도움말 탭
document.querySelectorAll('.at-htab').forEach(btn => {
    btn.addEventListener('click', () => {
        const pane = btn.dataset.tab;
        btn.closest('.at-help-body').querySelectorAll('.at-htab').forEach(b => b.classList.remove('active'));
        btn.closest('.at-help-body').querySelectorAll('.at-htab-pane').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        btn.closest('.at-help-body').querySelector(`[data-pane="${pane}"]`).classList.add('active');
    });
});

function renumList(containerId) {
    document.querySelectorAll(`#${containerId} .at-instr-num`).forEach((el, i) => el.textContent = (i + 1) + '.');
}
function addListItem(containerId, val, placeholder) {
    const row = document.createElement('div');
    row.className = 'at-instr-row';
    row.innerHTML = `<span class="at-instr-num"></span>
        <input type="text" class="at-instr-input" value="${val.replace(/"/g,'&quot;')}" placeholder="${placeholder}">
        <button class="at-instr-del" onclick="this.closest('.at-instr-row').remove();renumList('${containerId}');" title="삭제"><i class="bi bi-x"></i></button>`;
    document.getElementById(containerId).appendChild(row);
    renumList(containerId);
}
function getListItems(containerId) {
    return [...document.querySelectorAll(`#${containerId} .at-instr-input`)]
        .map(el => el.value.trim()).filter(Boolean);
}
function addInstr(val = '') {
    addListItem('instrList', val, "예: '촘촘하게'는 cols를 +3 증가로 해석하세요");
}
function getInstrs() {
    return getListItems('instrList');
}

const HOME_AI_SAMPLE_DEFAULTS = [
    '정자살 여닫이 2짝 900×2000', '완자살 미서기 3짝 1200×2100', '교살 여닫이 1짝 700×1800 흰색',
    '세모솟을살 미서기 2짝 1500×2200', '마름모살 여닫이 4짝 2000×2100', '육모솟을살 미서기 3짝 1800×2000',
    '완자살 작은 창 600×900', '정자살 넓은 통창 2400×2200', '교살 미닫이 2짝 어두운 원목톤', '마름모살 현관 중문 1000×2100',
];
function addSample(val = '') {
    addListItem('samplesList', val, '예: 정자살 여닫이 2짝 900×2000');
}
function getSamples() {
    return getListItems('samplesList');
}

function _h(json) {
    const h = { 'Authorization': 'Bearer ' + localStorage.getItem('pmok_auth_token') };
    if (json) h['Content-Type'] = 'application/json';
    return h;
}

// aliases: JSON object {classic: "...", square: "..."}
async function load() {
    const res = await fetch('/src/api/admin/ai_tuning.php', { headers: _h() });
    if (!res.ok) { document.getElementById('authWall').style.display = ''; return; }
    const d = await res.json();

    // 엔진 별칭 (stored as JSON in ai_engine_aliases)
    try {
        const aliases = JSON.parse(d.ai_engine_aliases || '{}');
        Object.entries(aliases).forEach(([k, v]) => {
            const el = document.getElementById('alias_' + k);
            if (el) el.value = v;
        });
    } catch {}

    // 엔진 한글 타이틀
    if (d.engine_titles) {
        Object.entries(d.engine_titles).forEach(([k, v]) => {
            const el = document.getElementById('title_' + k);
            if (el) el.value = v;
            // 테스트 드롭다운도 갱신
            const opt = document.querySelector(`#testEngine option[value="${k}"]`);
            if (opt) opt.textContent = v ? `${v} (${k})` : k;
        });
    }

    try {
        const instrs = JSON.parse(d.ai_extra_instructions || '[]');
        (Array.isArray(instrs) ? instrs : [instrs]).forEach(t => addInstr(t));
    } catch { if (d.ai_extra_instructions) addInstr(d.ai_extra_instructions); }
    if (document.getElementById('instrList').children.length === 0) addInstr();

    try {
        const samples = JSON.parse(d.home_ai_sample_prompts || '[]');
        (Array.isArray(samples) ? samples : []).forEach(t => addSample(t));
    } catch {}
    if (document.getElementById('samplesList').children.length === 0) {
        HOME_AI_SAMPLE_DEFAULTS.forEach(t => addSample(t));
    }

    window._paramDescDefault = d.ai_param_desc_default || '';
    window._paramDescCustom  = d.ai_param_desc || '';
    const textarea = document.getElementById('aiParamDesc');
    textarea.value = window._paramDescCustom || window._paramDescDefault;
    updateParamDescUI();
    document.getElementById('mainPage').style.display = '';
}

async function save() {
    const aliases = {};
    document.querySelectorAll('.at-engine-input').forEach(el => {
        if (el.value.trim()) aliases[el.dataset.engine] = el.value.trim();
    });

    const res = await fetch('/src/api/admin/ai_tuning.php', {
        method: 'POST',
        headers: _h(true),
        body: JSON.stringify({
            ai_engine_aliases:     JSON.stringify(aliases),
            ai_extra_instructions: JSON.stringify(getInstrs()),
            ai_param_desc:         document.getElementById('aiParamDesc').value,
            home_ai_sample_prompts: JSON.stringify(getSamples()),
        }),
    });
    if (res.ok) {
        const msg = document.getElementById('saveMsg');
        msg.style.display = '';
        setTimeout(() => msg.style.display = 'none', 2500);
    }
}

async function runTest() {
    const engine  = document.getElementById('testEngine').value;
    const message = document.getElementById('testMessage').value.trim();
    if (!message) return;

    const btn = document.getElementById('testBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>처리 중…';

    const resultBox = document.getElementById('testResult');
    resultBox.style.display = 'block';
    document.getElementById('testReply').textContent = '';
    document.getElementById('testParams').textContent = '';
    document.getElementById('testError').style.display = 'none';

    try {
        const res = await fetch('/src/api/ai/chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ engine, message, params: { W: 900, H: 1800, cols: 8, frame: 40, frameH: 40, slat: 12, doorType: 'swing', doorCount: 1 } }),
        });
        const data = await res.json();
        if (data.error) {
            document.getElementById('testError').textContent = '오류: ' + data.error;
            document.getElementById('testError').style.display = '';
        } else {
            document.getElementById('testReply').textContent = data.reply || '';
            const lines = Object.entries(data.params || {}).map(([k, v]) => `${k}: ${v}`);
            if (data.engine) lines.unshift('→ 엔진 전환: ' + data.engine);
            document.getElementById('testParams').textContent = lines.join('\n');
        }
    } catch (e) {
        document.getElementById('testError').textContent = '네트워크 오류';
        document.getElementById('testError').style.display = '';
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-send-fill me-1"></i>전송';
}

function updateParamDescUI() {
    const isCustom = document.getElementById('aiParamDesc').value.trim() !== (window._paramDescDefault || '').trim();
    document.getElementById('paramDescStatus').textContent = isCustom ? '✎ 커스텀' : '기본값 사용 중';
    document.getElementById('paramDescStatus').style.color  = isCustom ? 'var(--accent)' : 'var(--text-muted)';
    document.getElementById('btnParamReset').style.display  = isCustom ? '' : 'none';
}
function onParamDescInput() { updateParamDescUI(); }
function resetParamDesc() {
    if (!confirm('기본값으로 초기화하면 커스텀 내용이 삭제됩니다. 계속하시겠습니까?')) return;
    document.getElementById('aiParamDesc').value = window._paramDescDefault || '';
    window._paramDescCustom = '';
    updateParamDescUI();
}

window.addEventListener('load', load);
</script>
</body>
</html>
