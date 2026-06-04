<?php
require_once __DIR__ . '/db.php';

class Drawing {

    static function save(int $userId, string $type, string $name, ?int $createdAtMs, array $versions): int {
        $pdo  = db();
        $stmt = $pdo->prepare('SELECT id FROM drawings WHERE user_id = ? AND type = ?');
        $stmt->execute([$userId, $type]);
        $row  = $stmt->fetch();

        if ($row) {
            $drawingId = (int) $row['id'];
            $pdo->prepare('UPDATE drawings SET name = ?, updated_at = NOW() WHERE id = ?')
                ->execute([$name, $drawingId]);
        } else {
            $createdAt = $createdAtMs ? date('Y-m-d H:i:s', intval($createdAtMs / 1000)) : date('Y-m-d H:i:s');
            $pdo->prepare('INSERT INTO drawings (user_id, type, name, created_at) VALUES (?, ?, ?, ?)')
                ->execute([$userId, $type, $name, $createdAt]);
            $drawingId = (int) $pdo->lastInsertId();
        }

        $pdo->prepare('DELETE FROM drawing_versions WHERE drawing_id = ?')->execute([$drawingId]);
        $stmt = $pdo->prepare('INSERT INTO drawing_versions (drawing_id, params, saved_at) VALUES (?, ?, ?)');
        foreach ($versions as $ver) {
            $savedAt = date('Y-m-d H:i:s', intval(($ver['savedAt'] ?? time() * 1000) / 1000));
            $stmt->execute([$drawingId, json_encode($ver['params']), $savedAt]);
        }

        return $drawingId;
    }

    static function load(int $userId, string $type): ?array {
        $pdo  = db();
        $stmt = $pdo->prepare('SELECT * FROM drawings WHERE user_id = ? AND type = ?');
        $stmt->execute([$userId, $type]);
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
}
