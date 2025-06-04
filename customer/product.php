<?php
session_start();

require '../config/connection.php';
require '../config/function.php';

// Inisialisasi variabel cartItems
$cartItems = [];

// Cek apakah pengguna sudah login
if (isset($_SESSION['user_id'])) {
    // Ambil item keranjang dari database
    $cartItems = getCartItems($_SESSION['user_id']);
}

// Ambil semua kategori
$query_kategori = "SELECT DISTINCT kategori FROM products";
$stmt_kategori = $GLOBALS['db']->prepare($query_kategori);
$stmt_kategori->execute();
$result_kategori = $stmt_kategori->fetchAll(PDO::FETCH_ASSOC);

// Ambil semua sub kategori
$query_subkategori = "SELECT DISTINCT sub_kategori FROM products WHERE sub_kategori IS NOT NULL AND sub_kategori != ''";
$stmt_subkategori = $GLOBALS['db']->prepare($query_subkategori);
$stmt_subkategori->execute();
$result_subkategori = $stmt_subkategori->fetchAll(PDO::FETCH_ASSOC);

// Filter produk berdasarkan kategori
$selected_kategori = $_GET['kategori'] ?? 'all';
$selected_subkategori = $_GET['sub_kategori'] ?? 'all';

if ($selected_kategori === 'all' && $selected_subkategori === 'all') {
    $query_produk = "SELECT * FROM products";
    $stmt_produk = $GLOBALS['db']->prepare($query_produk);
} elseif ($selected_kategori !== 'all' && $selected_subkategori === 'all') {
    $query_produk = "SELECT * FROM products WHERE kategori = :kategori";
    $stmt_produk = $GLOBALS['db']->prepare($query_produk);
    $stmt_produk->bindParam(':kategori', $selected_kategori);
} elseif ($selected_kategori === 'all' && $selected_subkategori !== 'all') {
    $query_produk = "SELECT * FROM products WHERE sub_kategori = :sub_kategori";
    $stmt_produk = $GLOBALS['db']->prepare($query_produk);
    $stmt_produk->bindParam(':sub_kategori', $selected_subkategori);
} else {
    $query_produk = "SELECT * FROM products WHERE kategori = :kategori AND sub_kategori = :sub_kategori";
    $stmt_produk = $GLOBALS['db']->prepare($query_produk);
    $stmt_produk->bindParam(':kategori', $selected_kategori);
    $stmt_produk->bindParam(':sub_kategori', $selected_subkategori);
}

$stmt_produk->execute();
$produk = $stmt_produk->fetchAll(PDO::FETCH_ASSOC);


// if ($selected_kategori === 'all') {
//     // Ambil semua produk jika tidak ada kategori yang dipilih
//     $query_produk = "SELECT product_id, nama_produk, deskripsi, harga_produk, gambar_satu, kategori FROM products";
//     $stmt_produk = $GLOBALS['db']->prepare($query_produk);
// } else {
//     // Ambil produk berdasarkan kategori yang dipilih
//     $query_produk = "SELECT product_id, nama_produk, deskripsi, harga_produk, gambar_satu, kategori 
//                      FROM products 
//                      WHERE kategori = :kategori";
//     $stmt_produk = $GLOBALS['db']->prepare($query_produk);
//     $stmt_produk->bindParam(':kategori', $selected_kategori);

// }


// // Ambil produk berdasarkan kategori yang dipilih
// $query_produk = "SELECT product_id, nama_produk, deskripsi, harga_produk, gambar_satu, sub_kategori 
//                  FROM products 
//                  WHERE sub_kategori = :sub_kategori";
// $stmt_produk = $GLOBALS['db']->prepare($query_produk);
// $stmt_produk->bindParam(':sub_kategori', $selected_subkategori);

$stmt_produk->execute();
$result_produk = $stmt_produk->fetchAll(PDO::FETCH_ASSOC);

// Live Search
if (isset($_POST['query'])) {
    $searchTerm = $_POST['query'];
    searchProducts($searchTerm);
}
?>

<!DOCTYPE html>
<html lang="en">

