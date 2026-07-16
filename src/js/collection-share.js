let _libShareCtx = null;

// 컬렉션 카드/상세페이지의 "열기" 버튼: 비로그인 상태면 페이지 이동 없이 그 자리에서
// 로그인 모달만 띄우고, 로그인 성공 시 에디터로 이동한다. (외부에서 도면 링크를 직접
// 열람하는 "공유 미리보기" 페이지와는 별개 — 그쪽은 그대로 유지)
function openCollectionEditor(e, url) {
    e.preventDefault();
    pmokRequireAuth(() => { location.href = url; });
    return false;
}

function openCollectionShareModal(url, title, image) {
    _libShareCtx = { url, title: title || '평목 컬렉션', image };
    const linkInput = document.getElementById('libShareModalLink');
    if (linkInput) linkInput.value = url;
    const modal = document.getElementById('libShareModal');
    if (modal) modal.style.display = 'flex';
}

function closeCollectionShareModal() {
    const modal = document.getElementById('libShareModal');
    if (modal) modal.style.display = 'none';
}

async function _libShareCopyLink() {
    if (!_libShareCtx) return;
    const btn = document.getElementById('libShareModalCopy');
    try {
        await navigator.clipboard.writeText(_libShareCtx.url);
        if (btn) {
            const orig = btn.textContent;
            btn.textContent = '복사됨';
            setTimeout(() => { btn.textContent = orig; }, 1500);
        }
    } catch (e) {
        window.prompt('아래 링크를 복사하세요:', _libShareCtx.url);
    }
}

function _libShareToKakao() {
    if (!_libShareCtx) return;
    if (!window.Kakao?.isInitialized?.()) { _libShareCopyLink(); return; }
    Kakao.Share.sendDefault({
        objectType: 'feed',
        content: {
            title: _libShareCtx.title,
            description: '평목 스튜디오에서 만든 문살 패턴을 확인해보세요.',
            imageUrl: _libShareCtx.image || (location.origin + '/src/assets/logo.png'),
            link: { mobileWebUrl: _libShareCtx.url, webUrl: _libShareCtx.url },
        },
    });
}

function _libShareToFb() {
    if (!_libShareCtx) return;
    window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(_libShareCtx.url), '_blank', 'noopener,width=600,height=500');
}

function _libShareToX() {
    if (!_libShareCtx) return;
    window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(_libShareCtx.url) + '&text=' + encodeURIComponent(_libShareCtx.title), '_blank', 'noopener,width=600,height=500');
}

function _libShareToThreads() {
    if (!_libShareCtx) return;
    window.open('https://www.threads.net/intent/post?text=' + encodeURIComponent(_libShareCtx.title + ' ' + _libShareCtx.url), '_blank', 'noopener,width=600,height=500');
}

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('libShareModal');
    if (!modal) return;
    modal.addEventListener('click', e => { if (e.target === e.currentTarget) closeCollectionShareModal(); });
    document.getElementById('libShareModalClose')?.addEventListener('click', closeCollectionShareModal);
    document.getElementById('libShareModalCopy')?.addEventListener('click', _libShareCopyLink);
    document.getElementById('libShareModalKakao')?.addEventListener('click', _libShareToKakao);
    document.getElementById('libShareModalFb')?.addEventListener('click', _libShareToFb);
    document.getElementById('libShareModalX')?.addEventListener('click', _libShareToX);
    document.getElementById('libShareModalThreads')?.addEventListener('click', _libShareToThreads);
});
