    function _currentVersionSavedAt() {
        const ver = versions[currentVerIdx];
        return ver ? Math.floor(ver.savedAt / 1000) : null;
    }

    // 견적요청 중인 도면을 열었을 때 표시되는 잠금 배지 (뷰만 가능, 편집/저장/삭제는 서버에서도 차단됨)
    function updateLockBanner(isLocked) {
        const group = document.querySelector('.title-btn-group');
        if (!group) return;
        let badge = document.getElementById('lockBanner');
        if (isLocked) {
            if (!badge) {
                badge = document.createElement('span');
                badge.id = 'lockBanner';
                badge.className = 'lock-banner';
                badge.innerHTML = '<i class="bi bi-lock-fill"></i> 견적요청 중 · 편집 불가';
                group.insertBefore(badge, group.firstChild);
            }
        } else if (badge) {
            badge.remove();
        }
        const nameInput = document.getElementById('drawingName');
        if (nameInput) nameInput.readOnly = !!isLocked;
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
        window.__pmokOnDeactivate?.();
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

    // ── 견적요청 모달 ────────────────────────────────────
    function _orderHide() {
        document.getElementById('orderBackdrop')?.classList.remove('pm-active');
    }

    function _orderOpenPostcode() {
        new daum.Postcode({
            oncomplete(data) {
                document.getElementById('orderShipZipcode').value       = data.zonecode;
                document.getElementById('orderShipAddress').value       = data.roadAddress || data.autoRoadAddress || data.jibunAddress;
                document.getElementById('orderShipAddressDetail').value = '';
                document.getElementById('orderShipAddressDetail').focus();
            }
        }).open();
    }

    function openOrderModal(opts) {
        const token = localStorage.getItem('pmok_auth_token');
        if (!token) {
            pmokRequireAuth(() => openOrderModal(opts));
            return;
        }
        const { engine, getSpec, getDrawingId, getTitle, getThumbnail, getVersionLabel, onLocked } = opts;
        const headers = { Authorization: 'Bearer ' + token };
        Promise.all([
            fetch('/src/api/auth/profile.php', { headers }).then(r => r.json()),
            fetch('/src/api/auth/company.php', { headers }).then(r => r.json()),
        ]).then(([profileRes, companyRes]) => {
            const user    = profileRes.user    || {};
            const company = companyRes.company || {};

            if (!user.name || !user.phone) {
                pmConfirm(
                    '견적요청하려면 프로필에 이름과 연락처를 먼저 입력해주세요.',
                    () => { location.href = '/src/mypage/profile.php'; },
                    { sub: '프로필 페이지에서 입력 후 다시 시도해주세요.', type: 'ok', confirmText: '프로필로 이동' }
                );
                return;
            }

            document.getElementById('orderDate').textContent      = new Date().toISOString().slice(0, 10);
            document.getElementById('orderCustName').textContent  = user.name;
            document.getElementById('orderCustPhone').textContent = user.phone;
            const companyRow = document.getElementById('orderCompanyRow');
            if (company.company_name) {
                document.getElementById('orderCustCompany').textContent = company.company_name;
                companyRow.style.display = '';
            } else {
                companyRow.style.display = 'none';
            }
            document.getElementById('orderDrawingTitle').textContent   = getTitle() || '(제목 없음)';
            document.getElementById('orderDrawingVersion').textContent = (getVersionLabel && getVersionLabel()) || '—';

            const dueDateEl = document.getElementById('orderDueDate');
            const minDays   = parseInt(document.querySelector('.sb-lead-time')?.dataset?.minDays ?? '0', 10);
            const minDate   = new Date();
            let bizAdded = 0;
            while (bizAdded < minDays + 1) {
                minDate.setDate(minDate.getDate() + 1);
                const dow = minDate.getDay();
                if (dow !== 0 && dow !== 6) bizAdded++;
            }
            dueDateEl.min   = `${minDate.getFullYear()}-${String(minDate.getMonth()+1).padStart(2,'0')}-${String(minDate.getDate()).padStart(2,'0')}`;
            dueDateEl.value = '';

            document.getElementById('orderShipZipcode').value       = user.zipcode        || '';
            document.getElementById('orderShipAddress').value       = user.address        || '';
            document.getElementById('orderShipAddressDetail').value = user.address_detail || '';
            document.getElementById('orderShipPhone').value         = user.phone          || '';
            document.getElementById('orderMemo').value = '';

            document.getElementById('orderZipSearchBtn').onclick = _orderOpenPostcode;

            document.getElementById('orderBackdrop').classList.add('pm-active');

            const thumbImg = document.getElementById('orderThumbImg');
            try {
                const thumbSrc = getThumbnail ? getThumbnail() : null;
                if (thumbSrc) {
                    thumbImg.src = thumbSrc;
                    thumbImg.style.display = 'block';
                } else {
                    thumbImg.style.display = 'none';
                }
            } catch (_) {
                thumbImg.style.display = 'none';
            }

            document.getElementById('orderSubmitBtn').onclick = () => {
                const dueDate   = dueDateEl.value;
                const shipZip   = document.getElementById('orderShipZipcode').value.trim();
                const shipAddr  = document.getElementById('orderShipAddress').value.trim();
                const shipAddr2 = document.getElementById('orderShipAddressDetail').value.trim();
                const shipPhone = document.getElementById('orderShipPhone').value.trim();
                const memo      = document.getElementById('orderMemo').value.trim();

                if (!dueDate)        { pmAlert('납기 희망일을 선택해주세요.',     { type: 'danger' }); return; }
                if (!shipAddr)       { pmAlert('배송지 주소를 입력해주세요.',     { type: 'danger' }); return; }
                if (!shipPhone)      { pmAlert('배송지 연락처를 입력해주세요.',   { type: 'danger' }); return; }

                fetch('/src/api/orders/create.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + token },
                    body: JSON.stringify({
                        engine,
                        drawing_id:          getDrawingId() || null,
                        title:               getTitle() || '',
                        version_label:       (getVersionLabel && getVersionLabel()) || '',
                        thumbnail:           thumbSrc || null,
                        due_date:            dueDate,
                        ship_zipcode:        shipZip,
                        ship_address:        shipAddr,
                        ship_address_detail: shipAddr2,
                        ship_phone:          shipPhone,
                        memo,
                        spec:                getSpec(),
                    }),
                }).then(r => r.json()).then(data => {
                    if (data.error) { pmAlert(data.error, { type: 'danger' }); return; }
                    _orderHide();
                    if (getDrawingId()) onLocked && onLocked();
                    pmAlert('견적요청이 접수되었습니다.', { sub: '담당자가 확인 후 연락드립니다.' });
                }).catch(() => pmAlert('견적요청 접수에 실패했습니다.', { type: 'danger' }));
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

// 캔버스 터치 지원 (아이패드 등): 한 손가락 팬, 두 손가락 핀치줌, 탭으로 세그먼트 토글
(function () {
    const TAP_THRESHOLD = 8; // px

    let touchMode  = null;  // 'pan' | 'pinch' | null
    let startCX, startCY, startPanX, startPanY, moved;
    let startDist, startScale;
    let ignoreGesture = false; // 캔버스 컨트롤 버튼 위에서 시작한 터치는 무시

    function clientToLogical(clientX, clientY) {
        const rect = canvas.getBoundingClientRect();
        return {
            x: (clientX - rect.left) * (logW / rect.width),
            y: (clientY - rect.top)  * (logH / rect.height),
        };
    }

    function onTouchStart(e) {
        ignoreGesture = !!(e.target.closest('.canvas-controls') || e.target.closest('.canvas-title-bar'));
        if (ignoreGesture) { touchMode = null; return; }
        e.preventDefault();
        if (e.touches.length === 1) {
            const t = e.touches[0];
            touchMode = 'pan';
            startCX = t.clientX; startCY = t.clientY;
            startPanX = panX; startPanY = panY;
            moved = false;
        } else if (e.touches.length === 2) {
            const [t0, t1] = e.touches;
            touchMode  = 'pinch';
            startDist  = Math.hypot(t1.clientX - t0.clientX, t1.clientY - t0.clientY);
            startScale = scaleFactor;
            moved = true;
        }
    }

    function onTouchMove(e) {
        if (ignoreGesture) return;
        e.preventDefault();
        if (touchMode === 'pinch' && e.touches.length === 2) {
            const [t0, t1] = e.touches;
            const dist = Math.hypot(t1.clientX - t0.clientX, t1.clientY - t0.clientY);
            const midClientX = (t0.clientX + t1.clientX) / 2;
            const midClientY = (t0.clientY + t1.clientY) / 2;
            const mid = clientToLogical(midClientX, midClientY);

            const prevScale = scaleFactor;
            let newScale = startScale * (dist / startDist);
            newScale = Math.max(0.3, Math.min(newScale, 20));

            const worldX = (mid.x - logW / 2 - panX) / prevScale;
            const worldY = (mid.y - logH / 2 - panY) / prevScale;

            scaleFactor = newScale;
            panX = mid.x - logW / 2 - worldX * scaleFactor;
            panY = mid.y - logH / 2 - worldY * scaleFactor;
            draw();
        } else if (touchMode === 'pan' && e.touches.length === 1) {
            const t  = e.touches[0];
            const dx = t.clientX - startCX;
            const dy = t.clientY - startCY;
            if (!moved && Math.hypot(dx, dy) > TAP_THRESHOLD) moved = true;
            if (!lineEditMode) {
                panX = startPanX + dx;
                panY = startPanY + dy;
                draw();
            }
        }
    }

    function onTouchEnd(e) {
        if (ignoreGesture) {
            if (e.touches.length === 0) ignoreGesture = false;
            return;
        }
        if (touchMode === 'pan' && !moved && lineEditMode && e.changedTouches.length === 1) {
            const t = e.changedTouches[0];
            handleEditClick({ clientX: t.clientX, clientY: t.clientY });
        }
        if (e.touches.length === 0) touchMode = null;
        else if (e.touches.length === 1) {
            const t = e.touches[0];
            touchMode = 'pan';
            startCX = t.clientX; startCY = t.clientY;
            startPanX = panX; startPanY = panY;
            moved = true;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (!container) return;
        container.addEventListener('touchstart', onTouchStart, { passive: false });
        container.addEventListener('touchmove',  onTouchMove,  { passive: false });
        container.addEventListener('touchend',   onTouchEnd,   { passive: false });
        container.addEventListener('touchcancel', onTouchEnd,  { passive: false });
    });
})();

// 모바일 화면에서는 좌/우 사이드바를 기본적으로 접어둠
(function () {
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.matchMedia('(max-width: 1200px)').matches) return;
        if (sidebar && !sidebar.classList.contains('collapsed')) {
            sidebar.classList.add('collapsed');
            if (btnSidebarTab) btnSidebarTab.classList.add('collapsed');
        }
        if (rightSidebar && !rightSidebar.classList.contains('collapsed')) {
            rightSidebar.classList.add('collapsed');
            if (btnRightSidebarTab) btnRightSidebarTab.classList.add('collapsed');
        }
        resizeCanvasDebounced();
    });
})();

// 회전(가로↔세로) 직후 레이아웃이 아직 안 정착된 상태에서 캔버스가 다시 그려지는 문제 보정
(function () {
    function delayedResize() {
        resizeCanvasDebounced();
        setTimeout(resizeCanvasDebounced, 120);
        setTimeout(resizeCanvasDebounced, 350);
    }
    window.addEventListener('orientationchange', delayedResize);
    if (window.screen && window.screen.orientation) {
        window.screen.orientation.addEventListener('change', delayedResize);
    }
})();

// ── SVG 문양 삽입 (꽃살 등 장식 문양) — 6개 엔진 공용 ──────
// baseScale: 삽입 시 자동으로 맞춘 기준 크기(로직단위/원본px), scaleMul: 슬라이더(1.0 = 100% = 처음 삽입된 크기)
let svgInserts        = []; // [{ id, url, cx, cy, baseScale, scaleMul, rotation, w, h }] cx/cy는 로직 좌표
let selectedInsertIds = new Set();
const _motifImgCache = new Map();

function _loadMotifImg(url) {
    if (_motifImgCache.has(url)) return _motifImgCache.get(url);
    const img = new Image();
    img.onload = () => draw();
    img.src = url;
    _motifImgCache.set(url, img);
    return img;
}

// 이전 형식(절대 scale만 있던 도면) 호환: scaleMul 없으면 현재 scale을 baseScale로 고정
function _normalizeInsert(ins) {
    if (ins.scaleMul === undefined) {
        ins.baseScale = ins.scale ?? ins.baseScale ?? 1;
        ins.scaleMul  = 1;
    }
    return ins;
}

function addSvgInsert(url, naturalW, naturalH) {
    // Konva 모드에서는 Konva.Image로 대체
    if (typeof window.__pmokAddKonvaInsert === 'function') {
        window.__pmokAddKonvaInsert(url, naturalW, naturalH);
        return;
    }
    const w = naturalW || 100, h = naturalH || 100;
    const targetSize = Math.min(logW, logH) * 0.25;
    const baseScale = targetSize / Math.max(w, h);
    const id = 'ins_' + Date.now() + '_' + Math.random().toString(36).slice(2, 7);
    svgInserts.push({ id, url, cx: 0, cy: 0, baseScale, scaleMul: 1, rotation: 0, w, h });
    selectedInsertIds = new Set([id]);
    _loadMotifImg(url);
    renderSvgInsertPanel();
    draw();
}

function duplicateSelectedInsert() {
    if (!selectedInsertIds.size) return;
    const offset = Math.min(logW, logH) * 0.04;
    const newIds = new Set();
    svgInserts.filter(i => selectedInsertIds.has(i.id)).forEach(ins => {
        const id = 'ins_' + Date.now() + '_' + Math.random().toString(36).slice(2, 7);
        svgInserts.push({ ...ins, id, cx: ins.cx + offset, cy: ins.cy + offset });
        newIds.add(id);
    });
    selectedInsertIds = newIds;
    renderSvgInsertPanel();
    draw();
}

function selectSvgInsert(id) {
    selectedInsertIds = id ? new Set([id]) : new Set();
    renderSvgInsertPanel();
    draw();
}

function toggleSelectInsert(id) {
    if (selectedInsertIds.has(id)) selectedInsertIds.delete(id);
    else selectedInsertIds.add(id);
    renderSvgInsertPanel();
    draw();
}

function removeSelectedInsert() {
    if (!selectedInsertIds.size) return;
    svgInserts = svgInserts.filter(i => !selectedInsertIds.has(i.id));
    selectedInsertIds.clear();
    renderSvgInsertPanel();
    draw();
}

function updateSelectedInsert(patch) {
    if (selectedInsertIds.size !== 1) return;
    const ins = svgInserts.find(i => i.id === [...selectedInsertIds][0]);
    if (!ins) return;
    Object.assign(ins, patch);
    draw();
}

function renderSvgInsertPanel() {
    const panel = document.getElementById('svgInsertControls');
    if (!panel) return;
    const count = selectedInsertIds.size;
    panel.style.display = count ? '' : 'none';
    if (!count) return;

    const primaryId  = count === 1 ? [...selectedInsertIds][0] : null;
    const ins        = primaryId ? svgInserts.find(i => i.id === primaryId) : null;
    const scaleEl    = document.getElementById('svgInsertScale');
    const rotEl      = document.getElementById('svgInsertRotation');
    const scaleRow   = scaleEl?.closest('.svg-insert-row');
    const rotRow     = rotEl?.closest('.svg-insert-row');

    if (ins) {
        _normalizeInsert(ins);
        if (scaleEl) scaleEl.value = Math.round(ins.scaleMul * 100);
        if (rotEl)   rotEl.value   = ins.rotation;
    }
    if (scaleRow) scaleRow.style.display = ins ? '' : 'none';
    if (rotRow)   rotRow.style.display   = ins ? '' : 'none';

    let groupInfo = document.getElementById('svgInsertGroupInfo');
    if (!groupInfo) {
        groupInfo = document.createElement('div');
        groupInfo.id = 'svgInsertGroupInfo';
        groupInfo.style.cssText = 'font-size:11px;color:var(--text-3);padding:2px 0 4px;';
        panel.insertBefore(groupInfo, panel.firstChild);
    }
    groupInfo.textContent = count > 1 ? `${count}개 선택됨 (Shift+클릭으로 추가/해제)` : '';
    groupInfo.style.display = count > 1 ? '' : 'none';
}

// insert의 중심(canvas 로직 좌표)과 회전 적용된 우하단 모서리(리사이즈 핸들 위치)를 계산
// ctx/canvas/logW/logH/panX/panY/scaleFactor 는 각 엔진 스크립트의 전역을 그대로 참조
const SVG_INSERT_HANDLE_HIT_R = 14;
function _insertCorner(ins) {
    const ox = logW / 2 + panX, oy = logH / 2 + panY;
    const cx = ox + ins.cx * scaleFactor;
    const cy = oy + ins.cy * scaleFactor;
    const renderScale = ins.baseScale * ins.scaleMul;
    const hw = ins.w * renderScale * scaleFactor / 2;
    const hh = ins.h * renderScale * scaleFactor / 2;
    const rot = ins.rotation * Math.PI / 180;
    return {
        cx, cy,
        x: cx + (hw * Math.cos(rot) - hh * Math.sin(rot)),
        y: cy + (hw * Math.sin(rot) + hh * Math.cos(rot)),
    };
}

function drawSvgInserts() {
    if (window.__pmokKonvaInserts) return; // Konva.Image가 대신 렌더링
    if (!svgInserts.length) return;
    const ox = logW / 2 + panX, oy = logH / 2 + panY;
    svgInserts.forEach(ins => {
        _normalizeInsert(ins);
        const img = _loadMotifImg(ins.url);
        if (!img.complete || !img.naturalWidth) return;
        const x = ox + ins.cx * scaleFactor;
        const y = oy + ins.cy * scaleFactor;
        const renderScale = ins.baseScale * ins.scaleMul;
        const w = ins.w * renderScale * scaleFactor;
        const h = ins.h * renderScale * scaleFactor;
        ctx.save();
        ctx.translate(x, y);
        ctx.rotate(ins.rotation * Math.PI / 180);
        ctx.drawImage(img, -w / 2, -h / 2, w, h);
        if (selectedInsertIds.has(ins.id)) {
            ctx.setLineDash([5, 4]);
            ctx.strokeStyle = '#3A8C82';
            ctx.lineWidth = 1.5;
            ctx.strokeRect(-w / 2, -h / 2, w, h);
            ctx.setLineDash([]);
        }
        ctx.restore();
        if (selectedInsertIds.size === 1 && selectedInsertIds.has(ins.id)) {
            const corner = _insertCorner(ins);
            ctx.beginPath();
            ctx.arc(corner.x, corner.y, 6, 0, Math.PI * 2);
            ctx.fillStyle = '#3A8C82';
            ctx.fill();
            ctx.lineWidth = 1.5;
            ctx.strokeStyle = '#fff';
            ctx.stroke();
        }
    });
}

(function () {
    function toLogical(clientX, clientY) {
        const rect = canvas.getBoundingClientRect();
        return {
            x: (clientX - rect.left) * (logW / rect.width),
            y: (clientY - rect.top)  * (logH / rect.height),
        };
    }

    function hitTestInsert(clientX, clientY) {
        const p  = toLogical(clientX, clientY);
        const ox = logW / 2 + panX, oy = logH / 2 + panY;
        for (let i = svgInserts.length - 1; i >= 0; i--) {
            const ins = svgInserts[i];
            _normalizeInsert(ins);
            const x = ox + ins.cx * scaleFactor;
            const y = oy + ins.cy * scaleFactor;
            const r = Math.max(ins.w, ins.h) * ins.baseScale * ins.scaleMul * scaleFactor / 2;
            if (Math.hypot(p.x - x, p.y - y) <= r) return ins;
        }
        return null;
    }

    let insertDrag = null; // { mode: 'move'|'resize', starts?: Map, id?: string, ... }

    function onDown(clientX, clientY, targetEl, shiftKey = false) {
        if (targetEl !== canvas && targetEl !== container) return false;

        // 리사이즈 핸들: 단일 선택일 때만
        if (selectedInsertIds.size === 1) {
            const primaryId = [...selectedInsertIds][0];
            const sel = svgInserts.find(i => i.id === primaryId);
            if (sel) {
                _normalizeInsert(sel);
                const corner = _insertCorner(sel);
                const p = toLogical(clientX, clientY);
                if (Math.hypot(p.x - corner.x, p.y - corner.y) <= SVG_INSERT_HANDLE_HIT_R) {
                    insertDrag = {
                        mode: 'resize', id: primaryId,
                        centerX: corner.cx, centerY: corner.cy,
                        startDist: Math.hypot(corner.x - corner.cx, corner.y - corner.cy),
                        startScaleMul: sel.scaleMul,
                    };
                    return true;
                }
            }
        }

        const hit = hitTestInsert(clientX, clientY);
        if (!hit) {
            if (selectedInsertIds.size) selectSvgInsert(null);
            return false;
        }

        if (shiftKey) {
            toggleSelectInsert(hit.id);
        } else if (!selectedInsertIds.has(hit.id)) {
            selectSvgInsert(hit.id);
        }

        if (selectedInsertIds.size) {
            insertDrag = {
                mode: 'move',
                starts: new Map(svgInserts.filter(i => selectedInsertIds.has(i.id)).map(i => [i.id, { cx: i.cx, cy: i.cy }])),
                startClientX: clientX, startClientY: clientY,
            };
        }
        return true;
    }

    function onMove(clientX, clientY) {
        if (!insertDrag) return false;

        if (insertDrag.mode === 'resize') {
            const ins = svgInserts.find(i => i.id === insertDrag.id);
            if (!ins) { insertDrag = null; return false; }
            const p = toLogical(clientX, clientY);
            const dist = Math.hypot(p.x - insertDrag.centerX, p.y - insertDrag.centerY);
            if (insertDrag.startDist > 0) {
                const scaleMul = Math.min(3, Math.max(0.1, insertDrag.startScaleMul * dist / insertDrag.startDist));
                ins.scaleMul = scaleMul;
                const scaleEl = document.getElementById('svgInsertScale');
                if (scaleEl) scaleEl.value = Math.round(scaleMul * 100);
            }
            draw();
            return true;
        }

        const dcx = (clientX - insertDrag.startClientX) / scaleFactor;
        const dcy = (clientY - insertDrag.startClientY) / scaleFactor;
        svgInserts.forEach(ins => {
            const start = insertDrag.starts?.get(ins.id);
            if (!start) return;
            let cx = start.cx + dcx;
            let cy = start.cy + dcy;
            if (selectedInsertIds.size === 1) {
                const snapped = typeof snapToNode === 'function' ? snapToNode(cx, cy) : null;
                if (snapped) { cx = snapped.cx; cy = snapped.cy; }
            }
            ins.cx = cx; ins.cy = cy;
        });
        draw();
        return true;
    }

    function onUp() {
        const had = !!insertDrag;
        insertDrag = null;
        return had;
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (!container) return;

        // 마우스: 캡처 단계에서 먼저 검사해 엔진 자체 mousedown(팬 등)보다 우선권을 가짐
        container.addEventListener('mousedown', e => {
            if (window.__pmokKonvaInserts) return; // Konva가 삽입 이벤트 처리
            if (onDown(e.clientX, e.clientY, e.target, e.shiftKey)) e.stopImmediatePropagation();
        }, { capture: true });
        window.addEventListener('mousemove', e => {
            if (window.__pmokKonvaInserts) return;
            if (onMove(e.clientX, e.clientY)) e.stopImmediatePropagation();
        }, { capture: true });
        window.addEventListener('mouseup', () => { if (!window.__pmokKonvaInserts) onUp(); }, { capture: true });

        // 터치: 기존 터치 핸들러(팬/핀치)보다 먼저 처리되도록 캡처 단계 사용
        container.addEventListener('touchstart', e => {
            if (window.__pmokKonvaInserts) return;
            if (e.touches.length !== 1) return;
            const t = e.touches[0];
            if (onDown(t.clientX, t.clientY, e.target)) e.stopImmediatePropagation();
        }, { capture: true, passive: true });
        container.addEventListener('touchmove', e => {
            if (window.__pmokKonvaInserts) return;
            if (e.touches.length !== 1 || !insertDrag) return;
            const t = e.touches[0];
            if (onMove(t.clientX, t.clientY)) e.stopImmediatePropagation();
        }, { capture: true, passive: true });
        container.addEventListener('touchend', () => { if (!window.__pmokKonvaInserts) onUp(); }, { capture: true });
    });
})();

