<?php
require_once __DIR__ . '/../includes/functions.php';

class Listing {
    public const CATEGORIES = [
        'Textbooks', 'Reviewers', 'Laboratory Manuals',
        'Course Materials', 'Uniforms and Academic Attire', 'Other Academic Items'
    ];
    public const CONDITIONS = ['Brand New', 'Like New', 'Good', 'Fair'];
    public const STATUSES = ['available', 'reserved', 'sold', 'removed'];
    public const DEPARTMENTS = [
        'Accountancy', 'Architecture', 'Arts and Sciences', 'Business',
        'Engineering', 'Hospitality Management', 'Information Technology',
        'Medicine', 'Nursing', 'Pharmacy', 'Psychology', 'Education',
        'Communication', 'Law', 'Other'
    ];

    public static function findById(int $id): ?array {
        $stmt = db()->prepare(
            'SELECT l.*, u.full_name AS seller_name, u.student_number AS seller_number, u.user_id AS seller_id
             FROM listings l JOIN users u ON u.user_id = l.seller_id
             WHERE l.listing_id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // Marketplace search with filters.
    public static function search(array $f): array {
        $where = ['l.status != "removed"'];
        $params = [];
        if (!empty($f['q'])) {
            $where[] = '(l.title LIKE ? OR l.description LIKE ? OR l.course_code LIKE ?)';
            $like = '%' . $f['q'] . '%';
            array_push($params, $like, $like, $like);
        }
        if (!empty($f['category'])) { $where[] = 'l.category = ?'; $params[] = $f['category']; }
        if (!empty($f['department'])) { $where[] = 'l.department = ?'; $params[] = $f['department']; }
        if (!empty($f['condition'])) { $where[] = 'l.`condition` = ?'; $params[] = $f['condition']; }
        if (!empty($f['status']) && in_array($f['status'], self::STATUSES, true)) {
            $where[] = 'l.status = ?'; $params[] = $f['status'];
        }
        $order = 'l.created_at DESC';
        switch ($f['sort'] ?? '') {
            case 'price_asc':  $order = 'l.price ASC'; break;
            case 'price_desc': $order = 'l.price DESC'; break;
            case 'oldest':     $order = 'l.created_at ASC'; break;
        }
        $sql = 'SELECT l.*, u.full_name AS seller_name
                FROM listings l JOIN users u ON u.user_id = l.seller_id
                WHERE ' . implode(' AND ', $where) . "
                ORDER BY $order LIMIT 200";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function bySeller(int $sellerId): array {
        $stmt = db()->prepare(
            'SELECT * FROM listings WHERE seller_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$sellerId]);
        return $stmt->fetchAll();
    }

    public static function create(array $d, int $sellerId): int {
        $stmt = db()->prepare(
            'INSERT INTO listings (seller_id, title, description, category, department,
             course_code, edition, price, `condition`, status, image_path)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "available", ?)'
        );
        $stmt->execute([
            $sellerId, $d['title'], $d['description'] ?? null, $d['category'],
            $d['department'] ?: null, $d['course_code'] ?: null, $d['edition'] ?: null,
            $d['price'], $d['condition'], $d['image_path'] ?? null
        ]);
        return (int)db()->lastInsertId();
    }

    public static function update(int $id, array $d): void {
        $stmt = db()->prepare(
            'UPDATE listings SET title=?, description=?, category=?, department=?,
             course_code=?, edition=?, price=?, `condition`=? WHERE listing_id=?'
        );
        $stmt->execute([
            $d['title'], $d['description'] ?? null, $d['category'],
            $d['department'] ?: null, $d['course_code'] ?: null, $d['edition'] ?: null,
            $d['price'], $d['condition'], $id
        ]);
    }

    public static function setStatus(int $id, string $status): void {
        $stmt = db()->prepare('UPDATE listings SET status = ? WHERE listing_id = ?');
        $stmt->execute([$status, $id]);
    }

    public static function delete(int $id): void {
        $stmt = db()->prepare('DELETE FROM listings WHERE listing_id = ?');
        $stmt->execute([$id]);
    }

    public static function counts(): array {
        $rows = db()->query(
            'SELECT status, COUNT(*) c FROM listings GROUP BY status'
        )->fetchAll();
        $out = ['total' => 0, 'available' => 0, 'reserved' => 0, 'sold' => 0, 'removed' => 0];
        foreach ($rows as $r) { $out[$r['status']] = (int)$r['c']; $out['total'] += (int)$r['c']; }
        return $out;
    }

    public static function latest(int $limit = 12): array {
        return db()->query(
            'SELECT l.*, u.full_name AS seller_name FROM listings l
             JOIN users u ON u.user_id = l.seller_id
             WHERE l.status != "removed" ORDER BY l.created_at DESC LIMIT ' . (int)$limit
        )->fetchAll();
    }
}
