(function () {
  var closePopup = function () {};

  document.addEventListener('click', function (e) {
    var cta = e.target.closest('[data-nt-contact]');
    if (!cta) return;
    var formEl = document.getElementById('contactModal');
    if (!formEl || !window.bootstrap) return;
    e.preventDefault();
    closePopup();
    var subject = document.getElementById('ctSubject');
    if (subject && !subject.value) subject.value = cta.getAttribute('data-subject') || '';
    window.bootstrap.Modal.getOrCreateInstance(formEl).show();
  });

  var modal = document.getElementById('ntModal');
  if (!modal) return;
  var card = modal.querySelector('.nt-modal-card');
  var content = document.getElementById('ntModalContent');
  var lastFocus = null;

  function isOpen() { return !modal.hidden; }

  function open(id, writeHash) {
    var tpl = document.getElementById('nt-tpl-' + id);
    if (!tpl) return false;
    content.textContent = '';
    content.appendChild(tpl.content.cloneNode(true));
    if (!isOpen()) lastFocus = document.activeElement;
    modal.hidden = false;
    modal.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.add('nt-lock');
    card.scrollTop = 0;
    card.focus();
    if (writeHash && window.history && history.replaceState) history.replaceState(null, '', '#n' + id);
    return true;
  }

  function close() {
    if (!isOpen()) return;
    modal.hidden = true;
    modal.setAttribute('aria-hidden', 'true');
    content.textContent = '';
    document.documentElement.classList.remove('nt-lock');
    if (/^#n\d+$/.test(location.hash) && window.history && history.replaceState) {
      history.replaceState(null, '', location.pathname + location.search);
    }
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }

  closePopup = close;

  document.addEventListener('click', function (e) {
    var link = e.target.closest('[data-nt-open]');
    if (link) {
      if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      if (open(link.getAttribute('data-nt-open'), true)) e.preventDefault();
      return;
    }
    if (e.target.closest('[data-nt-close]')) close();
  });

  document.addEventListener('keydown', function (e) {
    if (!isOpen()) return;
    if (e.key === 'Escape') { close(); return; }
    if (e.key !== 'Tab') return;
    var items = card.querySelectorAll('a[href], button:not([disabled])');
    if (!items.length) { e.preventDefault(); return; }
    var first = items[0];
    var last = items[items.length - 1];
    if (e.shiftKey && (document.activeElement === first || document.activeElement === card)) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
  });

  function fromHash() {
    var m = /^#n(\d+)$/.exec(location.hash);
    if (m) open(m[1], false);
    else close();
  }

  window.addEventListener('hashchange', fromHash);
  fromHash();
})();
