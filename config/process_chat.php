<?php
session_start();
require 'connection.php';

// Inisialisasi session chat jika belum ada
if (!isset($_SESSION['current_chat'])) {
    $_SESSION['current_chat'] = [];
}

// Ambil riwayat chat dari database saat halaman dimuat
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_SESSION['user_id'] ?? 0;
    $stmt = $GLOBALS['db']->prepare("SELECT pesan_pengguna, respons_jawaban FROM chats WHERE users_id = ? ORDER BY created_at ASC");
    $stmt->execute([$user_id]);
    $chat_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Simpan riwayat chat ke session
    $_SESSION['current_chat'] = $chat_history;
    
    echo json_encode(['history' => $chat_history]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $user_id = $_SESSION['user_id'] ?? 0;
    $pesan = $_POST['message'];
    
    // Preprocessing pesan
    $pesan = strtolower($pesan);
    $pesan = preg_replace('/[^a-z0-9\s]/', '', $pesan);
    
    // Dataset jamu dan kata kunci
    $dataset = [
        'kunyit_asam' => [
            'keywords' => ['sehat', 'metabolisme', 'detox', 'haid', 'bau badan', 'daya tahan', 'segar'],
            'response' => "Jamu Kunyit Asam Madura cocok untuk Anda! Jamu ini mengandung kunyit, asam jawa, dan bahan alami lainnya. Manfaatnya termasuk meningkatkan daya tahan tubuh, melancarkan haid, detoksifikasi, dan menyegarkan badan.",
            'product_id' => 1,
            'product_link' => 'productdetail.php?id=1'
        ],
        'pahitan' => [
            'keywords' => ['jerawat', 'kulit', 'darah', 'bau badan', 'keringat', 'kekebalan'],
            'response' => "Jamu Pahitan Madura adalah pilihan tepat! Terbuat dari sambiloto, brotowali, dan rempah lainnya. Sangat efektif untuk mengatasi masalah kulit, membersihkan darah, dan meningkatkan sistem kekebalan tubuh.",
            'product_id' => 2,
            'product_link' => 'productdetail.php?id=2'
        ],
        'pengencang_payudara' => [
            'keywords' => ['payudara', 'kencang', 'kendor', 'melahirkan', 'menyusui'],
            'response' => "Jamu Pengencang Payudara adalah solusinya! Mengandung daun sirih, kunyit putih, dan bahan alami lainnya untuk mengencangkan dan menjaga kesehatan payudara secara alami.",
            'product_id' => 3,
            'product_link' => 'productdetail.php?id=3'
        ],
        'subur_kandungan' => [
            'keywords' => ['subur', 'haid', 'menstruasi', 'nyeri', 'reproduksi', 'kesuburan'],
            'response' => "Jamu Subur Kandungan sangat cocok untuk Anda! Dengan kandungan kunyit, kencur, dan rempah pilihan untuk menjaga kesuburan dan kesehatan reproduksi wanita.",
            'product_id' => 4,
            'product_link' => 'productdetail.php?id=4'
        ],
        'tongkat_madura' => [
            'keywords' => ['keputihan', 'organ', 'intim', 'wanita', 'rapet'],
            'response' => "Jamu Tongkat Madura adalah pilihan terbaik! Terbuat dari akar kayu rapet dan bahan alami untuk menjaga kesehatan organ kewanitaan.",
            'product_id' => 5,
            'product_link' => 'productdetail.php?id=5'
        ],
        'empot_empot' => [
            'keywords' => ['stamina', 'vitalitas', 'lelah', 'lesu', 'gairah', 'pria'],
            'response' => "Jamu Empot-Empot sangat cocok untuk meningkatkan stamina! Mengandung jahe merah, ginseng Jawa, dan rempah pilihan untuk menjaga vitalitas pria.",
            'product_id' => 6,
            'product_link' => 'productdetail.php?id=6'
        ]
    ];
    
    // Hitung probabilitas untuk setiap kategori
    $scores = [];
    $words = explode(' ', $pesan);
    
    foreach ($dataset as $category => $data) {
        $score = 0;
        foreach ($words as $word) {
            if (in_array($word, $data['keywords'])) {
                $score++;
            }
        }
        $scores[$category] = $score / count($words);
    }
    
    // Pilih kategori dengan skor tertinggi
    $max_score = max($scores);
    $best_category = array_search($max_score, $scores);
    
    // Siapkan respons dengan link produk
    if ($max_score > 0) {
        $product_link = $dataset[$best_category]['product_link'];
        $response = $dataset[$best_category]['response'] . "\n\nKlik link berikut untuk melihat detail produk: <a href='" . $product_link . "' class='jamu-link'>Lihat Detail Jamu</a>";
    } else {
        $response = "Maaf, saya tidak dapat memahami pertanyaan Anda. Silakan tanyakan tentang jamu untuk kesehatan, vitalitas, kecantikan, atau kewanitaan.";
    }
    
    // Simpan chat ke database
    $stmt = $GLOBALS['db']->prepare("INSERT INTO chats (users_id, pesan_pengguna, respons_jawaban, kategori_jamu, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$user_id, $_POST['message'], $response, $best_category]);
    
    // Simpan chat ke session
    $_SESSION['current_chat'][] = [
        'pesan_pengguna' => $_POST['message'],
        'respons_jawaban' => $response
    ];
    
    echo json_encode(['response' => $response]);
    exit;
}