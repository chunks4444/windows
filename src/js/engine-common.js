    function _currentVersionSavedAt() {
        const ver = versions[currentVerIdx];
        return ver ? Math.floor(ver.savedAt / 1000) : null;
    }

    function _pmGetEl() {
        if (_pmModalEl) return _pmModalEl;
        const el = document.createElement('div');
        el.className = 'pm-modal-backdrop';
        el.innerHTML = `
            <div class="pm-modal">
                <div class="pm-modal-icon-wrap" id="pmIconWrap">
                    <svg id="pmIconSvg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></svg>
                </div>
                <p class="pm-modal-msg" id="pmMsg"></p>
                <p class="pm-modal-sub" id="pmSub"></p>
                <div class="pm-modal-btns" id="pmBtns"></div>
            </div>`;
        document.body.appendChild(el);
        el.addEventListener('click', e => { if (e.target === el) _pmHide(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') _pmHide(); });
        _pmModalEl = el;
        return el;
    }

    function _pmHide() {
        if (!_pmModalEl) return;
        _pmModalEl.classList.remove('pm-active');
    }

    function _pmShow(msg, sub, iconType, btnsHtml) {
        const el = _pmGetEl();
        const wrap = document.getElementById('pmIconWrap');
        const svg  = document.getElementById('pmIconSvg');
        document.getElementById('pmMsg').textContent = msg;
        const subEl = document.getElementById('pmSub');
        subEl.textContent = sub || '';
        subEl.style.display = sub ? '' : 'none';
        if (iconType === 'danger') {
            wrap.className = 'pm-modal-icon-wrap warn';
            svg.setAttribute('stroke', '#E03030');
            svg.innerHTML = '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>';
        } else {
            wrap.className = 'pm-modal-icon-wrap info';
            svg.setAttribute('stroke', 'var(--teal)');
            svg.innerHTML = '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>';
        }
        document.getElementById('pmBtns').innerHTML = btnsHtml;
        el.style.display = 'flex';
        requestAnimationFrame(() => el.classList.add('pm-active'));
    }

    function _wpToken()   { return localStorage.getItem('pmok_auth_token'); }
    function _wpHeaders() { return { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + _wpToken() }; }

    function _localUserId() {
        const tok = localStorage.getItem('pmok_auth_token');
        if (!tok) return null;
        try { return JSON.parse(atob(tok.split('.')[1])).sub ?? null; } catch { return null; }
    }

    function _rendersKey() {
        const uid = _localUserId();
        return uid ? RENDERS_KEY + '_u' + uid : RENDERS_KEY;
    }

    function addThumbItem(id, src, filename) {
        const item = document.createElement('div');
        item.className = 'rp-thumb-item';
        item.dataset.id = id;

        const img = document.createElement('img');
        img.src = src;
        img.alt = filename;

        const btn = document.createElement('button');
        btn.className = 'rp-remove';
        btn.title = '제거';
        btn.textContent = '✕';
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const thumb = thumbImages.find(t => t.id === id);
            if (thumb?.serverId) {
                fetch(WALLPAPER_API + 'delete.php', {
                    method: 'POST', headers: _wpHeaders(),
                    body: JSON.stringify({ id: thumb.serverId }),
                }).catch(() => {});
            }
            thumbImages = thumbImages.filter(t => t.id !== id);
            item.remove();
            if (activeThumbId === id) {
                if (thumbImages.length > 0) {
                    setActiveThumb(thumbImages[thumbImages.length - 1].id);
                } else {
                    appBackgroundImage = null;
                    activeThumbId = null;
                    localStorage.removeItem(BG_IMAGE_KEY);
                    hideRightSidebar();
                    draw();
                }
            }
        });

        item.appendChild(img);
        item.appendChild(btn);
        item.addEventListener('click', () => setActiveThumb(id));
        thumbList.appendChild(item);
    }

    function animatePanelResize() {
        const duration = 270;
        const start = performance.now();
        function step(now) {
            resizeCanvas();
            if (now - start < duration) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    function buildColorPopup(popupId, previewDotId, previewNameId, btnId, onSelect, defaultHex) {
        const popup  = document.getElementById(popupId);
        const dot    = document.getElementById(previewDotId);
        const nameEl = document.getElementById(previewNameId);
        const btn    = document.getElementById(btnId);

        function updatePreview(color) {
            dot.style.background = color.hex;
            nameEl.textContent   = color.hex;
        }

        const allColors = colorGroups.flatMap(g => g.colors);

        colorGroups.forEach(group => {
            // 그룹 레이블
            const isFirst = group === colorGroups[0];
            const lbl = document.createElement('div');
            lbl.style.cssText = `grid-column:1/-1;font-size:9px;font-weight:700;letter-spacing:0.8px;text-transform:uppercase;color:var(--text-3);padding:2px 0 1px;${isFirst ? '' : 'border-top:1px solid var(--border);margin-top:4px;'}`;
            lbl.textContent = group.label;
            popup.appendChild(lbl);

            group.colors.forEach(color => {
                const sw = document.createElement('div');
                sw.className = 'color-swatch' + (color.hex === defaultHex ? ' selected' : '');
                sw.style.background = color.hex;
                sw.title = color.name;
                sw.addEventListener('click', e => {
                    e.stopPropagation();
                    popup.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
                    sw.classList.add('selected');
                    updatePreview(color);
                    onSelect(color.hex);
                    popup.classList.remove('open');
                    draw();
                });
                popup.appendChild(sw);
            });
        });

        const def = allColors.find(c => c.hex === defaultHex) || allColors[0];
        updatePreview(def);

        btn.addEventListener('click', e => {
            e.stopPropagation();
            document.querySelectorAll('.color-popup').forEach(p => {
                if (p !== popup) p.classList.remove('open');
            });
            popup.classList.toggle('open');
        });

        function selectColor(hex) {
            const color = allColors.find(c => c.hex === hex) || allColors[0];
            popup.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
            const swatches = popup.querySelectorAll('.color-swatch');
            const idx = allColors.indexOf(color);
            if (swatches[idx]) swatches[idx].classList.add('selected');
            updatePreview(color);
            onSelect(color.hex);
        }

        return { selectColor };
    }

    function buildFaceColorUI(onClear) {
        const clearBtn = document.getElementById('btnFaceClear');
        const inp      = document.getElementById('faceColorInput');
        const codeEl   = document.getElementById('faceColorCode');

        function syncCode() {
            if (codeEl && inp) codeEl.textContent = inp.value;
        }

        if (inp) inp.addEventListener('input', syncCode);

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                if (clearBtn) clearBtn.style.display = 'none';
                onClear();
            });
        }

        function getCurrentHex() {
            return inp ? inp.value : '#c8102e';
        }

        function updateClearBtn(hasColors) {
            if (clearBtn) clearBtn.style.display = hasColors ? '' : 'none';
        }

        function restoreColor(hex) {
            if (inp && hex) { inp.value = hex; syncCode(); }
        }

        return { getCurrentHex, updateClearBtn, restoreColor };
    }

    function closeDrawingManager() {
        document.getElementById('dmBackdrop').classList.remove('pm-active');
    }

    function compressImage(src, callback) {
        const img = new Image();
        img.onload = function() {
            const MAX = 1600;
            let w = img.width, h = img.height;
            if (w > MAX || h > MAX) {
                const s = Math.min(MAX / w, MAX / h);
                w = Math.round(w * s);
                h = Math.round(h * s);
            }
            const c = document.createElement('canvas');
            c.width = w; c.height = h;
            c.getContext('2d').drawImage(img, 0, 0, w, h);
            callback(c.toDataURL('image/jpeg', 0.82));
        };
        img.src = src;
    }

    function deactivateAllModes() {
        panMode = false;
        document.getElementById('btnPan').classList.remove('cv-btn-active');
        lineEditMode = null;
        addLineStart = null;
        document.getElementById('btnEditDelete').classList.remove('cv-btn-active');
        document.getElementById('btnEditAdd').classList.remove('cv-btn-active');
        placementMode = false;
        document.getElementById('btnScale').classList.remove('cv-btn-active');
        canvas.style.cursor = 'default';
    }

    function drawTriangleAffine(tctx, img,
        sx0, sy0, sx1, sy1, sx2, sy2,
        dx0, dy0, dx1, dy1, dx2, dy2
    ) {
        const det = sx0*(sy1-sy2) + sx1*(sy2-sy0) + sx2*(sy0-sy1);
        if (Math.abs(det) < 0.001) return;
        const a  = (dx0*(sy1-sy2) + dx1*(sy2-sy0) + dx2*(sy0-sy1)) / det;
        const b  = (dy0*(sy1-sy2) + dy1*(sy2-sy0) + dy2*(sy0-sy1)) / det;
        const c  = (sx0*(dx1-dx2) + sx1*(dx2-dx0) + sx2*(dx0-dx1)) / det;
        const d  = (sx0*(dy1-dy2) + sx1*(dy2-dy0) + sx2*(dy0-dy1)) / det;
        const e  = dx0 - a*sx0 - c*sy0;
        const f  = dy0 - b*sx0 - d*sy0;
        tctx.save();
        tctx.beginPath();
        tctx.moveTo(dx0, dy0); tctx.lineTo(dx1, dy1); tctx.lineTo(dx2, dy2);
        tctx.closePath(); tctx.clip();
        tctx.transform(a, b, c, d, e, f);
        tctx.drawImage(img, 0, 0);
        tctx.restore();
    }

    function fmtDate(ts) {
        const d = new Date(ts);
        const yy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        const hh = String(d.getHours()).padStart(2, '0');
        const mi = String(d.getMinutes()).padStart(2, '0');
        return `${yy}.${mm}.${dd} ${hh}:${mi}`;
    }

    function getOverlayCorners() {
        if (!doorCornerPositions) return null;
        const { tl, tr, br, bl } = doorCornerPositions;
        const center = {
            cx: (tl.cx + tr.cx + br.cx + bl.cx) / 4,
            cy: (tl.cy + tr.cy + br.cy + bl.cy) / 4,
        };
        const ox = logW/2 + panX, oy = logH/2 + panY;
        const ts = p => ({ x: ox + p.cx * scaleFactor, y: oy + p.cy * scaleFactor });
        return { tl: ts(tl), tr: ts(tr), br: ts(br), bl: ts(bl), center: ts(center) };
    }

    function hideRightSidebar() {
        rightSidebar.classList.add('collapsed');
        btnRightSidebarTab.classList.add('collapsed');
        animatePanelResize();
    }

    function isMouseNearOverlay(clientX, clientY) {
        if (!placementMode || !doorCornerPositions) return false;
        const rect = canvas.getBoundingClientRect();
        const mx = (clientX - rect.left) * (logW / rect.width);
        const my = (clientY - rect.top)  * (logH / rect.height);
        const c = getOverlayCorners();
        const pad = 24;
        const minX = Math.min(c.tl.x, c.tr.x, c.br.x, c.bl.x) - pad;
        const maxX = Math.max(c.tl.x, c.tr.x, c.br.x, c.bl.x) + pad;
        const minY = Math.min(c.tl.y, c.tr.y, c.br.y, c.bl.y) - pad;
        const maxY = Math.max(c.tl.y, c.tr.y, c.br.y, c.bl.y) + pad;
        return mx >= minX && mx <= maxX && my >= minY && my <= maxY;
    }

    function lightenHex(hex, amount) {
        const r = Math.min(255, parseInt(hex.slice(1, 3), 16) + amount);
        const g = Math.min(255, parseInt(hex.slice(3, 5), 16) + amount);
        const b = Math.min(255, parseInt(hex.slice(5, 7), 16) + amount);
        return `rgb(${r},${g},${b})`;
    }

    function loadSavedRenders() {
        try { savedRenders = JSON.parse(localStorage.getItem(_rendersKey())) || []; } catch(e) { savedRenders = []; }
        renderSavedThumbList();
    }

    function openDrawingManager() {
        document.getElementById('dmBackdrop').classList.add('pm-active');
        refreshDrawingList();
    }

    function pmAlert(msg, { sub = '', type = 'info' } = {}) {
        _pmShow(msg, sub, type, `<button class="pm-btn-ok" id="pmOk">확인</button>`);
        document.getElementById('pmOk').onclick = _pmHide;
    }

    function pmConfirm(msg, onConfirm, { sub = '', type = 'danger', confirmText = '삭제' } = {}) {
        _pmShow(msg, sub, type, `
            <button class="pm-btn-cancel" id="pmCancel">취소</button>
            <button class="pm-btn-${type}" id="pmConfirmBtn">${confirmText}</button>`);
        document.getElementById('pmCancel').onclick = _pmHide;
        document.getElementById('pmConfirmBtn').onclick = () => { _pmHide(); onConfirm(); };
    }

    // ── 주문 모달 ────────────────────────────────────
    function _orderHide() {
        document.getElementById('orderBackdrop')?.classList.remove('pm-active');
    }

    function openOrderModal({ engine, getSpec, getDrawingId, getTitle }) {
        const token = localStorage.getItem('pmok_auth_token');
        if (!token) {
            const el = document.getElementById('authModal');
            if (el && window.bootstrap) bootstrap.Modal.getOrCreateInstance(el).show();
            return;
        }
        const headers = { Authorization: 'Bearer ' + token };
        Promise.all([
            fetch('/src/api/auth/profile.php', { headers }).then(r => r.json()),
            fetch('/src/api/auth/company.php', { headers }).then(r => r.json()),
        ]).then(([profileRes, companyRes]) => {
            const user    = profileRes.user    || {};
            const company = companyRes.company || {};

            if (!user.name || !user.phone) {
                pmConfirm(
                    '주문하려면 프로필에 이름과 연락처를 먼저 입력해주세요.',
                    () => { location.href = '/src/mypage/profile.php'; },
                    { sub: '프로필 페이지에서 입력 후 다시 시도해주세요.', type: 'ok', confirmText: '프로필로 이동' }
                );
                return;
            }

            document.getElementById('orderCustName').textContent  = user.name;
            document.getElementById('orderCustPhone').textContent = user.phone;
            const companyRow = document.getElementById('orderCompanyRow');
            if (company.company_name) {
                document.getElementById('orderCustCompany').textContent = company.company_name;
                companyRow.style.display = '';
            } else {
                companyRow.style.display = 'none';
            }
            document.getElementById('orderDrawingTitle').textContent = getTitle() || '(제목 없음)';
            document.getElementById('orderMemo').value = '';

            document.getElementById('orderBackdrop').classList.add('pm-active');

            document.getElementById('orderSubmitBtn').onclick = () => {
                const memo = document.getElementById('orderMemo').value.trim();
                fetch('/src/api/orders/create.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + token },
                    body: JSON.stringify({
                        engine,
                        drawing_id: getDrawingId() || null,
                        title:      getTitle() || '',
                        memo,
                        spec:       getSpec(),
                    }),
                }).then(r => r.json()).then(data => {
                    if (data.error) { pmAlert(data.error, { type: 'danger' }); return; }
                    _orderHide();
                    pmAlert('주문이 접수되었습니다.', { sub: '담당자가 확인 후 연락드립니다.' });
                }).catch(() => pmAlert('주문 접수에 실패했습니다.', { type: 'danger' }));
            };
        }).catch(() => pmAlert('프로필 정보를 불러오지 못했습니다.', { type: 'danger' }));
    }

    document.getElementById('orderCancelBtn')?.addEventListener('click', _orderHide);
    document.getElementById('orderBackdrop')?.addEventListener('click', (e) => {
        if (e.target.id === 'orderBackdrop') _orderHide();
    });

    function renderSavedThumbList() {
        const list = document.getElementById('renderSavedList');
        if (!list) return;
        list.innerHTML = '';
        [...savedRenders].reverse().forEach((r, i) => {
            const realIdx = savedRenders.length - 1 - i;
            const item = document.createElement('div');
            item.className = 'render-saved-item';
            item.innerHTML = `<img src="${r.src}"><span class="render-saved-dl" title="다운로드"><i class="bi bi-download"></i></span><span class="render-saved-del" title="삭제"><i class="bi bi-x"></i></span>`;
            item.querySelector('img').addEventListener('click', () => {
                showRenderResult(r.src);
            });
            item.querySelector('.render-saved-dl').addEventListener('click', (e) => {
                e.stopPropagation();
                const link = document.createElement('a');
                link.download = getExportFilename('png').replace(/\.png$/, '_render.png');
                link.href = r.src;
                link.click();
            });
            item.querySelector('.render-saved-del').addEventListener('click', (e) => {
                e.stopPropagation();
                savedRenders.splice(realIdx, 1);
                try { localStorage.setItem(_rendersKey(), JSON.stringify(savedRenders)); } catch(e2) {}
                renderSavedThumbList();
            });
            list.appendChild(item);
        });
    }

    function resizeCanvasDebounced() {
        cancelAnimationFrame(_resizeTimer);
        _resizeTimer = requestAnimationFrame(resizeCanvas);
    }

    function saveRender(src) {
        savedRenders.push({ src, savedAt: Date.now() });
        if (savedRenders.length > MAX_RENDERS) savedRenders.shift();
        let ok = false;
        while (!ok && savedRenders.length > 0) {
            try { localStorage.setItem(_rendersKey(), JSON.stringify(savedRenders)); ok = true; }
            catch(e) { savedRenders.shift(); }
        }
        renderSavedThumbList();
    }

    function setEditMode(mode) {
        const willActivate = lineEditMode !== mode;
        deactivateAllModes();
        if (willActivate) {
            lineEditMode = mode;
            document.getElementById(mode === 'delete' ? 'btnEditDelete' : 'btnEditAdd').classList.add('cv-btn-active');
            canvas.style.cursor = mode === 'delete' ? CURSOR_ERASER : CURSOR_PEN;
            if (mode === 'add') draw();
        }
    }

    function setElText(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    function setPanMode() {
        const willActivate = !panMode;
        deactivateAllModes();
        if (willActivate) {
            panMode = true;
            document.getElementById('btnPan').classList.add('cv-btn-active');
            canvas.style.cursor = 'grab';
        }
        draw();
    }

    function setSlider(rangeId, numId, val) {
        document.getElementById(rangeId).value = val;
        document.getElementById(numId).value   = val;
    }

    function showRightSidebar() {
        rightSidebar.classList.remove('collapsed');
        btnRightSidebarTab.classList.remove('collapsed');
        animatePanelResize();
    }

    function toggleSidebar() {
        sidebar.classList.toggle('collapsed');
        btnSidebarTab.classList.toggle('collapsed');
        animatePanelResize();
    }

    function updateDoorCountOptions() {
        const isSwing = txtDoorType.value === 'swing';
        Array.from(txtDoorCount.options).forEach(opt => {
            const v = parseInt(opt.value);
            opt.hidden = isSwing && v > 2;
        });
        if (isSwing && parseInt(txtDoorCount.value) > 2) {
            txtDoorCount.value = '2';
        }
        if (_versionsLoaded) draw();
    }

    function updateModified() {
        const now = Date.now();
        localStorage.setItem(MODIFIED_KEY, now);
        setElText('dateModified', fmtDate(now));
    }

    function updateResetPlacementBtn() {
        btnResetPlacement.style.display = doorCornerPositions ? '' : 'none';
    }

    function getExportFilename(ext) {
        const name = (document.getElementById('drawingName')?.value || '').trim() || '창호도면';
        const ver  = (document.getElementById('verLabel')?.textContent || '').trim();
        const suffix = ver && ver !== '—' ? `_${ver}` : '';
        const safe = (name + suffix).replace(/[\\/:*?"<>|]/g, '_');
        return `${safe}.${ext}`;
    }


// 커스텀 셀렉트
(function () {
    const CHEVRON = `<svg width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="rgba(255,255,255,0.45)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 1l4 4 4-4"/></svg>`;
    const proto   = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value');

    function initOne(sel) {
        if (sel._csInited) return;
        sel._csInited = true;

        const wrap     = document.createElement('div');
        wrap.className = 'cs-wrap';

        const trigger  = document.createElement('div');
        trigger.className = 'cs-trigger';
        const label    = document.createElement('span');
        trigger.appendChild(label);
        trigger.insertAdjacentHTML('beforeend', CHEVRON);

        const dropdown = document.createElement('div');
        dropdown.className = 'cs-dropdown';

        function buildOptions() {
            dropdown.innerHTML = '';
            Array.from(sel.options).forEach(opt => {
                const item = document.createElement('div');
                item.className = 'cs-option' + (opt.value === proto.get.call(sel) ? ' active' : '');
                item.textContent = opt.text;
                item.dataset.value = opt.value;
                item.addEventListener('click', e => {
                    e.stopPropagation();
                    proto.set.call(sel, opt.value);
                    sel.dispatchEvent(new Event('change', { bubbles: true }));
                    sel.dispatchEvent(new Event('input',  { bubbles: true }));
                    syncDisplay();
                    wrap.classList.remove('open');
                });
                dropdown.appendChild(item);
            });
        }

        function syncDisplay() {
            const cur = sel.options[sel.selectedIndex];
            label.textContent = cur ? cur.text : '';
            dropdown.querySelectorAll('.cs-option').forEach(item => {
                item.classList.toggle('active', item.dataset.value === proto.get.call(sel));
            });
        }

        // 프로그래밍 방식 .value 변경 감지
        Object.defineProperty(sel, 'value', {
            get: () => proto.get.call(sel),
            set: (val) => { proto.set.call(sel, val); syncDisplay(); },
            configurable: true,
        });

        trigger.addEventListener('click', e => {
            e.stopPropagation();
            const isOpen = wrap.classList.contains('open');
            document.querySelectorAll('.cs-wrap.open').forEach(w => w.classList.remove('open'));
            if (!isOpen) { buildOptions(); syncDisplay(); wrap.classList.add('open'); }
        });

        wrap.appendChild(trigger);
        wrap.appendChild(dropdown);
        sel.parentNode.insertBefore(wrap, sel);

        buildOptions();
        syncDisplay();
    }

    function initCustomSelects() {
        document.querySelectorAll('select.sb-select').forEach(initOne);
    }

    document.addEventListener('DOMContentLoaded', initCustomSelects);
    document.addEventListener('click', () => {
        document.querySelectorAll('.cs-wrap.open').forEach(w => w.classList.remove('open'));
    });
})();

// 관리자 전용 섹션 표시
(function () {
    function jwtRole(tok) {
        try { return JSON.parse(atob(tok.split('.')[1])).role; } catch(e) { return null; }
    }
    function showAdminSections() {
        const tok  = localStorage.getItem('pmok_auth_token');
        const role = tok ? jwtRole(tok) : null;
        const show = role === 's' || role === 'm';
        document.querySelectorAll('.admin-only').forEach(el => {
            el.style.display = show ? '' : 'none';
        });
    }
    document.addEventListener('DOMContentLoaded', showAdminSections);
    window.addEventListener('pmokAuthChanged', showAdminSections);
})();
