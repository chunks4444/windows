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

    // openModal() 등에서 colorInput.value = '...'로 직접(프로그램적으로) 값을 넣으면
    // input 이벤트가 안 뜨기 때문에 옆의 헥사 텍스트 필드가 예전 값에 머물러 있게 된다 —
    // 그 상태에서 헥사코드를 고치려 하면 엉뚱한 값에서 시작하는 것처럼 보여 "잘 안 되는" 것처럼 느껴짐.
    // 그래서 매번 모달을 열 때 등 값을 다시 채운 뒤엔 반드시 pmokEnhanceColorInputs()를
    // 다시 호출해 동기화해야 한다(이미 강화된 입력은 재생성 없이 값만 맞춘다).
    function syncHexInput(colorInput) {
        const hexInput = colorInput._pmokHexInput;
        if (hexInput) hexInput.value = colorInput.value;
    }

    function enhance(colorInput) {
        if (colorInput.dataset.hexEnhanced) {
            syncHexInput(colorInput);
            return;
        }
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
        // 모양은 전부 .pmok-hex-input CSS에 맡기고 여기선 인라인 스타일을 주지 않는다.
        // 이 입력창은 밝은 어드민 페이지와 어두운 엔진 사이드바 양쪽에 붙는데,
        // 인라인 스타일은 CSS로 덮을 수 없어서 한쪽에 맞추면 다른 쪽이 반드시 깨진다.

        colorInput.insertAdjacentElement('afterend', hexInput);
        colorInput._pmokHexInput = hexInput;

        // 엔진 사이드바의 색상 칩(.color-code-box)에서는 칩 어디를 눌러도 색상 선택창이 열리게 한다.
        // 위 두 줄(울거미·살)은 칩 전체가 <button>이라 아무 데나 눌러도 팔레트가 열리는데,
        // 여기는 14px짜리 색상칩을 정확히 눌러야만 열려서 조작감이 달랐다.
        // 단, 헥사 입력칸을 누른 경우는 제외한다 — 그건 직접 타이핑하라고 둔 자리다.
        const chip = colorInput.closest('.color-code-box');
        if (chip) {
            chip.style.cursor = 'pointer';
            chip.addEventListener('click', e => {
                if (e.target === hexInput || e.target === colorInput) return;
                colorInput.click();
            });
        }

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
        hexInput.addEventListener('keydown', e => {
            if (e.key === 'Enter') { e.preventDefault(); commit(); }
        });
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
