<?php
// 회원별 활동 단계(user_milestones)의 과거 분을 기존 기록에서 채운다. (2026-11-09)
//   처음 스튜디오 연 시각   ← page_views (엔진 페이지, 로그인 상태 방문)  — page_views는 1년치만 있다
//   처음 도면 저장          ← drawings.created_at
//   처음 내보내기           ← drawing_export_logs
//   처음 렌더링             ← renders
//   처음 견적요청           ← orders (취소된 건 포함: 견적요청을 했다는 사실이 기준)
//   마지막 방문             ← page_views 마지막 방문, users.last_login_at 중 늦은 쪽
//
//   php ops/backfill_user_milestones.php            # 미리보기 (DB는 바꾸지 않고 채워질 숫자만 보여준다)
//   php ops/backfill_user_milestones.php --apply    # 실제로 채운다
//
// 여러 번 돌려도 안전하다: 이미 있는 값은 더 이른 쪽을 남기고, 마지막 방문은 더 늦은 쪽을 남긴다.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

require_once __DIR__ . '/../src/lib/db.php';

$apply = in_array('--apply', $argv ?? [], true);
$pdo   = db();
echo "모드: " . ($apply ? 'APPLY(실제 반영)' : 'DRY-RUN(미리보기)') . "\n";

$map = function (string $sql) use ($pdo): array {
    return $pdo->query($sql)->fetchAll(PDO::FETCH_KEY_PAIR);
};
$studio  = $map("SELECT user_id, MIN(visited_at) FROM page_views WHERE user_id IS NOT NULL AND page REGEXP '/src/engine/[a-z]+/[a-z]+\\\\.php' GROUP BY user_id");
$lastPv  = $map("SELECT user_id, MAX(visited_at) FROM page_views WHERE user_id IS NOT NULL GROUP BY user_id");
$drawing = $map("SELECT user_id, MIN(created_at) FROM drawings GROUP BY user_id");
$export  = $map("SELECT user_id, MIN(created_at) FROM drawing_export_logs GROUP BY user_id");
$render  = $map("SELECT user_id, MIN(created_at) FROM renders GROUP BY user_id");
$order   = $map("SELECT user_id, MIN(created_at) FROM orders GROUP BY user_id");

$users = $pdo->query("SELECT id, last_login_at FROM users")->fetchAll(PDO::FETCH_ASSOC);
$earliest = fn($a, $b) => $a === null ? $b : ($b === null ? $a : min($a, $b));

$upsert = $pdo->prepare(
    "INSERT INTO user_milestones (user_id, first_studio_at, first_drawing_at, first_export_at, first_render_at, first_order_at, last_seen_at)
     VALUES (:id, :studio, :drawing, :export, :render, :order, :seen)
     ON DUPLICATE KEY UPDATE
        first_studio_at  = IF(VALUES(first_studio_at)  IS NULL, first_studio_at,  IF(first_studio_at  IS NULL, VALUES(first_studio_at),  LEAST(first_studio_at,  VALUES(first_studio_at)))),
        first_drawing_at = IF(VALUES(first_drawing_at) IS NULL, first_drawing_at, IF(first_drawing_at IS NULL, VALUES(first_drawing_at), LEAST(first_drawing_at, VALUES(first_drawing_at)))),
        first_export_at  = IF(VALUES(first_export_at)  IS NULL, first_export_at,  IF(first_export_at  IS NULL, VALUES(first_export_at),  LEAST(first_export_at,  VALUES(first_export_at)))),
        first_render_at  = IF(VALUES(first_render_at)  IS NULL, first_render_at,  IF(first_render_at  IS NULL, VALUES(first_render_at),  LEAST(first_render_at,  VALUES(first_render_at)))),
        first_order_at   = IF(VALUES(first_order_at)   IS NULL, first_order_at,   IF(first_order_at   IS NULL, VALUES(first_order_at),   LEAST(first_order_at,   VALUES(first_order_at)))),
        last_seen_at     = IF(VALUES(last_seen_at) IS NULL, last_seen_at, IF(last_seen_at IS NULL, VALUES(last_seen_at), GREATEST(last_seen_at, VALUES(last_seen_at))))"
);

$n = 0; $cnt = ['studio' => 0, 'drawing' => 0, 'export' => 0, 'render' => 0, 'order' => 0, 'seen' => 0];
foreach ($users as $u) {
    $id = (int)$u['id'];
    $row = [
        'id'      => $id,
        'studio'  => $studio[$id]  ?? null,
        'drawing' => $drawing[$id] ?? null,
        'export'  => $export[$id]  ?? null,
        'render'  => $render[$id]  ?? null,
        'order'   => $order[$id]   ?? null,
        'seen'    => ($lastPv[$id] ?? null) !== null && ($u['last_login_at'] ?? null) !== null ? max($lastPv[$id], $u['last_login_at']) : ($lastPv[$id] ?? $u['last_login_at'] ?? null),
    ];
    foreach (['studio', 'drawing', 'export', 'render', 'order', 'seen'] as $k) if ($row[$k] !== null) $cnt[$k]++;
    if ($apply) $upsert->execute($row);
    $n++;
}

echo "회원 $n 명 처리\n";
printf("  처음 스튜디오 연 회원 %d / 도면 저장 %d / 내보내기 %d / 렌더링 %d / 견적요청 %d / 마지막 방문 기록 %d\n",
    $cnt['studio'], $cnt['drawing'], $cnt['export'], $cnt['render'], $cnt['order'], $cnt['seen']);
if ($apply) {
    echo "반영 완료. user_milestones 행 수: " . $pdo->query("SELECT COUNT(*) FROM user_milestones")->fetchColumn() . "\n";
}
echo "끝\n";
