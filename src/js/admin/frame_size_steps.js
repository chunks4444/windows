const hdr = () => ({ 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token() });

const DOOR_TYPE_LABEL = { swing: '여닫이', slide: '미서기' };
let allSteps = [];

async function loadSteps() {
    const res  = await fetch('/src/api/admin/frame_size_steps.php', { headers: hdr() });
    const data = await res.json();
    if (!res.ok) { alert(data.error || '불러오기 실패'); return; }
    allSteps = data.rows || [];
    renderGroups();
    document.getElementById('fssPage').style.display = '';
}

function renderGroups() {
    const box = document.getElementById('fssGroups');
    const doorTypes = [...new Set(allSteps.map(r => r.door_type))];
    box.innerHTML = doorTypes.map(dt => {
        const rows = allSteps.filter(r => r.door_type === dt).sort((a, b) => a.stage_order - b.stage_order);
        return `
        <div class="adm-table-wrap" style="margin-top:20px;">
            <div style="font-size:14px;font-weight:700;margin-bottom:8px;">${esc(DOOR_TYPE_LABEL[dt] || dt)}</div>
            <table>
                <thead>
                    <tr>
                        <th style="width:60px;">단계</th>
                        <th style="width:160px;">문 높이 상한(mm)</th>
                        <th style="width:160px;">두께(mm)</th>
                        <th style="width:160px;">나비(mm)</th>
                    </tr>
                </thead>
                <tbody>
                    ${rows.map(r => `
                    <tr data-id="${r.id}">
                        <td>${r.stage_order}</td>
                        <td><input type="number" class="form-control form-control-sm fss-height" min="0" value="${r.height_max ?? ''}" placeholder="비우면 상한 없음"></td>
                        <td><input type="number" class="form-control form-control-sm fss-thickness" min="1" value="${r.frame_thickness}"></td>
                        <td><input type="number" class="form-control form-control-sm fss-width" min="1" value="${r.frame_width}"></td>
                    </tr>`).join('')}
                </tbody>
            </table>
        </div>`;
    }).join('');
}

async function saveSteps() {
    const rows = [...document.querySelectorAll('#fssGroups tr[data-id]')].map(tr => ({
        id: tr.dataset.id,
        height_max: tr.querySelector('.fss-height').value,
        frame_thickness: tr.querySelector('.fss-thickness').value,
        frame_width: tr.querySelector('.fss-width').value,
    }));
    const res  = await fetch('/src/api/admin/frame_size_steps.php', {
        method: 'POST', headers: hdr(), body: JSON.stringify({ rows }),
    });
    const data = await res.json();
    if (!res.ok) { alert(data.error || '저장 실패'); return; }
    allSteps = data.rows || [];
    renderGroups();
    alert('저장했습니다.');
}

document.addEventListener('DOMContentLoaded', () => {
    if (token()) loadSteps();
});
window.addEventListener('pmokAuthChanged', loadSteps);
