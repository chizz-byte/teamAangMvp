<?php
require_once __DIR__ . '/../includes/functions.php';

class Report {
    public static function create(int $listingId, int $reporterId, string $reason): void {
        $stmt = db()->prepare(
            'INSERT INTO reports (listing_id, reporter_id, reason) VALUES (?, ?, ?)'
        );
        $stmt->execute([$listingId, $reporterId, $reason]);
    }

    public static function alreadyReported(int $listingId, int $reporterId): bool {
        $stmt = db()->prepare(
            'SELECT 1 FROM reports WHERE listing_id = ? AND reporter_id = ? AND status = "pending"'
        );
        $stmt->execute([$listingId, $reporterId]);
        return (bool)$stmt->fetchColumn();
    }

    public static function pending(): array {
        return db()->query(
            'SELECT r.*, l.title AS listing_title, l.status AS listing_status,
                    u.full_name AS reporter_name, u.student_number AS reporter_number
             FROM reports r
             JOIN listings l ON l.listing_id = r.listing_id
             JOIN users u ON u.user_id = r.reporter_id
             WHERE r.status = "pending"
             ORDER BY r.created_at DESC'
        )->fetchAll();
    }

    public static function all(): array {
        return db()->query(
            'SELECT r.*, l.title AS listing_title, u.full_name AS reporter_name
             FROM reports r
             JOIN listings l ON l.listing_id = r.listing_id
             JOIN users u ON u.user_id = r.reporter_id
             ORDER BY r.created_at DESC LIMIT 200'
        )->fetchAll();
    }

    public static function setStatus(int $id, string $status): void {
        $stmt = db()->prepare('UPDATE reports SET status = ? WHERE report_id = ?');
        $stmt->execute([$status, $id]);
    }

    public static function pendingCount(): int {
        return (int)db()->query('SELECT COUNT(*) FROM reports WHERE status = "pending"')->fetchColumn();
    }
}
