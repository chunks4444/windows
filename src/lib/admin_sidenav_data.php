<?php
// 어드민 사이드메뉴 트리 데이터 — 데스크톱 사이드바(components/admin_sidenav.php)와
// 모바일 드로어(components/nav.php) 양쪽에서 공유. 새 어드민 페이지 추가 시 여기만 수정.
return [
    ['title' => '통계', 'items' => [
        ['stats.php',            'bi-bar-chart-line',  '접속 통계'],
    ]],
    ['title' => '블로그', 'items' => [
        ['blog.php',             'bi-journal-text',    '블로그 글 관리'],
        ['blog_series.php',      'bi-collection',      '시리즈 관리'],
    ]],
    ['title' => '콘텐츠 관리', 'items' => [
        ['works.php',            'bi-images',          '포트폴리오 관리'],
        ['svg_motifs.php',       'bi-flower1',         '문양(SVG) 라이브러리'],
        ['guide.php',            'bi-book-half',       '가이드 관리'],
        ['company.php',          'bi-building',        '회사소개 관리'],
        ['faq.php',              'bi-question-circle', 'FAQ 관리'],
        ['notice.php',           'bi-megaphone',       '공지 배너'],
        ['collection.php',       'bi-image',           '컬렉션 관리'],
        ['hero_slides.php',      'bi-images',          '슬라이드 관리'],
        ['space_cards.php',      'bi-grid',            '메인 큐레이션 관리'],
        ['studio_cards.php',     'bi-grid-1x2',        '스튜디오 카드 관리'],
        ['colors.php',           'bi-palette',         '컬러 팔레트 관리'],
    ]],
    ['title' => '주문 관리', 'items' => [
        ['orders.php',           'bi-receipt',         '주문 관리'],
    ]],
    ['title' => '설정값 관리', 'items' => [
        ['cost_table.php',       'bi-calculator',      '원가 테이블'],
        ['meta.php',             'bi-search',          'SEO 메타 관리'],
        ['oauth.php',            'bi-key',             'SNS 로그인 설정'],
        ['mail_settings.php',    'bi-envelope',        '메일 발송 설정'],
        ['render_settings.php',  'bi-stars',           'AI 렌더링 설정'],
        ['engine_settings.php',  'bi-sliders',         '엔진 기본값 관리'],
        ['pattern_categories.php','bi-tags',           '패턴 카테고리'],
        ['pattern_modifiers.php', 'bi-tag',            '수식어 관리'],
        ['pattern_drawings.php',  'bi-diagram-3',      '도면 분류'],
        ['ai_tuning.php',        'bi-stars',           'AI 튜닝'],
        ['ai_stats.php',         'bi-bar-chart-line',  'AI 사용 통계'],
    ]],
    ['title' => '회원 관리', 'items' => [
        ['users.php',            'bi-shield-lock',     '회원 목록/권한'],
    ]],
    ['title' => '배포 점검', 'items' => [
        ['smoke_test.php',       'bi-activity',        '스모크 테스트'],
    ]],
];
