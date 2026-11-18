<?php
require_once __DIR__ . '/../includes/functions.php';

class Message {
    // All messages between two users about a listing, chronological.
    public static function thread(int $listingId, int $userA, int $userB): array {
        $stmt = db()->prepare(
            'SELECT m.*, u.full_name AS sender_name
             FROM messages m JOIN users u ON u.user_id = m.sender_id
             WHERE m.listing_id = ?
               AND ((m.sender_id = ? AND m.receiver_id = ?)
                 OR (m.sender_id = ? AND m.receiver_id = ?))
             ORDER BY m.created_at ASC, m.message_id ASC'
        );
        $stmt->execute([$listingId, $userA, $userB, $userB, $userA]);
        return $stmt->fetchAll();
    }

    public static function send(int $senderId, int $receiverId, int $listingId, string $content): int {
        $stmt = db()->prepare(
            'INSERT INTO messages (sender_id, receiver_id, listing_id, content) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$senderId, $receiverId, $listingId, $content]);
        return (int)db()->lastInsertId();
    }

    // Conversation list for a user: one row per (listing, peer).
    public static function conversations(int $userId): array {
        $stmt = db()->prepare(
            'SELECT m.listing_id, l.title AS listing_title, l.image_path,
                    CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END AS peer_id,
                    u.full_name AS peer_name,
                    MAX(m.created_at) AS last_at,
                    SUBSTRING_INDEX(GROUP_CONCAT(m.content ORDER BY m.created_at DESC, m.message_id DESC SEPARATOR "||"), "||", 1) AS last_msg
             FROM messages m
             JOIN listings l ON l.listing_id = m.listing_id
             JOIN users u ON u.user_id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END
             WHERE m.sender_id = ? OR m.receiver_id = ?
             GROUP BY m.listing_id, peer_id, l.title, l.image_path, u.full_name
             ORDER BY last_at DESC'
        );
        $stmt->execute([$userId, $userId, $userId, $userId]);
        return $stmt->fetchAll();
    }

    public static function unreadCount(int $userId): int {
        $stmt = db()->prepare(
            'SELECT COUNT(*) FROM messages WHERE receiver_id = ?'
        );
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    // Check whether $userId participates in this listing thread with $peerId.
    public static function canAccess(int $userId, int $listingId, int $peerId): bool {
        $stmt = db()->prepare(
            'SELECT 1 FROM messages
             WHERE listing_id = ? AND ((sender_id = ? AND receiver_id = ?)
                OR (sender_id = ? AND receiver_id = ?)) LIMIT 1'
        );
        $stmt->execute([$listingId, $userId, $peerId, $peerId, $userId]);
        return (bool)$stmt->fetchColumn();
    }
}
