<?php
session_start();
require_once 'function.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log all incoming data
error_log("Review submission request received");
error_log("POST data: " . print_r($_POST, true));
error_log("Session data: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("Error: User not logged in");
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Error: Invalid request method - " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

// Get and validate input
$product_id = isset($_POST['product_id']) ? trim($_POST['product_id']) : null;
$user_id = $_SESSION['user_id'];
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : null;

// Log validated input
error_log("Validated input - Product ID: $product_id, User ID: $user_id, Rating: $rating, Comment: $comment");

// Validate input
if (!$product_id || !$rating || !$comment) {
    error_log("Error: Missing required fields");
    echo json_encode(['status' => 'error', 'message' => 'Semua field harus diisi']);
    exit();
}

if ($rating < 1 || $rating > 5) {
    error_log("Error: Invalid rating value - $rating");
    echo json_encode(['status' => 'error', 'message' => 'Rating tidak valid']);
    exit();
}

try {
    // Get database connection from global PDO instance
    if (!isset($GLOBALS['db'])) {
        throw new Exception("Database connection not available");
    }
    $db = $GLOBALS['db'];
    error_log("Database connection successful");

    // Check if user has already reviewed this product
    $check_query = "SELECT reviews_id FROM reviews WHERE product_id = :product_id AND user_id = :user_id";
    error_log("Checking existing review with query: $check_query");

    $stmt = $db->prepare($check_query);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        error_log("Error: User has already reviewed this product");
        echo json_encode(['status' => 'error', 'message' => 'Anda sudah memberikan ulasan untuk produk ini']);
        exit();
    }

    // Insert new review
    $insert_query = "INSERT INTO reviews (product_id, user_id, rating, comment, created_at) VALUES (:product_id, :user_id, :rating, :comment, NOW())";
    error_log("Inserting review with query: $insert_query");

    $stmt = $db->prepare($insert_query);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
    $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);

    if ($stmt->execute()) {
        error_log("Review successfully inserted");
        echo json_encode(['status' => 'success', 'message' => 'Ulasan berhasil dikirim']);
    } else {
        throw new Exception("Failed to insert review");
    }
} catch (Exception $e) {
    error_log("Error in submit_review.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan saat mengirim ulasan',
        'debug_message' => $e->getMessage()
    ]);
}
