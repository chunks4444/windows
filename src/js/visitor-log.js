(function () {
    if (!navigator.sendBeacon) return;

    var conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;

    var data = JSON.stringify({
        screen:   screen.width + 'x' + screen.height,
        viewport: window.innerWidth + 'x' + window.innerHeight,
        tz:       (Intl && Intl.DateTimeFormat
                    ? Intl.DateTimeFormat().resolvedOptions().timeZone
                    : ''),
        touch:    ('ontouchstart' in window) ? 1 : 0,
        conn:     conn ? (conn.effectiveType || '') : '',
        dark:     window.matchMedia('(prefers-color-scheme: dark)').matches ? 1 : 0,
        dpr:      window.devicePixelRatio || 1,
        mem:      navigator.deviceMemory  || 0,
        cpu:      navigator.hardwareConcurrency || 0,
        page:     location.pathname + location.search,
    });

    navigator.sendBeacon(
        '/src/api/log/client.php',
        new Blob([data], { type: 'application/json' })
    );
})();
