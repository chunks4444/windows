<?php
if (!empty($_hide_topbar_notice)) return;
$_noticeText = '';
$_noticeLink = '';
$_noticeBg   = 'dark';
try {
    $_pdo_n = db();
    $_noticeVisible = $_pdo_n->query("SELECT value FROM site_config WHERE key_name='notice_visible'")->fetchColumn();
    if ($_noticeVisible === '1') {
        $_noticeText = $_pdo_n->query("SELECT value FROM site_config WHERE key_name='notice_text'")->fetchColumn() ?: '';
        $_noticeLink = $_pdo_n->query("SELECT value FROM site_config WHERE key_name='notice_link'")->fetchColumn() ?: '';
        $_noticeBg   = $_pdo_n->query("SELECT value FROM site_config WHERE key_name='notice_bg'")->fetchColumn() ?: 'dark';
    }
} catch (Throwable $e) {}

if ($_noticeText):
    $_noticeHash = substr(md5($_noticeText), 0, 8);
    $_bgColors = ['dark'=>'var(--text)','teal'=>'var(--accent)','red'=>'var(--danger)'];
    $_bgColor  = $_bgColors[$_noticeBg] ?? 'var(--text)';
?>
<style id="pmNoticeStyle">
body { padding-top: 108px !important; }
.pm-navbar { top: 40px !important; }
</style>
<div id="pmTopbarNotice" style="background:<?= $_bgColor ?>;color:var(--bg);position:fixed;top:0;left:0;right:0;z-index:1031;height:40px;display:flex;align-items:center;justify-content:center;padding:0 48px;font-family:inherit;">
    <div style="flex:1;display:flex;align-items:center;justify-content:center;overflow:hidden;">
        <?php if ($_noticeLink): ?>
            <a href="<?= htmlspecialchars($_noticeLink) ?>" style="font-size:13px;font-weight:600;color:inherit;text-decoration:none;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                <?= htmlspecialchars($_noticeText) ?> <i class="bi bi-arrow-right"></i>
            </a>
        <?php else: ?>
            <span style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($_noticeText) ?></span>
        <?php endif; ?>
    </div>
    <button onclick="pmDismissNotice('<?= $_noticeHash ?>')" aria-label="닫기"
            style="position:absolute;right:14px;background:none;border:none;color:inherit;opacity:0.65;cursor:pointer;font-size:15px;padding:6px;line-height:1;display:flex;align-items:center;">
        <i class="bi bi-x-lg"></i>
    </button>
</div>
<script>
(function() {
    var key = 'pmok_notice_<?= $_noticeHash ?>';
    if (!sessionStorage.getItem(key)) return;
    // 이미 닫은 경우: style 블록 제거 + 토바 숨김
    var s = document.getElementById('pmNoticeStyle');
    if (s) s.remove();
    document.body.style.paddingTop = '68px';
    var el = document.getElementById('pmTopbarNotice');
    if (el) el.style.display = 'none';
})();
function pmDismissNotice(hash) {
    sessionStorage.setItem('pmok_notice_' + hash, '1');
    document.getElementById('pmTopbarNotice').style.display = 'none';
    var s = document.getElementById('pmNoticeStyle');
    if (s) s.remove();
    document.body.style.paddingTop = '68px';
    var nav = document.querySelector('.pm-navbar');
    if (nav) nav.style.top = '0';
}
</script>
<?php endif; ?>
