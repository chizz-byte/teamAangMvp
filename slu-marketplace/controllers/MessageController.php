<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/Listing.php';
require_once __DIR__ . '/../models/User.php';

class MessageController {
    // Validate that $me may message $peer about $listingId.
    // Allowed if a thread already exists, or if $peer is the listing's seller
    // and $me is not the seller (buyers may always initiate).
    private static function canMessage(int $me, int $peer, int $listingId): bool {
        if ($me === $peer) return false;
        if (!User::findById($peer)) return false;
        $listing = Listing::findById($listingId);
        if (!$listing || $listing['status'] === 'removed') return false;
        // Already part of this thread?
        if (Message::canAccess($me, $listingId, $peer)) return true;
        // Otherwise only the listing's seller can be contacted first.
        return (int)$listing['seller_id'] === $peer && (int)$listing['seller_id'] !== $me;
    }

    public static function send(): void {
        csrf_verify();
        $me = current_user();
        if (!$me) json_response(['ok' => false, 'error' => 'Login required'], 401);
        $listingId = (int)($_POST['listing_id'] ?? 0);
        $peerId = (int)($_POST['peer_id'] ?? 0);
        $content = trim((string)($_POST['content'] ?? ''));
        if ($content === '' || mb_strlen($content) > 2000) {
            json_response(['ok' => false, 'error' => 'Message must be 1-2000 characters']);
        }
        if (!self::canMessage((int)$me['user_id'], $peerId, $listingId)) {
            json_response(['ok' => false, 'error' => 'Not allowed'], 403);
        }
        $id = Message::send((int)$me['user_id'], $peerId, $listingId, $content);
        $msg = db()->prepare(
            'SELECT m.*, u.full_name AS sender_name FROM messages m
             JOIN users u ON u.user_id = m.sender_id WHERE m.message_id = ?'
        );
        $msg->execute([$id]);
        json_response(['ok' => true, 'message' => $msg->fetch()]);
    }

    public static function fetch(): void {
        $me = current_user();
        if (!$me) json_response(['ok' => false, 'error' => 'Login required'], 401);
        $listingId = (int)($_GET['listing_id'] ?? 0);
        $peerId = (int)($_GET['peer_id'] ?? 0);
        $after = (int)($_GET['after'] ?? 0);
        if (!self::canMessage((int)$me['user_id'], $peerId, $listingId)) {
            json_response(['ok' => false, 'error' => 'Not allowed'], 403);
        }
        $sql = 'SELECT m.*, u.full_name AS sender_name FROM messages m
                JOIN users u ON u.user_id = m.sender_id
                WHERE m.listing_id = ?
                  AND ((m.sender_id = ? AND m.receiver_id = ?)
                    OR (m.sender_id = ? AND m.receiver_id = ?))';
        $params = [$listingId, (int)$me['user_id'], $peerId, $peerId, (int)$me['user_id']];
        if ($after > 0) { $sql .= ' AND m.message_id > ?'; $params[] = $after; }
        $sql .= ' ORDER BY m.created_at ASC, m.message_id ASC';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $msgs = $stmt->fetchAll();
        json_response(['ok' => true, 'messages' => $msgs]);
    }
}
