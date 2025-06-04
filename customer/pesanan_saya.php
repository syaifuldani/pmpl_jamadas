<?php

// Di PHP, sebelum memulai session
session_start([
    'cookie_secure' => true,
    'cookie_samesite' => 'Lax'
]);

// Security Headers
function setSecurityHeaders()
{
    // Protect against clickjacking
    header("X-Frame-Options: SAMEORIGIN");

    // Protect against XSS and other injections
    // Update Content Security Policy untuk mengizinkan cdnjs
    header("Content-Security-Policy: script-src 'self' https://*.midtrans.com https://cdnjs.cloudflare.com https://code.jquery.com 'unsafe-inline' 'unsafe-eval'");

    // Prevent MIME-type sniffing
    header("X-Content-Type-Options: nosniff");

    // Enable XSS protection
    header("X-XSS-Protection: 1; mode=block");

    // Use HTTPS only
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

    // Prevent browsers from sending referrer information
    header("Referrer-Policy: same-origin");
}

// Gunakan function di atas
setSecurityHeaders();

require_once '../config/function.php';
require_once '../config/midtrans_config.php';

// Memastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$userId = $_SESSION['user_id'];
$orders = getOrdersByID($userId);
$CheckTransactionPendingOver24Hours = CheckTransactionPendingOver24Hours($userId);
// var_dump($userId);
// var_dump($orders);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan</title>
    <link rel="icon" href="../resources/img/icons/jamadas2.png" type="image/png">
    <!-- // Cetak Note Script -->
    <!-- Ganti URL jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="../resources/css/cart.css">
    <link rel="stylesheet" href="../resources/css/navbar.css">
    <link rel="stylesheet" href="../resources/css/pesanan_saya.css">
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key=<?php $_ENV['MIDTRANS_CLIENT_KEY'] ?> crossorigin="anonymous" importance="high" async></script>
    <!-- Note: replace with src="https://app.midtrans.com/snap/snap.js" for Production environment -->
    <script src="../resources/js/alert-detailorder-admin.js"></script>
</head>

