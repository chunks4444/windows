// 페이지의 모든 <input type="color"> 옆에 헥사코드를 직접 입력할 수 있는 텍스트 필드를 붙여준다.
// 기존에 "읽기전용 헥사코드 표시" span(예: id="muntolColorCode")이 있으면 그건 숨기고
// 새 입력창으로 대체 — 각 엔진의 기존 색상 로직(draw() 트리거 등)은 그대로 두고, 새 입력창에서
// 값이 바뀌면 원래 color input에 값을 넣고 input 이벤트를 그대로 흘려보내 기존 로직이 반응하게 한다.
(function () {
    function isValidHex(v) {
        return /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(v);
    }
    function normalizeHex(v) {
        v = v.trim();
        if (v && v[0] !== '#') v = '#' + v;
        if (/^#[0-9a-fA-F]{3}$/.test(v)) {
            v = '#' + v[1] + v[1] + v[2] + v[2] + v[3] + v[3];
        }
        return v.toLowerCase();
    }

    function enhance(colorInput) {
        if (colorInput.dataset.hexEnhanced) return;
        colorInput.dataset.hexEnhanced = '1';

        const legacyId = colorInput.id ? colorInput.id.replace(/Input$/, '') + 'Code' : null;
        const legacyEl = legacyId ? document.getElementById(legacyId) : null;
        if (legacyEl) legacyEl.style.display = 'none';

        const hexInput = document.createElement('input');
        hexInput.type = 'text';
        hexInput.className = 'pmok-hex-input';
        hexInput.maxLength = 7;
        hexInput.autocomplete = 'off';
        hexInput.spellcheck = false;
        hexInput.value = colorInput.value;
        hexInput.placeholder = '#000000';
        hexInput.style.cssText = 'width:70px;font-size:11px;font-family:monospace;'
            + 'border:1px solid var(--border, #ccc);border-radius:4px;padding:2px 6px;'
            + 'margin-left:4px;background:var(--bg, #fff);color:var(--text, #222);';

        colorInput.insertAdjacentElement('afterend', hexInput);

        colorInput.addEventListener('input', () => { hexInput.value = colorInput.value; });

        function commit() {
            const v = normalizeHex(hexInput.value);
            if (!isValidHex(v)) { hexInput.value = colorInput.value; return; }
            hexInput.value = v;
            if (colorInput.value === v) return;
            colorInput.value = v;
            colorInput.dispatchEvent(new Event('input', { bubbles: true }));
            colorInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
        hexInput.addEventListener('change', commit);
        hexInput.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); commit(); } });
    }

    function enhanceAll() {
        document.querySelectorAll('input[type="color"]').forEach(enhance);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', enhanceAll);
    } else {
        enhanceAll();
    }

    window.pmokEnhanceColorInputs = enhanceAll;
})();