<head></head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Produk Jamu Madura</title>
<link rel="icon" href="../resources/img/icons/jamadas.jpg" type="image/png">
<link rel="stylesheet" href="../resources/css/navbar.css">
<link rel="stylesheet" href="../resources/css/dashboard.css">
<style>
    .product-container {
        display: flex;
        margin-top: 60px;
        padding: 20px;
        position: relative;
    }

    .sidebar-kategori {
        width: 250px;
        padding: 5px;
        border-radius: 5px;
        height: calc(100vh - 100px);
        position: fixed;
        left: 20px;
        z-index: 1;
    }

    .sidebar-kategori h3 {
        color: #333;
        margin-bottom: 20px;
        padding-top: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #77dd77;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .sidebar-kategori h3 i {
        color: #77dd77;
    }

    .kategori-dropdown {
        position: relative;
        margin-bottom: 20px;
    }

    .kategori-select {
        width: 100%;
        padding: 12px 15px;
        border: none;
        border-radius: 8px;
        background-color: transparent;
        font-size: 14px;
        color: #666;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2377dd77' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 15px;
        transition: all 0.3s ease;
    }

    .kategori-select:hover {
        color: #77dd77;
    }

    .kategori-select:focus {
        outline: none;
        color: #77dd77;
    }

    .kategori-select option {
        color: #666;
        background-color: #fff;
        padding: 10px;
    }

    .kategori-select option:hover {
        color: #77dd77;
    }

    .kategori-select option:checked {
        color: #77dd77;
        font-weight: 500;
    }

    .sub-kategori {
        margin-top: 20px;
        position: relative;
    }

    .sub-kategori-select {
        width: 100%;
        padding: 12px 15px;
        border: none;
        border-radius: 8px;
        background-color: transparent;
        font-size: 14px;
        color: #666;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2377dd77' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 15px;
        transition: all 0.3s ease;
    }

    .sub-kategori-select:hover {
        color: #77dd77;
    }

    .sub-kategori-select:focus {
        outline: none;
        color: #77dd77;
    }

    .sub-kategori-select option {
        color: #666;
        background-color: #fff;
        padding: 10px;
    }

    .sub-kategori-select option:hover {
        color: #77dd77;
    }

    .sub-kategori-select option:checked {
        color: #77dd77;
        font-weight: 500;
    }

    .reset-filter {
        margin-top: 20px;
        width: 100%;
        padding: 12px 15px;
        background-color: transparent;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        font-size: 14px;
        color: #333;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .reset-filter:hover {
        color: #dc3545;
    }

    .reset-filter i {
        color: #dc3545;
    }

    .reset-filter:hover i {
        color: #dc3545;
    }

    .product-content {
        flex: 1;
        max-width: 1200px;
        margin-left: 290px;
        padding: 0 20px;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }

    .detail-button {
        background-color: #77dd77 !important;
        color: white !important;
        text-decoration: none;
        padding: 5px;
        border-radius: 5px;
        transition: background-color 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    .detail-button:hover {
        background-color: #6eca6e !important;
    }

    .detail-button p {
        margin: 0;
        font-size: 14px;
    }

    .cart-icon {
        width: 16px;
        height: 16px;
    }

    @media (max-width: 1024px) {
        .product-container {
            flex-direction: column;
        }

        .sidebar-kategori {
            width: 100%;
            height: auto;
            position: relative;
            margin-bottom: 20px;
            left: 0;
        }

        .kategori-dropdown {
            margin-bottom: 15px;
        }

        .product-content {
            margin-left: 0;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .product-card {
            max-width: 100%;
        }
    }

    @media (max-width: 768px) {
        .product-content {
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .product-card {
            padding: 8px;
        }

        .product-card img {
            max-height: 160px;
        }

        .product-card .description {
            min-height: 80px;
            max-height: 80px;
            padding: 8px;
        }

        .product-card .description p {
            font-size: 11px;
        }

        .product-name {
            font-size: 13px;
        }

        .detail-button p {
            font-size: 12px;
        }
    }

    @media (max-width: 480px) {
        .product-content {
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .product-card {
            padding: 6px;
        }

        .product-card img {
            max-height: 140px;
        }

        .product-card .description {
            min-height: 70px;
            max-height: 70px;
            padding: 6px;
        }

        .product-card .description p {
            font-size: 10px;
        }

        .product-name {
            font-size: 12px;
        }

        .detail-button p {
            font-size: 11px;
        }
    }
</style>
<link rel="stylesheet" href="../resources/css/chat.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <?php include 'layout/cusmrLayout/navbar.php'; ?>
    </nav>
    <div id="navbarSearchResults" class="search-results"></div>

    <div class="product-container">
        <!-- Sidebar Kategori -->
        <div class="sidebar-kategori">
            <h3><i class="fas fa-filter"></i> Filter Produk</h3>
            
            <div class="kategori-dropdown">
                <select class="kategori-select" onchange="window.location.href=this.value">
                    <option value="?kategori=all&sub_kategori=<?= urlencode($selected_subkategori) ?>" 
                        <?= $selected_kategori === 'all' ? 'selected' : '' ?>>
                        Semua Kategori
                    </option>
                    <?php foreach ($result_kategori as $kategori): ?>
                        <option value="?kategori=<?= urlencode($kategori['kategori']) ?>&sub_kategori=<?= urlencode($selected_subkategori) ?>"
                            <?= $selected_kategori === $kategori['kategori'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kategori['kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <h3><i class="fas fa-tags"></i> Sub Kategori</h3>
            <div class="sub-kategori">
                <select class="sub-kategori-select" onchange="window.location.href=this.value">
                    <option value="?kategori=<?= urlencode($selected_kategori) ?>&sub_kategori=all"
                        <?= $selected_subkategori === 'all' ? 'selected' : '' ?>>
                        Semua Sub Kategori
                    </option>
                    <?php foreach ($result_subkategori as $sub): ?>
                        <option value="?kategori=<?= urlencode($selected_kategori) ?>&sub_kategori=<?= urlencode($sub['sub_kategori']) ?>"
                            <?= $selected_subkategori === $sub['sub_kategori'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sub['sub_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button class="reset-filter" onclick="window.location.href='?kategori=all&sub_kategori=all'">
                <i class="fas fa-sync-alt"></i>
                Reset Filter
            </button>
        </div>


        <!-- Items Product -->
        <div class="product-content">
            <?php if (empty($result_produk)): ?>
                <div class="no-products">
                    <p>Tidak ada produk dalam kategori ini.</p>
                </div>
            <?php else: ?>
                <?php foreach ($result_produk as $produk): ?>
                    <div class="product-card">
                        <img class="product" src="data:image/jpeg;base64,<?= base64_encode($produk['gambar_satu']) ?>"
                            alt="<?= htmlspecialchars($produk['nama_produk']) ?>">
                        <p class="product-name"><?= htmlspecialchars($produk['nama_produk']) ?></p>
                        <div class="description">
                            <h5>Deskripsi Produk</h5>
                            <p><?= htmlspecialchars($produk['deskripsi']) ?></p>
                        </div>
                        <p class="product-price">Rp. <?= number_format($produk['harga_produk'], 0, ',', '.') ?></p>
                        <a href="productdetail.php?id=<?= $produk['product_id'] ?>" class="detail-button">
                            <!-- <img class="cart-icon" src="../resources/img/icons/cart.png" alt=""> -->
                            <p>Lihat Detail</p>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <!-- Chatbot -->
<div class="chat-container" id="chatContainer">
    <button class="chat-button" id="chatButton">
        <img src="../resources/img/icons/chat.png" alt="Chat" width="30">
    </button>
    <div class="chat-popup" id="chatPopup">
        <div class="chat-header">
            <h3>Asisten Jamu Madura</h3>
            <button class="close-button" onclick="toggleChat()">&times;</button>
        </div>
        <div class="chat-messages" id="chatMessages">
            <div class="bot-message">Halo! Saya adalah asisten Jamu Madura. Apa yang ingin Anda ketahui tentang jamu kami?</div>
        </div>
        <div class="chat-input">
            <input type="text" id="messageInput" placeholder="Ketik pesan Anda...">
            <button onclick="sendMessage()">Kirim</button>
        </div>
    </div>
</div>

<style>
.chat-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
}

.chat-button {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: #77dd77;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.chat-popup {
    display: none;
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 300px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.chat-header {
    background: #77dd77;
    color: white;
    padding: 10px;
    border-radius: 10px 10px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-header h3 {
    margin: 0;
    font-size: 16px;
}

.close-button {
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
}

.chat-messages {
    height: 300px;
    padding: 10px;
    overflow-y: auto;
}

.chat-input {
    padding: 10px;
    border-top: 1px solid #eee;
    display: flex;
    gap: 5px;
}

.chat-input input {
    flex: 1;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.chat-input button {
    padding: 8px 15px;
    background: #77dd77;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.bot-message {
    background: #f1f1f1;
    padding: 10px;
    border-radius: 10px;
    margin-bottom: 10px;
}

.user-message {
    background: #e3f9e3;
    padding: 10px;
    border-radius: 10px;
    margin-bottom: 10px;
    text-align: right;
}
</style>

<script>
function toggleChat() {
    const popup = document.getElementById('chatPopup');
    popup.style.display = popup.style.display === 'none' ? 'block' : 'none';
}

function sendMessage() {
    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    if (!message) return;

    const chatMessages = document.getElementById('chatMessages');
    chatMessages.innerHTML += `<div class="user-message">${message}</div>`;
    input.value = '';

    // Kirim pesan ke server
    fetch('../config/process_chat.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `message=${encodeURIComponent(message)}`
    })
    .then(response => response.json())
    .then(data => {
        chatMessages.innerHTML += `<div class="bot-message">${data.response}</div>`;
        chatMessages.scrollTop = chatMessages.scrollHeight;
    })
    .catch(error => {
        console.error('Error:', error);
        chatMessages.innerHTML += `<div class="bot-message">Maaf, terjadi kesalahan. Silakan coba lagi.</div>`;
    });
}

document.getElementById('messageInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
});

document.getElementById('chatButton').addEventListener('click', toggleChat);
</script>
<script src="../resources/js/chat.js"></script>
</body>

</html>