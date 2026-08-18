<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$type = $_POST['type'] ?? '';
$myId = $_SESSION['user_id'];

if ($type === 'request_action') {
    $requestId = $_POST['request_id'] ?? '';
    $action = $_POST['action'] ?? '';

    $stmt = $pdo->prepare("SELECT br.*, i.user_id AS owner_id FROM borrow_request br JOIN item i ON br.item_id = i.item_id WHERE br.request_id = ?");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($request && $request['owner_id'] === $myId && $request['status'] === 'pending') {
        if ($action === 'approve') {
            $pdo->prepare("UPDATE borrow_request SET status = 'confirmed' WHERE request_id = ?")->execute([$requestId]);
        } elseif ($action === 'deny') {
            $pdo->prepare("UPDATE borrow_request SET status = 'denied' WHERE request_id = ?")->execute([$requestId]);
        }
    }

} elseif ($type === 'confirm_pickup') {
    $requestId = $_POST['request_id'] ?? '';

    $stmt = $pdo->prepare("SELECT br.*, i.user_id AS owner_id FROM borrow_request br JOIN item i ON br.item_id = i.item_id WHERE br.request_id = ?");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($request && $request['owner_id'] === $myId && $request['status'] === 'confirmed') {
        $borrowId = "B" . substr(uniqid(), -4);
        $pdo->prepare("INSERT INTO borrowing (borrow_id, item_id, borrower_id, borrow_date, status) VALUES (?, ?, ?, CURDATE(), 'borrowed')")
            ->execute([$borrowId, $request['item_id'], $request['requester_id']]);

        $pdo->prepare("UPDATE item SET availability = 'no' WHERE item_id = ?")->execute([$request['item_id']]);
        $pdo->prepare("UPDATE borrow_request SET status = 'borrowed' WHERE request_id = ?")->execute([$requestId]);
    }

} elseif ($type === 'mark_returned') {
    $borrowId = $_POST['borrow_id'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM borrowing WHERE borrow_id = ? AND borrower_id = ?");
    $stmt->execute([$borrowId, $myId]);
    $borrow = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($borrow && $borrow['status'] === 'borrowed') {
        $pdo->prepare("UPDATE borrowing SET status = 'returned', return_date = CURDATE() WHERE borrow_id = ?")->execute([$borrowId]);
        $pdo->prepare("UPDATE item SET availability = 'yes' WHERE item_id = ?")->execute([$borrow['item_id']]);
    }

} elseif ($type === 'set_fine') {
    $borrowId = $_POST['borrow_id'] ?? '';
    $fineAmount = (int) ($_POST['fine_amount'] ?? 0);
    $fineNote = trim($_POST['fine_note'] ?? '');

    $stmt = $pdo->prepare("SELECT b.*, i.user_id AS owner_id FROM borrowing b JOIN item i ON b.item_id = i.item_id WHERE b.borrow_id = ?");
    $stmt->execute([$borrowId]);
    $borrow = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($borrow && $borrow['owner_id'] === $myId && $borrow['status'] === 'returned' && $fineAmount >= 0) {
        $pdo->prepare("UPDATE borrowing SET fine_amount = ?, fine_note = ? WHERE borrow_id = ?")->execute([$fineAmount, $fineNote, $borrowId]);
    }

} elseif ($type === 'archive_item') {
    $itemId = $_POST['item_id'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM item WHERE item_id = ? AND user_id = ?");
    $stmt->execute([$itemId, $myId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        $newState = ($item['archived'] === 'yes') ? 'no' : 'yes';
        $pdo->prepare("UPDATE item SET archived = ? WHERE item_id = ?")->execute([$newState, $itemId]);
    }

} elseif ($type === 'delete_item') {
    $itemId = $_POST['item_id'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM item WHERE item_id = ? AND user_id = ?");
    $stmt->execute([$itemId, $myId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        $activeCheck = $pdo->prepare("SELECT COUNT(*) FROM borrowing WHERE item_id = ? AND status = 'borrowed'");
        $activeCheck->execute([$itemId]);

        if ($activeCheck->fetchColumn() == 0) {
            $pdo->prepare("DELETE FROM image WHERE item_id = ?")->execute([$itemId]);
            $pdo->prepare("DELETE FROM review WHERE item_id = ?")->execute([$itemId]);
            $pdo->prepare("DELETE FROM borrow_request WHERE item_id = ?")->execute([$itemId]);
            $pdo->prepare("DELETE FROM borrowing WHERE item_id = ?")->execute([$itemId]);
            $pdo->prepare("DELETE FROM item WHERE item_id = ?")->execute([$itemId]);
        }
    }

} elseif ($type === 'send_message') {
    $receiverId = $_POST['receiver_id'] ?? '';
    $itemId = !empty($_POST['item_id']) ? $_POST['item_id'] : null;
    $content = trim($_POST['content'] ?? '');

    if (!empty($content) && !empty($receiverId)) {
        $messageId = "M" . substr(uniqid(), -5);
        $stmt = $pdo->prepare("INSERT INTO message (message_id, sender_id, receiver_id, item_id, content, sent_date, is_read) VALUES (?, ?, ?, ?, ?, NOW(), 'no')");
        $stmt->execute([$messageId, $myId, $receiverId, $itemId, $content]);
    }

    header("Location: messages.php?with=" . urlencode($receiverId));
    exit;
}

header("Location: dashboard.php");
exit;
?>