const API = '/src/api/admin/guide.php';
function _h() { return { 'Authorization': 'Bearer ' + localStorage.getItem('pmok_auth_token'), 'Content-Type': 'application/json' }; }

// src/guide/_head.php의 $guide_nav와 동일한 카테고리 구분 — 목록 표시용
const GUIDE_CATEGORIES = [
    { title: '평목 소개', slugs: ['intro', 'getting-started'] },
    { title: '스튜디오', slugs: ['studio-classic', 'studio-square', 'studio-cross', 'studio-diamond', 'studio-triangle', 'studio-hexagon'] },
    { title: '도면 관리', slugs: ['canvas-toolbar', 'drawing', 'export'] },
    { title: 'AI 렌더링', slugs: ['render'] },
    { title: '컬렉션', slugs: ['collection'] },
    { title: '계정 설정', slugs: ['account'] },
    { title: '주문', slugs: ['order'] },
    { title: '배송', slugs: ['delivery'] },
    { title: 'FAQ', slugs: ['faq'] },
];

let articles = [], quill, sourceMode = false;

function fileToResizedDataUrl(file, maxDim) {
    return new Promise((resolve, reject) => {
        if (!['image/jpeg', 'image/png'].includes(file.type)) { reject('PNG 또는 JPG 파일만 업로드할 수 있습니다.'); return; }
        const reader = new FileReader();
        reader.onload = e => {
            const img = new Image();
            img.onload = () => {
                let w = img.width, h = img.height;
                if (w > maxDim || h > maxDim) {
                    const scale = Math.min(maxDim / w, maxDim / h);
                    w = Math.round(w * scale); h = Math.round(h * scale);
                }
                const canvas = document.createElement('canvas');
                canvas.width = w; canvas.height = h;
                canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                resolve({ dataUrl: canvas.toDataURL('image/jpeg', 0.92) });
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}

function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

async function loadArticles() {
    const res  = await fetch(API, { headers: _h() });
    const data = await res.json();
    if (!res.ok) { document.getElementById('guideAuthWall').style.display = ''; return; }
    articles = data.articles || [];
    render();
}

function fmtDate(s) {
    if (!s) return '—';
    return String(s).slice(0, 16).replace('T', ' ');
}

function render() {
    const bySlug = {};
    articles.forEach(a => { bySlug[a.slug] = a; });
    let rows = '';
    GUIDE_CATEGORIES.forEach(cat => {
        rows += `<tr class="guide-cat-row"><td colspan="4">${esc(cat.title)}</td></tr>`;
        cat.slugs.forEach(slug => {
            const a = bySlug[slug];
            if (!a) return;
            rows += `
            <tr data-slug="${esc(slug)}">
                <td>${esc(a.title)}</td>
                <td><code>${esc(a.slug)}</code></td>
                <td>${esc(fmtDate(a.updated_at))}</td>
                <td>
                    <button class="adm-edit-btn" style="height:28px;padding:0 10px;font-size:12px;" onclick="openModal('${esc(slug)}')">수정</button>
                </td>
            </tr>`;
        });
    });
    document.getElementById('guideBody').innerHTML = rows;
}

function openModal(slug) {
    const a = articles.find(x => x.slug === slug);
    if (!a) return;
    document.getElementById('guideSlugLabel').textContent = a.slug;
    document.getElementById('guideSlug').value  = a.slug;
    document.getElementById('guideTitle').value = a.title;
    quill.root.innerHTML = a.body_html || '';
    document.getElementById('guideBodySource').value = a.body_html || '';
    setSourceMode(false);
    document.getElementById('guideModalOverlay').classList.add('open');
}

function closeModal() {
    document.getElementById('guideModalOverlay').classList.remove('open');
}

function setSourceMode(on) {
    sourceMode = on;
    const editor = document.getElementById('guideBodyEditor');
    const source = document.getElementById('guideBodySource');
    const btn    = document.getElementById('guideEditorToggleBtn');
    if (on) {
        source.value = quill.root.innerHTML;
        editor.style.display = 'none';
        source.style.display = '';
        btn.textContent = '서식 도구로 보기';
    } else {
        quill.root.innerHTML = source.value;
        editor.style.display = '';
        source.style.display = 'none';
        btn.textContent = 'HTML 소스로 보기';
    }
}

function toggleEditorMode() {
    setSourceMode(!sourceMode);
}

async function insertContentImage(file) {
    const range = quill.getSelection(true);
    try {
        const { dataUrl } = await fileToResizedDataUrl(file, 1600);
        const res  = await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify({ action: 'upload_content_image', image_data: dataUrl }) });
        const data = await res.json();
        if (!data.ok) { alert(data.error || '이미지 업로드 실패'); return; }
        quill.insertEmbed(range.index, 'image', data.url, 'user');
        quill.setSelection(range.index + 1);
    } catch (err) {
        alert(err);
    }
}

async function saveArticle() {
    const bodyHtml = sourceMode ? document.getElementById('guideBodySource').value : quill.root.innerHTML;
    const body = {
        action:    'save',
        slug:      document.getElementById('guideSlug').value,
        title:     document.getElementById('guideTitle').value.trim(),
        body_html: bodyHtml.trim(),
    };
    if (!body.title) { alert('제목을 입력해주세요.'); return; }
    const res  = await fetch(API, { method: 'POST', headers: _h(), body: JSON.stringify(body) });
    const data = await res.json();
    const st = document.getElementById('guideSaveStatus');
    if (data.ok) {
        st.className = 'pc-status ok'; st.textContent = '저장됨';
        setTimeout(() => { if (st.textContent === '저장됨') st.textContent = ''; }, 2000);
        await loadArticles();
    } else {
        st.className = 'pc-status err'; st.textContent = data.error || '저장 실패';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const contentImgFile = document.getElementById('guideContentImgFile');

    quill = new Quill('#guideBodyEditor', {
        theme: 'snow',
        modules: {
            toolbar: {
                container: [
                    [{ header: [2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['blockquote', 'link', 'image'],
                    ['clean'],
                ],
                handlers: {
                    image: () => contentImgFile.click(),
                },
            },
        },
        placeholder: '본문을 입력하세요.',
    });

    contentImgFile.addEventListener('change', () => {
        const file = contentImgFile.files[0];
        contentImgFile.value = '';
        if (file) insertContentImage(file);
    });

    document.getElementById('guideModalOverlay').addEventListener('click', e => {
        if (e.target === e.currentTarget) closeModal();
    });

    const user = JSON.parse(localStorage.getItem('pmok_auth_user') || 'null');
    if (!user || user.role !== 's') { document.getElementById('guideAuthWall').style.display = ''; return; }
    document.getElementById('guidePage').style.display = '';
    loadArticles();
});
