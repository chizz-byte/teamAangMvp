<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../models/Listing.php';

class TransactionController {
    private const LOCATIONS = ['Main Campus', 'Maryheights Campus'];

    private static function participant(array $t, int $uid): bool {
        return (int)$t['buyer_id'] === $uid || (int)$t['seller_id'] === $uid;
    }

    // Buyer initiates: creates pending transaction, reserves listing.
    public static function propose(): void {
        csrf_verify();
        $me = current_user();
        if (!$me) json_response(['ok' => false, 'error' => 'Login required'], 401);
        $listingId = (int)($_POST['listing_id'] ?? 0);
        $listing = Listing::findById($listingId);
        if (!$listing || $listing['status'] !== 'available') {
            json_response(['ok' => false, 'error' => 'Listing is not available']);
        }
        if ((int)$listing['seller_id'] === (int)$me['user_id']) {
            json_response(['ok' => false, 'error' => 'You cannot buy your own listing']);
        }
        // One pending transaction per listing.
        if (Transaction::activeForListing($listingId)) {
            json_response(['ok' => false, 'error' => 'This listing already has a pending transaction']);
        }
        $price = $_POST['agreed_price'] ?? '';
        $price = ($price === '' || $price === null) ? null : (float)$price;
        if ($price !== null && ($price <= 0 || $price > 1000000)) {
            json_response(['ok' => false, 'error' => 'Invalid price']);
        }
        $loc = $_POST['meetup_location'] ?? '';
        $loc = in_array($loc, self::LOCATIONS, true) ? $loc : null;
        $id = Transaction::create($listingId, (int)$me['user_id'], (int)$listing['seller_id'], $price, $loc);
        Listing::setStatus($listingId, 'reserved');
        json_response(['ok' => true, 'transaction_id' => $id]);
    }

    // Either participant updates agreed price / meetup location while pending.
    public static function update(): void {
        csrf_verify();
        $me = current_user();
        if (!$me) json_response(['ok' => false, 'error' => 'Login required'], 401);
        $t = Transaction::findById((int)($_POST['transaction_id'] ?? 0));
        if (!$t || !self::participant($t, (int)$me['user_id']) || $t['status'] !== 'pending') {
            json_response(['ok' => false, 'error' => 'Not allowed'], 403);
        }
        $price = $_POST['agreed_price'] ?? '';
        $price = ($price === '' || $price === null) ? null : (float)$price;
        if ($price !== null && ($price <= 0 || $price > 1000000)) {
            json_response(['ok' => false, 'error' => 'Invalid price']);
        }
        $loc = $_POST['meetup_location'] ?? '';
        $loc = in_array($loc, self::LOCATIONS, true) ? $loc : null;
        Transaction::updateDetails((int)$t['transaction_id'], $price, $loc);
        json_response(['ok' => true]);
    }

    public static function complete(): void {
        csrf_verify();
        $me = current_user();
        if (!$me) json_response(['ok' => false, 'error' => 'Login required'], 401);
        $t = Transaction::findById((int)($_POST['transaction_id'] ?? 0));
        if (!$t || !self::participant($t, (int)$me['user_id']) || $t['status'] !== 'pending') {
            json_response(['ok' => false, 'error' => 'Not allowed'], 403);
        }
        Transaction::complete((int)$t['transaction_id']);
        json_response(['ok' => true]);
    }

    public static function cancel(): void {
        csrf_verify();
        $me = current_user();
        if (!$me) json_response(['ok' => false, 'error' => 'Login required'], 401);
        $t = Transaction::findById((int)($_POST['transaction_id'] ?? 0));
        if (!$t || !self::participant($t, (int)$me['user_id']) || $t['status'] !== 'pending') {
            json_response(['ok' => false, 'error' => 'Not allowed'], 403);
        }
        Transaction::cancel((int)$t['transaction_id']);
        json_response(['ok' => true]);
    }
}