(function () {
    function openLibraryPicker() {
        const modal = document.getElementById('svgPickerModal');
        const grid  = document.getElementById('svgPickerGrid');
        if (!modal || !grid) return;
        grid.innerHTML = '<div style="grid-column:1/-1;font-size:12px;color:var(--text-3);">불러오는 중…</div>';
        modal.style.display = 'flex';
        fetch('/src/api/svg_motifs.php').then(r => r.json()).then(data => {
            const motifs = data.motifs || [];
            if (!motifs.length) { grid.innerHTML = '<div style="grid-column:1/-1;font-size:12px;color:var(--text-3);">등록된 문양이 없습니다.</div>'; return; }
            grid.innerHTML = motifs.map(m => `
                <div class="svg-picker-item" data-url="${m.svg_url}" title="${m.name}">
                    <img src="${m.svg_url}" alt="${m.name}">
                    <span>${m.name}</span>
                </div>`).join('');
            grid.querySelectorAll('.svg-picker-item').forEach(el => {
                el.addEventListener('click', () => {
                    closeLibraryPicker();
                    const img = new Image();
                    img.onload = () => addSvgInsert(el.dataset.url, img.naturalWidth, img.naturalHeight);
                    img.onerror = () => addSvgInsert(el.dataset.url, 100, 100);
                    img.src = el.dataset.url;
                });
            });
        }).catch(() => { grid.innerHTML = '<div style="grid-column:1/-1;font-size:12px;color:var(--text-3);">불러오기 실패</div>'; });
    }

    function closeLibraryPicker() {
        const modal = document.getElementById('svgPickerModal');
        if (modal) modal.style.display = 'none';
    }

    function handleSvgFileUpload(input) {
        const file = input.files[0];
        if (!file) return;
        if (!/\.svg$/i.test(file.name) && file.type !== 'image/svg+xml') {
            alert('SVG 파일만 업로드할 수 있습니다.');
            input.value = '';
            return;
        }
        const uploadBtn = document.querySelector('button[onclick*="svgFileInput"]');
        if (uploadBtn) uploadBtn.classList.add('btn-loading');
        const reader = new FileReader();
        reader.onload = async e => {
            try {
                const res = await fetch('/src/api/uploads/svg_insert.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + localStorage.getItem('pmok_auth_token') },
                    body: JSON.stringify({ svg_data: e.target.result }),
                });
                const data = await res.json();
                input.value = '';
                if (!data.ok) { alert(data.error || '업로드 실패'); return; }
                const img = new Image();
                img.onload = () => addSvgInsert(data.url, img.naturalWidth, img.naturalHeight);
                img.onerror = () => addSvgInsert(data.url, 100, 100);
                img.src = data.url;
            } finally {
                if (uploadBtn) uploadBtn.classList.remove('btn-loading');
            }
        };
        reader.readAsDataURL(file);
    }

    window.openSvgLibraryPicker  = openLibraryPicker;
    window.closeSvgLibraryPicker = closeLibraryPicker;
    window.handleSvgFileUpload   = handleSvgFileUpload;

    document.addEventListener('DOMContentLoaded', () => {
        const scaleEl = document.getElementById('svgInsertScale');
        const rotEl   = document.getElementById('svgInsertRotation');
        const delBtn  = document.getElementById('btnSvgInsertDelete');
        const dupBtn  = document.getElementById('btnSvgInsertDuplicate');
        if (scaleEl) scaleEl.addEventListener('input', e => updateSelectedInsert({ scaleMul: (+e.target.value || 100) / 100 }));
        if (rotEl)   rotEl.addEventListener('input', e => updateSelectedInsert({ rotation: +e.target.value || 0 }));
        if (delBtn)  delBtn.addEventListener('click', removeSelectedInsert);
        if (dupBtn)  dupBtn.addEventListener('click', duplicateSelectedInsert);
        const pickerModal = document.getElementById('svgPickerModal');
        if (pickerModal) pickerModal.addEventListener('click', e => { if (e.target === pickerModal) closeLibraryPicker(); });
    });
})();

