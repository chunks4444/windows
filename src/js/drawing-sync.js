// 도면 DB 동기화 공용 유틸
// 각 엔진 JS보다 먼저 로드되어야 함

window.DrawingSync = (function () {

    function _token() { return localStorage.getItem('pmok_auth_token'); }
    function _headers() {
        return { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + _token() };
    }

    async function save(type, title, createdAt, versions, thumbnail = null, workTimeSec = 0) {
        if (!_token() || !title) return;
        await fetch('/src/api/drawings/save.php', {
            method: 'POST',
            headers: _headers(),
            body: JSON.stringify({ type, title, created_at: createdAt, versions, thumbnail, work_time_sec: workTimeSec }),
        }).catch(() => {});
    }

    // title 있으면 특정 도면 + 버전 반환 { drawing, versions }
    // title 없으면 해당 타입의 도면 목록 반환 { drawings: [] }
    async function load(type, title) {
        if (!_token()) return null;
        const qs = title
            ? `type=${encodeURIComponent(type)}&title=${encodeURIComponent(title)}`
            : `type=${encodeURIComponent(type)}`;
        try {
            const res  = await fetch('/src/api/drawings/load.php?' + qs, {
                headers: { 'Authorization': 'Bearer ' + _token() },
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

    return { save, load, list, rename, delete: del };
})();
