// 도면 DB 동기화 공용 유틸
// 각 엔진 JS보다 먼저 로드되어야 함

window.DrawingSync = (function () {

    async function save(type, name, createdAt, versions) {
        const token = localStorage.getItem('pmok_auth_token');
        if (!token) return;
        await fetch('/src/api/drawings/save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
            body: JSON.stringify({ type, name, created_at: createdAt, modified_at: Date.now(), versions }),
        }).catch(() => {});
    }

    async function load(type) {
        const token = localStorage.getItem('pmok_auth_token');
        if (!token) return null;
        try {
            const res  = await fetch('/src/api/drawings/load.php?type=' + type, {
                headers: { 'Authorization': 'Bearer ' + token },
            });
            const data = await res.json();
            if (!data.drawing || !data.versions.length) return null;
            return data.versions;
        } catch { return null; }
    }

    return { save, load };
})();
