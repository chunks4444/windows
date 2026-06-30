// 도면 DB 동기화 공용 유틸
// 각 엔진 JS보다 먼저 로드되어야 함

window.DrawingSync = (function () {

    function _token() { return localStorage.getItem('pmok_auth_token'); }
    function _headers() {
        return { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + _token() };
    }

    async function save(type, title, createdAt, versions, thumbnail = null, workTimeSec = 0, patternCategory = null) {
        if (!_token() || !title) return { ok: false, reason: 'no_token' };
        try {
            const res  = await fetch('/src/api/drawings/save.php', {
                method: 'POST',
                headers: _headers(),
                body: JSON.stringify({ type, title, created_at: createdAt, versions, thumbnail, work_time_sec: workTimeSec, pattern_category: patternCategory }),
            });
            const data = await res.json().catch(() => ({}));
            if (res.status === 401) return { ok: false, reason: 'auth' };
            if (!res.ok)           return { ok: false, reason: data.error || 'server_error' };
            return { ok: true, drawingId: data.drawing_id ?? null };
        } catch {
            return { ok: false, reason: 'network' };
        }
    }

    // title 있으면 특정 도면 + 버전 반환 { drawing, versions }
    // title 없으면 해당 타입의 도면 목록 반환 { drawings: [] }
    async function load(type, title) {
        if (!_token()) return null;
        try {
            const res  = await fetch('/src/api/drawings/load.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + _token() },
                body: JSON.stringify(title ? { type, title } : { type }),
            });
            return await res.json();
        } catch { return null; }
    }

    async function list(type) {
        const data = await load(type, '');
        return data?.drawings ?? [];
    }

    async function rename(type, oldTitle, newTitle) {
        if (!_token()) return false;
        try {
            const res  = await fetch('/src/api/drawings/rename.php', {
                method: 'POST',
                headers: _headers(),
                body: JSON.stringify({ type, old_title: oldTitle, new_title: newTitle }),
            });
            return (await res.json()).ok === true;
        } catch { return false; }
    }

    async function del(type, title) {
        if (!_token()) return false;
        try {
            const res  = await fetch('/src/api/drawings/delete.php', {
                method: 'POST',
                headers: _headers(),
                body: JSON.stringify({ type, title }),
            });
            return (await res.json()).ok === true;
        } catch { return false; }
    }

    function logExport(drawingId, engine, format, drawingName, version) {
        if (!_token()) return;
        fetch('/src/api/drawings/export_log.php', {
            method: 'POST',
            headers: _headers(),
            body: JSON.stringify({ drawing_id: drawingId || null, engine, format, drawing_name: drawingName || '', version: version || '' }),
        }).catch(() => {});
    }

    return { save, load, list, rename, delete: del, logExport };
})();

// ── 저장 완료 토스트 ──────────────────────────────
function pmShowSaveToast(msg = '저장을 완료했습니다.', sticky = false) {
    let el = document.getElementById('pmSaveToast');
    if (!el) {
        el = document.createElement('div');
        el.id = 'pmSaveToast';
        el.className = 'pm-save-toast';
        const anchor = document.querySelector('.canvas-title-bar') || document.body;
        anchor.appendChild(el);
    }
    el.textContent = msg;
    el.classList.remove('pm-save-toast--out');
    el.classList.add('pm-save-toast--in');
    clearTimeout(el._hideTimer);
    if (!sticky) {
        el._hideTimer = setTimeout(() => {
            el.classList.remove('pm-save-toast--in');
            el.classList.add('pm-save-toast--out');
        }, 2200);
    }
}

function pmHideSaveToast() {
    const el = document.getElementById('pmSaveToast');
    if (!el) return;
    clearTimeout(el._hideTimer);
    el.classList.remove('pm-save-toast--in');
    el.classList.add('pm-save-toast--out');
}

// ── 사이드바 반응형 초기화 ─────────────────────
(function () {
    const MOBILE = 768;

    function applyState() {
        const sidebar    = document.getElementById('sidebar');
        const tabBtn     = document.getElementById('btnSidebarTab');
        const rSidebar   = document.getElementById('rightSidebar');
        const rTabBtn    = document.getElementById('btnRightSidebarTab');
        if (!sidebar || !tabBtn) return;

        const isMobile = window.innerWidth <= MOBILE;

        if (isMobile) {
            sidebar.classList.add('collapsed');
            tabBtn.classList.add('collapsed');
            if (rSidebar) { rSidebar.classList.add('collapsed'); rTabBtn?.classList.add('collapsed'); }
        } else {
            sidebar.classList.remove('collapsed');
            tabBtn.classList.remove('collapsed');
            if (rSidebar) { rSidebar.classList.remove('collapsed'); rTabBtn?.classList.remove('collapsed'); }
        }
    }

    document.addEventListener('DOMContentLoaded', applyState);

    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(applyState, 150);
    });
})();