<body>

    <div class="container">

        <nav class="navbar">
            <?php include 'layout/cusmrLayout/navbar.php'; ?>
        </nav>

        <div id="alert-container"></div>

        <div class="pesanan-container">
            <h2>Pesanan Saya</h2>

            <!-- Tab Navigation -->
            <div class="status-tabs-container">
                <nav class="status-tabs" role="tablist">
                    <button class="tab-button" data-status="pending" role="tab">
                        Perlu Dibayar
                    </button>
                    <button class="tab-button" data-status="settlement" role="tab">
                        Dibayar
                    </button>
                    <button class="tab-button" data-status="processing" role="tab">
                        Dikemas
                    </button>
                    <button class="tab-button" data-status="shipped" role="tab">
                        Dikirim
                    </button>
                    <button class="tab-button" data-status="delivered" role="tab">
                        Selesai
                    </button>
                    <button class="tab-button" data-status="cancelled" role="tab">
                        Dibatalkan
                    </button>
                </nav>
            </div>

            <!-- Orders Container -->
            <div class="orders-container">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card" data-status="<?= $order['transaction_status'] ?>">
                        <div class="order-header">
                            <div class="order-date">
                                <i class="fas fa-calendar"></i>
                                <?= date('d F Y', strtotime($order['created_at'])) ?>
                            </div>
                            <div class="order-status <?= strtolower($order['transaction_status']) ?>">
                                <span
                                    class="order-status status-<?= !empty($order['transaction_status']) ? strtolower($order['transaction_status']) : 'pending' ?>">
                                    <?= getStatusLabel($order['transaction_status']) ?>
                                </span>
                            </div>
                        </div>

                        <div class="order-body">
                            <div class="order-items">
                                <?php if (isset($order['items']) && is_array($order['items'])): ?>
                                    <?php foreach ($order['items'] as $item): ?>
                                        <div class="item">
                                            <img src="<?= htmlspecialchars($item['gambar_satu']) ?>"
                                                alt="<?= htmlspecialchars($item['nama_produk']) ?>" class="item-image">
                                            <div class="item-details">
                                                <h4><?= htmlspecialchars($item['nama_produk']) ?></h4>
                                                <p><?= $item['jumlah_order'] ?> x Rp
                                                    <?= number_format($item['harga_order'], 0, ',', '.') ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>Tidak ada item dalam pesanan ini.</p>
                                <?php endif; ?>
                            </div>

                            <!-- Di bagian tampilan -->
                            <div class="order-info">
                                <div class="total-items">
                                    <?= isset($order['items']) && is_array($order['items']) ? count($order['items']) : 0 ?>
                                    Produk
                                </div>
                                <div class="total-price">
                                    Total Pesanan: <span>Rp
                                        <?= number_format($order['total_harga'] ?? 0, 0, ',', '.') ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="order-footer">
                            <?php if ($order['transaction_status'] == 'pending'): ?>
                                <button class="btn-pay" onclick="payOrder('<?= htmlspecialchars($order['order_id']) ?>')"
                                    data-order-id="<?= htmlspecialchars($order['order_id']) ?>" type="button">
                                    Bayar Sekarang
                                </button>
                            <?php elseif ($order['transaction_status'] == 'shipped'): ?>
                                <button class="btn-receive" onclick="return handleReceived(this, '<?= $order['order_id'] ?>')"
                                    data-order-id="<?= $order['order_id'] ?>" type="button">
                                    Pesanan Diterima
                                </button>
                            <?php endif; ?>
                            <?php if ($order['transaction_status'] == 'delivered'): ?>
                                <?php
                                // Check if user has already reviewed this product
                                $hasReviewed = false;
                                try {
                                    if (isset($GLOBALS['db'])) {
                                        $db = $GLOBALS['db'];
                                        $checkStmt = $db->prepare("SELECT reviews_id FROM reviews WHERE product_id = :product_id AND user_id = :user_id");
                                        $checkStmt->bindParam(':product_id', $order['items'][0]['product_id'], PDO::PARAM_INT);
                                        $checkStmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                                        $checkStmt->execute();
                                        $hasReviewed = $checkStmt->rowCount() > 0;
                                    }
                                } catch (Exception $e) {
                                    error_log("Error checking review status: " . $e->getMessage());
                                }

                                if ($hasReviewed): ?>
                                    <a href="productdetail.php?id=<?= htmlspecialchars($order['items'][0]['product_id']) ?>" class="btn-review view-review">
                                        Lihat Ulasan
                                    </a>
                                <?php else: ?>
                                    <button class="btn-review" onclick="openReviewModal('<?= $order['order_id'] ?>', '<?= $order['items'][0]['product_id'] ?>')" type="button">
                                        Berikan Ulasan
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                            <button class="btn-details"
                                onclick="viewOrderDetails('<?= htmlspecialchars($order['order_id']) ?>')">Lihat
                                Detail</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- Tambahkan di bagian akhir file sebelum closing body tag -->
        <div id="orderDetailModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <div class="modal-body">
                    <div id="orderDetailContent">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Modal -->
        <div id="reviewModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeReviewModal()">&times;</span>
                <div class="modal-body">
                    <h2>Berikan Ulasan</h2>
                    <form id="reviewForm" onsubmit="submitReview(event)">
                        <input type="hidden" id="reviewOrderId" name="order_id">
                        <input type="hidden" id="reviewProductId" name="product_id">

                        <div class="rating-input">
                            <label>Rating:</label>
                            <div class="stars">
                                <input type="radio" id="star5" name="rating" value="5" required>
                                <label for="star5">★</label>
                                <input type="radio" id="star4" name="rating" value="4">
                                <label for="star4">★</label>
                                <input type="radio" id="star3" name="rating" value="3">
                                <label for="star3">★</label>
                                <input type="radio" id="star2" name="rating" value="2">
                                <label for="star2">★</label>
                                <input type="radio" id="star1" name="rating" value="1">
                                <label for="star1">★</label>
                            </div>
                        </div>

                        <style>
                            .stars {
                                display: flex;
                                flex-direction: row;
                                /* Display stars from left to right */
                                gap: 2px;
                            }

                            .stars input {
                                display: none;
                            }

                            .stars label {
                                cursor: pointer;
                                font-size: 30px;
                                color: #ddd;
                                transition: color 0.2s;
                            }

                            /* Hover effect from left to right */
                            .stars label:hover,
                            .stars label:hover~label,
                            .stars input:checked~label {
                                color: #ffd700;
                            }

                            /* Ensure stars are filled from left to right */
                            .stars input:checked+label {
                                color: #ffd700;
                            }
                        </style>

                        <div class="review-text">
                            <label for="reviewComment">Ulasan Anda:</label>
                            <textarea name="comment" id="reviewComment" rows="4" required
                                placeholder="Bagikan pengalaman Anda dengan produk ini..."></textarea>
                        </div>

                        <button type="submit" class="submit-review">Kirim Ulasan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <script>
            showAlert('<?= $_SESSION['success'] ?>', 'success');
        </script>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <script>
            showAlert('<?= $_SESSION['error'] ?>', 'error');
        </script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <script>
        function handleReceived(button, orderId) {
            console.log('Handling order:', orderId);
            if (confirm('Apakah Anda yakin pesanan sudah diterima?')) {
                button.disabled = true;
                button.innerHTML = 'Memproses...';

                const formData = new FormData();
                formData.append('order_id', orderId);
                formData.append('status', 'delivered');

                fetch('../config/updateStatusAfterDelivered.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showAlert('Pesanan berhasil dikonfirmasi diterima', 'success');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            showAlert(data.message || 'Gagal mengupdate status', 'error');
                            button.disabled = false;
                            button.innerHTML = 'Pesanan Diterima';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('Terjadi kesalahan saat memproses permintaan', 'error');
                        button.disabled = false;
                        button.innerHTML = 'Pesanan Diterima';
                    });
            }
            return false;
        }

        // Review Modal Functions
        function openReviewModal(orderId, productId) {
            const modal = document.getElementById('reviewModal');
            document.getElementById('reviewOrderId').value = orderId;
            document.getElementById('reviewProductId').value = productId;
            modal.style.display = "block";
        }

        function closeReviewModal() {
            const modal = document.getElementById('reviewModal');
            modal.style.display = "none";
            document.getElementById('reviewForm').reset();
        }

        function submitReview(event) {
            event.preventDefault();

            const formData = new FormData(event.target);

            // Debug log the form data
            console.log('Submitting review with data:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            const submitButton = event.target.querySelector('.submit-review');
            const originalButtonText = submitButton.textContent;

            // Disable submit button and show loading state
            submitButton.disabled = true;
            submitButton.textContent = 'Mengirim...';

            fetch('../config/submit_review.php', {
                    method: 'POST',
                    body: formData
                })
                .then(async response => {
                    // Log the raw response
                    const responseText = await response.text();
                    console.log('Raw server response:', responseText);

                    // Try to parse as JSON
                    try {
                        return JSON.parse(responseText);
                    } catch (e) {
                        console.error('Failed to parse server response as JSON:', e);
                        throw new Error('Invalid server response: ' + responseText);
                    }
                })
                .then(data => {
                    console.log('Parsed server response:', data);

                    if (data.status === 'success') {
                        showAlert('Ulasan berhasil dikirim', 'success');
                        closeReviewModal();

                        // Change the review button to a link
                        const reviewBtn = document.querySelector(`button[onclick="openReviewModal('${formData.get('order_id')}', '${formData.get('product_id')}')"]`);
                        if (reviewBtn) {
                            const productId = formData.get('product_id');
                            const newLink = document.createElement('a');
                            newLink.href = `productdetail.php?id=${productId}`;
                            newLink.className = 'btn-review view-review';
                            newLink.textContent = 'Lihat Ulasan';
                            reviewBtn.parentNode.replaceChild(newLink, reviewBtn);
                        }
                    } else {
                        // Show the specific error message from the server
                        const errorMessage = data.message || data.debug_message || 'Gagal mengirim ulasan';
                        showAlert(errorMessage, 'error');
                        console.error('Server error:', data);
                    }
                })
                .catch(error => {
                    console.error('Error details:', error);
                    showAlert('Terjadi kesalahan saat mengirim ulasan: ' + error.message, 'error');
                })
                .finally(() => {
                    // Reset button state
                    submitButton.disabled = false;
                    submitButton.textContent = originalButtonText;
                });
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('reviewModal');
            if (event.target == modal) {
                closeReviewModal();
            }
        }
    </script>
    <!-- Tambahkan di bagian head -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="../resources/js/LihatDetailPesananCust.js"></script>
    <script src="../resources/js/ExistingOrder.js"></script>
    <script src="../resources/js/CetakNota.js"></script>

</body>

</html>