(function () {
    function won(n) { return Math.round(n).toLocaleString('ko-KR'); }

    function updateWoodCost() {
        const woodEl    = document.getElementById('spWoodCost');
        const doorEl    = document.getElementById('spCostDoor');
        const muntolEl  = document.getElementById('spCostMuntol');
        const startEl   = document.querySelector('.sb-price-start');
        const endEl     = document.querySelector('.sb-price-end');

        const p = window.__pmokLastParts;
        if (!p?.woodJae) {
            if (woodEl)   woodEl.textContent   = '–';
            if (doorEl)   doorEl.textContent   = '–';
            if (muntolEl) muntolEl.textContent = '–';
            if (startEl)  startEl.textContent  = '–';
            if (endEl)    endEl.textContent    = '';
            window.__pmokEstimatedPrice = 0;
            return;
        }

        const opt    = document.getElementById('txtWood')?.selectedOptions?.[0];
        const price  = parseFloat(opt?.dataset?.price  ?? 0);
        const weight = parseFloat(opt?.dataset?.weight ?? 1);
        if (!price) {
            if (woodEl)   woodEl.textContent   = '–';
            if (doorEl)   doorEl.textContent   = '–';
            if (muntolEl) muntolEl.textContent = '–';
            if (startEl)  startEl.textContent  = '–';
            if (endEl)    endEl.textContent    = '';
            window.__pmokEstimatedPrice = 0;
            return;
        }

        const tw          = p.techWeight ?? 1;
        const doorCost    = Math.round((p.woodJae_door   ?? 0) * tw * weight * price);
        const muntolCost  = Math.round((p.woodJae_muntol ?? 0) *      weight * price);
        const woodCost    = doorCost + muntolCost;
        const estMin      = woodCost * 5;
        const estMax      = Math.round(estMin * 1.15);

        if (woodEl)   woodEl.textContent   = won(woodCost) + '원';
        if (doorEl)   doorEl.textContent   = won(doorCost) + '원';
        if (muntolEl) muntolEl.textContent = won(muntolCost) + '원';
        if (startEl)  startEl.textContent  = won(estMin);
        if (endEl)    endEl.textContent    = ` ~ ${won(estMax)}원`;

        window.__pmokEstimatedPrice = estMin;
    }

    function isAdminRole() {
        try {
            const token = localStorage.getItem('pmok_auth_token');
            if (!token) return false;
            const payload = JSON.parse(atob(token.split('.')[1].replace(/-/g, '+').replace(/_/g, '/')));
            return ['s', 'm', 'a'].includes(payload.role);
        } catch { return false; }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const sidebar      = document.getElementById('sidebar');
        const rightSidebar = document.getElementById('rightSidebar');
        if (sidebar) {
            sidebar.addEventListener('change', updateWoodCost);
            sidebar.addEventListener('input',  updateWoodCost);
        }
        if (rightSidebar) {
            rightSidebar.addEventListener('change', updateWoodCost);
        }
        if (isAdminRole()) {
            document.querySelector('.sb-price-breakdown')?.classList.add('admin-visible');
        }
    });

    window.__pmokUpdateWoodCost = updateWoodCost;
})();
