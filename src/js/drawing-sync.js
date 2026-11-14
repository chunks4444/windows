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
            _applyShareMeta(data);   // 저장으로 썸네일이 바뀌므로 카카오 공유 카드용 og 메타도 새 도면 이미지로
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

    // 공유를 켠 직후 화면의 og 메타(제목·설명·이미지)를 이 도면의 값으로 바꾼다.
    // 아직 공유 전이던 도면을 편집기로 열면 서버가 엔진 기본 메타(정자살 페이지 이미지 등)를 심어 두는데,
    // 각 엔진의 카카오 공유(shareToKakao)가 이 메타를 읽어 카드 이미지·제목으로 쓰기 때문에 도면 대신 엔진 이미지가 나갔다.
    function _applyShareMeta(d) {
        const set = (prop, val) => {
            if (!val) return;
            let m = document.querySelector('meta[property="' + prop + '"]');
            if (!m) { m = document.createElement('meta'); m.setAttribute('property', prop); document.head.appendChild(m); }
            m.setAttribute('content', val);
        };
        set('og:title', d.title);
        set('og:description', d.description);
        set('og:image', d.og_image);
    }

    // 공유 링크 끝에 붙일 도면 버전('&v=<썸네일 이름>'). 화면 og:image 주소에 들어 있는 썸네일 이름을 쓴다.
    // 도면을 다시 저장하면 썸네일이 새 이름이 되어 링크 주소도 달라지므로, 카카오·페이스북이 옛 미리보기를 재사용하지 않는다.
    function shareVersion() {
        const m = document.querySelector('meta[property="og:image"]');
        const t = /[?&]t=(\d+_[0-9a-f]{8})/.exec(m && m.content || '');
        return t ? '&v=' + t[1] : '';
    }

    async function setShared(id, shared) {
        if (!_token() || !id) return { ok: false };
        try {
            const res  = await fetch('/src/api/drawings/share.php', {
                method: 'POST',
                headers: _headers(),
                body: JSON.stringify({ id, shared }),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) return { ok: false, reason: data.error || 'server_error' };
            if (data.is_shared) _applyShareMeta(data);
            return { ok: true, is_shared: data.is_shared, share_url: data.share_url };
        } catch {
            return { ok: false, reason: 'network' };
        }
    }

    function logExport(drawingId, engine, format, drawingName, version) {
        if (!_token()) return;
        fetch('/src/api/drawings/export_log.php', {
            method: 'POST',
            headers: _headers(),
            body: JSON.stringify({ drawing_id: drawingId || null, engine, format, drawing_name: drawingName || '', version: version || '' }),
        }).catch(() => {});
    }

    return { save, load, list, rename, delete: del, setShared, shareVersion, logExport };
})();

// ── 저장 완료 토스트 ──────────────────────────────
function pmShowSaveToast(msg = '저장을 완료했습니다.', sticky = false) {
    let el = document.getElementById('pmSaveToast');
    if (!el) {
        el = document.createElement('div');
        el.id = 'pmSaveToast';
        el.className = 'pm-save-toast';
        // 레일 구조 엔진은 제목 바가 왼쪽 '파일' 탭으로 옮겨져 캔버스 위에 없으므로 캔버스 영역에 띄운다
        const anchor = document.querySelector('.canvas-title-bar') || document.querySelector('.canvas-area') || document.body;
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
