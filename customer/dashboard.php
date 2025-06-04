<?php
session_start();

require '../config/connection.php';
require '../config/function.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: index.php"); // Jika belum login, redirect ke halaman login
  exit();
}

// Inisialisasi variabel cartItems
$cartItems = [];

// Cek apakah pengguna sudah login
if (isset($_SESSION['user_id'])) {
  // Ambil item keranjang dari database
  $cartItems = getCartItems($_SESSION['user_id']);
}

// Live Search
if (isset($_POST['query'])) {
  $searchTerm = $_POST['query'];
  searchProducts($searchTerm);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link rel="icon" href="../resources/img/icons/jamadas2.png" type="image/png">
  <link rel="stylesheet" href="../resources/css/dashboard.css">
  <link rel="stylesheet" href="../resources/css/navbar.css">
  <link rel="stylesheet" href="../resources/css/chat.css">
</head>

<body>
  <div class="container" id="container">
    <!-- Navbar -->
    <nav class="navbar">
      <?php include 'layout/cusmrLayout/navbar.php'; ?>
    </nav>
    <!-- Menampilkan hasil pencarian -->
    <div id="navbarSearchResults" class="search-results"></div>

    <!-- Hero Section -->
    <section class="hero animate-slide-left">
      <h1 class="animate-fade-in animate-delay-1">Selamat datang di layanan Jamu Madura Online!</h1>
      <p class="animate-fade-in animate-delay-2">
        Kami menyediakan berbagai pilihan jamu Madura asli yang siap Anda pesan untuk menjaga kesehatan dan kebugaran Anda.
        Mulai dari jamu untuk vitalitas, kecantikan, hingga kesehatan harian, semua terbuat dari bahan alami pilihan khas Madura.
        Pilih produk favorit Anda, tambahkan ke keranjang, dan biarkan kami mengirimkannya langsung ke alamat Anda.
        Proses mudah, hasil menyehatkan!
      </p>
    </section>

    <!-- Search Section -->
    <div class="section-search">
      <section class="search animate-slide-right">
        <h2 class="animate-fade-in animate-delay-1">
          Temukan Beragam Jamu Madura Asli & Berkualitas
        </h2>
        <p class="animate-fade-in animate-delay-2">
          Pesan Sekarang, Jamu Madura Siap Dikirim ke Rumah Anda
        </p>
        <!-- <form action="" method="POST" class="search-input animate-slide-right animate-delay-3">
          <label><img src="../resources/img/icons/search.png" alt=""></label>
          <input type="text" id="contentSearchBox" name="query" placeholder="Cari Jamu Madura Anda"
            value="<?= isset($_POST['query']) ? htmlspecialchars($_POST['query']) : '' ?>">
        </form>
        <div id="contentSearchResults" class="search-results animate-slide-bottom animate-delay-4">
        </div> -->
      </section>
      <div class="image animate-slide-left animate-delay-3">
        <img src="../resources/img/introduction/image6.png" alt="Jamu Madura">
      </div>
    </div>

    <!-- Product Section -->
    <section class="products animate-slide-bottom">
      <h2 class="animate-fade-in animate-delay-1">
        Pesan Jamu Madura Asli dengan Mudah!
      </h2>
      <div class="product-grid">
        <div class="product-card-dsbrd animate-slide-top animate-delay-2">
          <a href="product.php?kategori=Perawatan+Kecantikan+dan+Tubuh&sub_kategori=all">
            <img src="../resources/img/homeimg/jamu_vitalitas.jpeg" alt="Jamu Vitalitas">
            <p>Jamu Vitalitas</p>
          </a>
        </div>
        <div class="product-card-dsbrd animate-slide-top animate-delay-3">
          <a href="product.php?kategori=Reproduksi+Wanita&sub_kategori=all">
            <img src="../resources/img/homeimg/jamu_kecantikan.jpg" alt="Reproduksi Wanita">
            <p>Reproduksi Wanita</p>
          </a>
        </div>
        <div class="product-card-dsbrd animate-slide-top animate-delay-4">
          <a href="product.php?kategori=Vitalitas+Pria&sub_kategori=all">
            <img src="../resources/img/homeimg/jamu_kewanitaan.jpg" alt="Vitalitas Pria">
            <p>Vitalitas Pria</p>
          </a>
        </div>
      </div>
    </section>

    <div class="layout-wrapper animate-slide-right animate-delay-3">
      <div class="instructions">
        <div class="step animate-slide-left animate-delay-1">
          <img src="../resources/img/icons/checkaction.png" alt="Pilih Jamu" class="icon">
          <div class="text">
            <h3>Pilih Jamu</h3>
            <p>Mulai pesanan Anda dengan memilih jamu Madura yang sesuai kebutuhan dari berbagai produk asli yang kami sediakan.</p>
          </div>
        </div>
        <div class="step animate-slide-left animate-delay-1">
          <img src="../resources/img/icons/cartaction.png" alt="Tambahkan ke Keranjang" class="icon">
          <div class="text">
            <h3>Tambahkan ke Keranjang</h3>
            <p>Isi detail pemesanan, seperti jumlah, alamat pengiriman, dan catatan khusus jika ada. Pastikan data Anda benar sebelum checkout.</p>
          </div>
        </div>
        <div class="step animate-slide-left animate-delay-1">
          <img src="../resources/img/icons/payaction.png" alt="Pilih Metode Pembayaran" class="icon">
          <div class="text">
            <h3>Pilih Metode Pembayaran</h3>
            <p>Pilih metode pembayaran yang Anda inginkan dan tunggu jamu Madura pesanan Anda sampai di rumah.</p>
          </div>
        </div>
      </div>
      <div class="preview slide-in-bottom">
        <img src="../resources/img/introduction/image2.png" alt="Preview Jamu Madura" class="preview-image">
      </div>
    </div>
  </div>

  <!-- Footers Promotions -->
  <footer class="footer animate-slide-top animate-delay-2">
    <?php include 'layout/cusmrLayout/footer.php'; ?>
  </footer>
  </div>
  <!-- Chatbot -->
  <div class="chat-toggle">
    <img src="https://cdn-icons-png.flaticon.com/512/4712/4712027.png" alt="Chat" width="30" height="30">
  </div>

  <div class="chat-container">
    <div class="chat-header">
      <img src="https://cdn-icons-png.flaticon.com/512/4712/4712027.png" alt="Bot Avatar">
      <h3>Asisten Jamu</h3>
    </div>
    <div class="chat-box"></div>
    <div class="chat-input">
      <input type="text" placeholder="Tanyakan tentang jamu..." id="chat-input">
      <button onclick="sendMessage()">Kirim</button>
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
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .chat-popup {
      display: none;
      position: absolute;
      bottom: 80px;
      right: 0;
      width: 300px;
      background: white;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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
      color: black;
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
      background: rgb(4, 255, 42);
      padding: 10px;
      border-radius: 10px;
      margin-bottom: 10px;
    }

    .user-message {
      background: rgb(0, 254, 0);
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
  <script src="../resources/js/burgersidebar.js"></script>
  <script src="../resources/js/livesearch.js"></script>
  <script src="../resources/js/chat.js"></script>
</body>