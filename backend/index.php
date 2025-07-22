<?php
// Start session
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Include database connection
require_once '../src/conn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit();
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

try {
    // Payout Balance (sum of completed transaction amounts)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(estimated_total), 0) as payout_balance FROM transactions WHERE user_id = ? AND state = 'completed'");
    $stmt->execute([$user_id]);
    $payout_balance = number_format($stmt->fetchColumn(), 4);

    // Total Transactions
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_count, COALESCE(SUM(estimated_total), 0) as total_amount FROM transactions WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_transactions = $stmt->fetch();
    $total_transaction_count = $total_transactions['total_count'];
    $total_transaction_amount = number_format($total_transactions['total_amount'], 4);

    // Completed Transactions
    $stmt = $pdo->prepare("SELECT COUNT(*) as completed_count FROM transactions WHERE user_id = ? AND state = 'completed'");
    $stmt->execute([$user_id]);
    $completed_transactions = $stmt->fetchColumn();

    // Pending Transactions
    $stmt = $pdo->prepare("SELECT COUNT(*) as pending_count, COALESCE(SUM(estimated_total), 0) as pending_amount FROM transactions WHERE user_id = ? AND state = 'pending'");
    $stmt->execute([$user_id]);
    $pending_transactions = $stmt->fetch();
    $pending_transaction_count = $pending_transactions['pending_count'];
    $pending_transaction_amount = number_format($pending_transactions['pending_amount'], 4);

    // Rejected Transactions
    $stmt = $pdo->prepare("SELECT COUNT(*) as rejected_count FROM transactions WHERE user_id = ? AND state = 'rejected'");
    $stmt->execute([$user_id]);
    $rejected_transactions = $stmt->fetchColumn();

    // Fetch transaction history
    $stmt = $pdo->prepare("SELECT id, state, role, coin, amount, estimated_total, payment_method, transaction_id FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $transactions = $stmt->fetchAll();

    // Return JSON response
    echo json_encode([
        'status' => 'success',
        'user_name' => $user_name,
        'payout_balance' => $payout_balance,
        'total_transaction_count' => $total_transaction_count,
        'total_transaction_amount' => $total_transaction_amount,
        'completed_transactions' => $completed_transactions,
        'pending_transaction_count' => $pending_transaction_count,
        'pending_transaction_amount' => $pending_transaction_amount,
        'rejected_transactions' => $rejected_transactions,
        'transactions' => $transactions
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>