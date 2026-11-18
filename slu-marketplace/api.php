<?php
// AJAX endpoints. Each action loads its controller and dispatches.
require_once __DIR__ . '/includes/functions.php';

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'messages_send':
        require_once __DIR__ . '/controllers/MessageController.php';
        MessageController::send();
        break;
    case 'messages_fetch':
        require_once __DIR__ . '/controllers/MessageController.php';
        MessageController::fetch();
        break;
    case 'tx_propose':
        require_once __DIR__ . '/controllers/TransactionController.php';
        TransactionController::propose();
        break;
    case 'tx_update':
        require_once __DIR__ . '/controllers/TransactionController.php';
        TransactionController::update();
        break;
    case 'tx_complete':
        require_once __DIR__ . '/controllers/TransactionController.php';
        TransactionController::complete();
        break;
    case 'tx_cancel':
        require_once __DIR__ . '/controllers/TransactionController.php';
        TransactionController::cancel();
        break;
    default:
        json_response(['ok' => false, 'error' => 'Unknown action'], 404);
}
