<?php
require_once __DIR__ . '/../includes/functions.php';

class Review {
    public static function submit(int $transactionId, int $reviewerId, int $reviewedUserId, int $rating, ?string $feedback): bool {
        try {
            $stmt = db()->prepare(
                'INSERT INTO reviews (transaction_id, reviewer_id, reviewed_user_id, rating, feedback)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$transactionId, $reviewerId, $reviewedUserId, $rating, $feedback ?: null]);
            return true;
        } catch (PDOException $e) {
            return false; // duplicate review
        }
    }

    public static function hasReviewed(int $transactionId, int $reviewerId): bool {
        $stmt = db()->prepare('SELECT 1 FROM reviews WHERE transaction_id = ? AND reviewer_id = ?');
        $stmt->execute([$transactionId, $reviewerId]);
        return (bool)$stmt->fetchColumn();
    }

    public static function forUser(int $userId, int $limit = 20): array {
        $stmt = db()->prepare(
            'SELECT r.*, u.full_name AS reviewer_name, t.transaction_id
             FROM reviews r
             JOIN users u ON u.user_id = r.reviewer_id
             JOIN transactions t ON t.transaction_id = r.transaction_id
             WHERE r.reviewed_user_id = ?
             ORDER BY r.created_at DESC LIMIT ' . (int)$limit
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
