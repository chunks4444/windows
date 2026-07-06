<?php
// 엔진 "제작 시방서" / "부재목록" / "예산견적 상세" 열람 권한 판정.
// role(s/m)은 자동으로 시방서·부재목록을 통과하고, role s만 예산견적 상세를 자동 통과한다.
// 그 외 회원은 users.view_spec/view_parts/view_cost 플래그로 개별 승인.
require_once __DIR__ . '/jwt.php';
require_once __DIR__ . '/db.php';

function get_content_permissions(): array {
    $payload = jwt_from_request();
    if (!$payload) return ['spec' => false, 'parts' => false, 'cost' => false];

    $role = $payload['role'] ?? null;
    $perms = [
        'spec'  => in_array($role, ['s', 'm'], true),
        'parts' => in_array($role, ['s', 'm'], true),
        'cost'  => $role === 's',
    ];
    if ($perms['spec'] && $perms['parts'] && $perms['cost']) return $perms;

    $stmt = db()->prepare('SELECT view_spec, view_parts, view_cost FROM users WHERE id = ?');
    $stmt->execute([$payload['sub'] ?? null]);
    if ($row = $stmt->fetch()) {
        $perms['spec']  = $perms['spec']  || (bool)$row['view_spec'];
        $perms['parts'] = $perms['parts'] || (bool)$row['view_parts'];
        $perms['cost']  = $perms['cost']  || (bool)$row['view_cost'];
    }
    return $perms;
}
