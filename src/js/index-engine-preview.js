// 메인 페이지 작은 미리보기 위젯 — 실제 classic 엔진의 서버 치수 계산(geometry.php)을
// 그대로 호출해서, 파라미터가 바뀔 때마다 살 간격·칸 수가 실제로 재계산되는 걸 보여준다.
// (녹화 영상이 아니라 매번 실제 서버 응답으로 다시 그리는 것)
(function () {
    const canvas = document.getElementById('idxEnginePreview');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    const presets = [
        { cols: 8,  vRatio: 1.0, pattern: '2/3/2', slatT: 12 },
        { cols: 14, vRatio: 1.6, pattern: '3/5/3', slatT: 10 },
        { cols: 10, vRatio: 1.2, pattern: '4/7/4', slatT: 14 },
        { cols: 6,  vRatio: 2.0, pattern: '1/2/1', slatT: 16 },
    ];
    let idx = 0;

    async function fetchGeo(p) {
        const body = new URLSearchParams({
            cols: p.cols,
            frameOpeningW: 900,
            frameOpeningH: 2000,
            frameThick: 30,
            frameGap: 2,
            pungpanH: 0,
            pungpanOn: '0',
            frameW: 60,
            frameH: 60,
            slatT: p.slatT,
            vRatio: p.vRatio,
            pattern: p.pattern,
            doorType: 'swing',
            doorCount: 1,
        });
        try {
            const res = await fetch('/src/engine/classic/api/geometry.php', { method: 'POST', body });
            if (!res.ok) return null;
            const data = await res.json();
            return data.geo || null;
        } catch {
            return null;
        }
    }

    function draw(geo) {
        const W = canvas.width, H = canvas.height;
        ctx.clearRect(0, 0, W, H);
        if (!geo) return;

        const margin = 14;
        const scale = Math.min((W - margin * 2) / geo.outerW, (H - margin * 2) / geo.outerH);
        const ox = (W - geo.outerW * scale) / 2;
        const oy = (H - geo.outerH * scale) / 2;

        const ink = getComputedStyle(document.documentElement).getPropertyValue('--text').trim() || '#23262A';

        // 외곽 문틀(울거미)
        ctx.strokeStyle = ink;
        ctx.lineWidth = Math.max(1, geo.frameW * scale);
        ctx.strokeRect(
            ox + (geo.frameW * scale) / 2,
            oy + (geo.frameH * scale) / 2,
            geo.outerW * scale - geo.frameW * scale,
            geo.outerH * scale - geo.frameH * scale
        );

        const innerX = ox + geo.frameW * scale;
        const innerY = oy + geo.frameH * scale;
        const innerW = geo.innerW * scale;
        const innerH = geo.innerH * scale;

        ctx.fillStyle = ink;

        // 가로살
        const slatPx = Math.max(1, geo.slatT * scale);
        (geo.hBarYs || []).forEach((y) => {
            ctx.fillRect(innerX, innerY + y * scale - slatPx / 2, innerW, slatPx);
        });

        // 세로살 (가로살 사이 구간에만, 겹치지 않게)
        const stepPx = (geo.cellW + geo.slatT) * scale;
        (geo.vSegBounds || []).forEach((seg) => {
            const y0 = innerY + seg.y0 * scale;
            const y1 = innerY + seg.y1 * scale;
            for (let x = innerX + stepPx; x < innerX + innerW - 1; x += stepPx) {
                ctx.fillRect(x - slatPx / 2, y0, slatPx, y1 - y0);
            }
        });
    }

    async function tick() {
        const p = presets[idx % presets.length];
        idx++;
        const geo = await fetchGeo(p);
        canvas.style.opacity = '0';
        setTimeout(() => {
            draw(geo);
            canvas.style.opacity = '1';
        }, 200);
    }

    tick();
    setInterval(tick, 3200);
})();
