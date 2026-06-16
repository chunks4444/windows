<?php
require_once __DIR__ . '/lib/db.php';
$pdo = db();

// 첫 번째 work 삭제 후 새 이미지로 교체
$first = $pdo->query('SELECT id FROM works ORDER BY sort_order, id LIMIT 1')->fetch();
if ($first) {
    $pdo->prepare('UPDATE works SET image_url=?, title=?, description=? WHERE id=?')
        ->execute([
            'https://picsum.photos/seed/pmok01/500/700',
            '한옥 창호',
            '경기도 양평 공방',
            $first['id']
        ]);
    echo "완료: 첫 번째 작품 이미지 교체됨";
} else {
    echo "데이터 없음";
}
