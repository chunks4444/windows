<?php
require_once __DIR__ . '/db.php';

class Drawing {

    // 도면 저장 (type + title 기준 upsert, 버전 전체 교체)
    static function save(int $userId, string $type, string $title, ?int $createdAtMs, array $versions, ?string $thumbnail = null, int $workTimeSec = 0): int {
        $pdo  = db();
        $stmt = $pdo->prepare('SELECT id FROM drawings WHERE user_id = ? AND type = ? AND title = ?');
        $stmt->execute([$userId, $type, $title]);
        $row  = $stmt->fetch();

        if ($row) {
            $drawingId = (int) $row['id'];
            $pdo->prepare('UPDATE drawings SET updated_at = NOW(), thumbnail = COALESCE(?, thumbnail), work_time_sec = ? WHERE id = ?')
                ->execute([$thumbnail, $workTimeSec, $drawingId]);
        } else {
            $createdAt = $createdAtMs ? date('Y-m-d H:i:s', intval($createdAtMs / 1000)) : date('Y-m-d H:i:s');
            // IGNORE 로 중복 삽입(레이스 컨디션) 방지 — 중복이면 INSERT 건너뜀
            $pdo->prepare('INSERT IGNORE INTO drawings (user_id, type, title, created_at, thumbnail, work_time_sec) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([$userId, $type, $title, $createdAt, $thumbnail, $workTimeSec]);
            $drawingId = (int) $pdo->lastInsertId();
            // lastInsertId()가 0이면 이미 존재 → SELECT로 ID 조회
            if ($drawingId === 0) {
                $stmt2 = $pdo->prepare('SELECT id FROM drawings WHERE user_id = ? AND type = ? AND title = ?');
                $stmt2->execute([$userId, $type, $title]);
                $drawingId = (int) $stmt2->fetchColumn();
            }
        }

        $pdo->prepare('DELETE FROM drawing_versions WHERE drawing_id = ?')->execute([$drawingId]);
        $stmt = $pdo->prepare('INSERT INTO drawing_versions (drawing_id, params, saved_at) VALUES (?, ?, ?)');
        foreach ($versions as $ver) {
            $savedAt = date('Y-m-d H:i:s', intval(($ver['savedAt'] ?? time() * 1000) / 1000));
            $stmt->execute([$drawingId, json_encode($ver['params']), $savedAt]);
        }

        return $drawingId;
    }

    // 제목 변경 (버전에 영향 없음)
    static function rename(int $userId, string $type, string $oldTitle, string $newTitle): bool {
        $pdo  = db();
        $stmt = $pdo->prepare('UPDATE drawings SET title = ?, updated_at = NOW() WHERE user_id = ? AND type = ? AND title = ?');
        $stmt->execute([$newTitle, $userId, $type, $oldTitle]);
        return $stmt->rowCount() > 0;
    }

    // 특정 도면 로드 (type + title)
    static function load(int $userId, string $type, string $title): ?array {
        $pdo  = db();
        $stmt = $pdo->prepare('SELECT * FROM drawings WHERE user_id = ? AND type = ? AND title = ?');
        $stmt->execute([$userId, $type, $title]);
        $drawing = $stmt->fetch();
        if (!$drawing) return null;

        $stmt = $pdo->prepare('SELECT params, saved_at FROM drawing_versions WHERE drawing_id = ? ORDER BY saved_at ASC');
        $stmt->execute([$drawing['id']]);

        $versions = array_map(fn($r) => [
            'savedAt' => strtotime($r['saved_at']) * 1000,
            'params'  => json_decode($r['params'], true),
        ], $stmt->fetchAll());

        return ['drawing' => $drawing, 'versions' => $versions];
    }

    // 유저의 타입별 도면 목록 (메타만, 버전·썸네일 미포함)
    static function list(int $userId, string $type): array {
        $pdo  = db();
        $stmt = $pdo->prepare('SELECT id, title, work_time_sec, created_at, updated_at, locked_at FROM drawings WHERE user_id = ? AND type = ? ORDER BY updated_at DESC');
        $stmt->execute([$userId, $type]);
        return $stmt->fetchAll();
    }

    // 유저의 전체 도면 목록 (타입 무관, 대시보드용) - 썸네일 미포함, 별도 thumbnails()로 lazy 로딩
    static function list_all(int $userId, int $page = 1, int $limit = 20): array {
        $pdo    = db();
        $offset = ($page - 1) * $limit;
        $stmt   = $pdo->prepare(
            'SELECT d.id, d.type, d.title, d.work_time_sec, d.created_at, d.updated_at, d.locked_at,
                    (SELECT COUNT(*) FROM drawing_versions WHERE drawing_id = d.id) AS version_count
             FROM drawings d
             WHERE d.user_id = ?
             ORDER BY d.updated_at DESC
             LIMIT ? OFFSET ?'
        );
        $stmt->execute([$userId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    // 도면 ID 배열에 대한 썸네일만 반환 (user_id 검증 포함)
    static function thumbnails(int $userId, array $ids): array {
        if (!$ids) return [];
        $pdo          = db();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt         = $pdo->prepare("SELECT id, thumbnail FROM drawings WHERE id IN ($placeholders) AND user_id = ?");
        $stmt->execute([...$ids, $userId]);
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[(int) $row['id']] = $row['thumbnail'];
        }
        return $result;
    }

    // 도면 삭제 (버전도 CASCADE 삭제)
    static function delete(int $userId, string $type, string $title): bool {
        $pdo  = db();
        $stmt = $pdo->prepare('DELETE FROM drawings WHERE user_id = ? AND type = ? AND title = ?');
        $stmt->execute([$userId, $type, $title]);
        return $stmt->rowCount() > 0;
    }

    // 견적요청 중인 도면인지 확인 (잠금 여부)
    static function is_locked(int $userId, string $type, string $title): bool {
        $pdo  = db();
        $stmt = $pdo->prepare('SELECT locked_at FROM drawings WHERE user_id = ? AND type = ? AND title = ?');
        $stmt->execute([$userId, $type, $title]);
        $row = $stmt->fetch();
        return $row && $row['locked_at'] !== null;
    }

    // 견적요청 접수 시 해당 도면 잠금
    static function lock(int $drawingId, int $userId): void {
        $pdo = db();
        $pdo->prepare('UPDATE drawings SET locked_at = NOW() WHERE id = ? AND user_id = ?')
            ->execute([$drawingId, $userId]);
    }
}
