<?php
require_once __DIR__ . '/../includes/functions.php';

class Transaction {
    public static function findById(int $id): ?array {
        $stmt = db()->prepare(
            'SELECT t.*, l.title AS listing_title, l.image_path, l.status AS listing_status,
                    b.full_name AS buyer_name, s.full_name AS seller_name
             FROM transactions t
             JOIN listings l ON l.listing_id = t.listing_id
             JOIN users b ON b.user_id = t.buyer_id
             JOIN users s ON s.user_id = t.seller_id
             WHERE t.transaction_id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function forListingPair(int $listingId, int $buyerId, int $sellerId): ?array {
        $stmt = db()->prepare(
            'SELECT * FROM transactions WHERE listing_id = ? AND buyer_id = ? AND seller_id = ?
             ORDER BY transaction_id DESC LIMIT 1'
        );
        $stmt->execute([$listingId, $buyerId, $sellerId]);
        return $stmt->fetch() ?: null;
    }

    public static function activeForListing(int $listingId): ?array {
        $stmt = db()->prepare(
            'SELECT * FROM transactions WHERE listing_id = ? AND status = "pending"
             ORDER BY transaction_id DESC LIMIT 1'
        );
        $stmt->execute([$listingId]);
        return $stmt->fetch() ?: null;
    }

    public static function create(int $listingId, int $buyerId, int $sellerId, ?float $price = null, ?string $location = null): int {
        $stmt = db()->prepare(
            'INSERT INTO transactions (listing_id, buyer_id, seller_id, agreed_price, meetup_location, status)
             VALUES (?, ?, ?, ?, ?, "pending")'
        );
        $stmt->execute([$listingId, $buyerId, $sellerId, $price, $location]);
        return (int)db()->lastInsertId();
    }

    public static function updateDetails(int $id, ?float $price, ?string $location): void {
        $stmt = db()->prepare(
            'UPDATE transactions SET agreed_price = ?, meetup_location = ? WHERE transaction_id = ?'
        );
        $stmt->execute([$price, $location, $id]);
    }

    public static function complete(int $id): void {
        $t = self::findById($id);
        if (!$t) return;
        db()->prepare('UPDATE transactions SET status = "completed", completed_at = NOW() WHERE transaction_id = ?')
            ->execute([$id]);
        db()->prepare('UPDATE listings SET status = "sold" WHERE listing_id = ?')
            ->execute([$t['listing_id']]);
    }

    public static function cancel(int $id): void {
        $t = self::findById($id);
        if (!$t) return;
        db()->prepare('UPDATE transactions SET status = "cancelled" WHERE transaction_id = ?')
            ->execute([$id]);
        // Release the listing if no other pending transaction holds it.
        $stmt = db()->prepare(
            'SELECT transaction_id FROM transactions WHERE listing_id = ? AND status = "pending" LIMIT 1'
        );
        $stmt->execute([$t['listing_id']]);
        if (!$stmt->fetchColumn()) {
            db()->prepare('UPDATE listings SET status = "available" WHERE listing_id = ? AND status = "reserved"')
                ->execute([$t['listing_id']]);
        }
    }

    public static function forUser(int $userId): array {
        $stmt = db()->prepare(
            'SELECT t.*, l.title AS listing_title, l.image_path,
                    b.full_name AS buyer_name, s.full_name AS seller_name
             FROM transactions t
             JOIN listings l ON l.listing_id = t.listing_id
             JOIN users b ON b.user_id = t.buyer_id
             JOIN users s ON s.user_id = t.seller_id
             WHERE t.buyer_id = ? OR t.seller_id = ?
             ORDER BY t.created_at DESC'
        );
        $stmt->execute([$userId, $userId]);
        return $stmt->fetchAll();
    }
}
