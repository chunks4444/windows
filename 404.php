<?php
header('Content-Type: text/html; charset=UTF-8');
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <?php require_once __DIR__ . '/src/lib/meta.php'; meta_tags(['title' => '페이지를 찾을 수 없습니다 - 평목']); ?>
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .err-wrap {
            min-height: 60vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 60px 20px;
        }
        .err-code {
            font-size: 88px;
            font-weight: 800;
            color: var(--accent);
            letter-spacing: -2px;
            line-height: 1;
        }
        .err-msg {
            font-size: var(--fs-18, 18px);
            color: var(--text-2, #666);
            margin: 16px 0 32px;
        }
        .err-home-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            background: var(--accent);
            color: #fff;
            border-radius: 999px;
            font-weight: 600;
            text-decoration: none;
            transition: background .15s;
        }
        .err-home-btn:hover { background: var(--accent-hover, var(--accent)); color: #fff; }
    </style>
</head>

<body>
    <?php include __DIR__ . '/src/components/nav.php'; ?>

    <div class="err-wrap">
        <div class="err-code">404</div>
        <p class="err-msg">요청하신 페이지를 찾을 수 없습니다.</p>
        <a href="/" class="err-home-btn">홈으로 돌아가기</a>
    </div>

    <?php include __DIR__ . '/src/components/footer.php'; ?>
</body>

</html>
