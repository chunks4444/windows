const SERIES_API = '/src/api/admin/blog_series.php';
function _h() { return { 'Authorization': 'Bearer ' + localStorage.getItem('pmok_auth_token'), 'Content-Type': 'application/json' }; }
function esc(s) { return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }

let seriesList = [];

async function loadSeriesList() {
    const res  = await fetch(SERIES_API, { headers: _h() });
    const data = await res.json();
    seriesList = data.series || [];
    renderSeriesTable();
}

function renderSeriesTable() {
    document.getElementById('bsBody').innerHTML = seriesList.map(s => `
        <tr id="series-row-${s.id}">
            <td><input class="bs-name-input" id="series-name-${s.id}" value="${esc(s.name)}"></td>
            <td><input class="bs-tagline-input" id="series-tagline-${s.id}" value="${esc(s.tagline)}"></td>
            <td><input class="bs-sort-input" id="series-order-${s.id}" type="number" value="${s.sort_order}"></td>
            <td style="text-align:center;"><input type="checkbox" id="series-showhome-${s.id}" ${Number(s.show_on_home) ? 'checked' : ''}></td>
            <td style="text-align:center;"><input type="checkbox" id="series-completed-${s.id}" ${Number(s.is_completed) ? 'checked' : ''}></td>
            <td style="white-space:nowrap;">
                <button class="bs-btn bs-btn-save" onclick="saveSeriesRow(${s.id})">저장</button>
                <button class="bs-btn bs-btn-del" onclick="deleteSeriesRow(${s.id}, '${esc(s.name)}')">삭제</button>
                <span class="bs-status" id="series-status-${s.id}"></span>
            </td>
        </tr>`).join('') || '<tr><td colspan="6" style="text-align:center;color:var(--text);padding:20px;">시리즈가 없습니다.</td></tr>';
}

async function saveSeriesRow(id) {
    const name    = document.getElementById(`series-name-${id}`).value.trim();
    const tagline = document.getElementById(`series-tagline-${id}`).value.trim();
    const order   = parseInt(document.getElementById(`series-order-${id}`).value) || 0;
    const showOnHome = document.getElementById(`series-showhome-${id}`).checked;
    const completed  = document.getElementById(`series-completed-${id}`).checked;
    const st = document.getElementById(`series-status-${id}`);
    if (!name) { st.className = 'bs-status err'; st.textContent = '이름 필수'; return; }
    const data = await (await fetch(SERIES_API, { method: 'PUT', headers: _h(),
        body: JSON.stringify({ id, name, tagline, sort_order: order, show_on_home: showOnHome, is_completed: completed }) })).json();
    st.className = data.ok ? 'bs-status ok' : 'bs-status err';
    st.textContent = data.ok ? '저장됨' : (data.error || '오류');
    if (data.ok) setTimeout(() => st.textContent = '', 2000);
}

async function deleteSeriesRow(id, name) {
    if (!confirm(`"${name}" 시리즈를 삭제하시겠습니까? (연결된 글은 시리즈 없음으로 변경됩니다)`)) return;
    await fetch(SERIES_API, { method: 'DELETE', headers: _h(), body: JSON.stringify({ id }) });
    await loadSeriesList();
}

async function addSeries() {
    const name    = document.getElementById('bsAddName').value.trim();
    const tagline = document.getElementById('bsAddTagline').value.trim();
    const order   = parseInt(document.getElementById('bsAddOrder').value) || 0;
    const showOnHome = document.getElementById('bsAddShowOnHome').checked;
    const completed  = document.getElementById('bsAddCompleted').checked;
    const st = document.getElementById('bsAddStatus');
    if (!name) { st.className = 'bs-status err'; st.textContent = '이름을 입력하세요'; return; }
    const data = await (await fetch(SERIES_API, { method: 'POST', headers: _h(),
        body: JSON.stringify({ name, tagline, sort_order: order, show_on_home: showOnHome, is_completed: completed }) })).json();
    if (data.ok) {
        document.getElementById('bsAddName').value = '';
        document.getElementById('bsAddTagline').value = '';
        document.getElementById('bsAddOrder').value = '0';
        document.getElementById('bsAddCompleted').checked = false;
        st.className = 'bs-status ok'; st.textContent = '추가됨';
        await loadSeriesList();
        setTimeout(() => st.textContent = '', 2000);
    } else {
        st.className = 'bs-status err'; st.textContent = data.error || '오류';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const user = JSON.parse(localStorage.getItem('pmok_auth_user') || 'null');
    if (!user || user.role !== 's') { document.getElementById('bsAuthWall').style.display = ''; return; }
    document.getElementById('bsPage').style.display = '';
    loadSeriesList();
});
