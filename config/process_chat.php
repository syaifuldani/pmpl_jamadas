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
            'product_link' => 'productdetail.php?id=1',
            'kategori' => 'Kesehatan Umum & Imunitas',
            'sub_kategori' => 'Jamu Cair'
        ],        
        'pahitan' => [
            'keywords' => ['jerawat', 'kulit', 'darah', 'bau badan', 'keringat', 'kekebalan'],
            'response' => "Jamu Pahitan Madura adalah pilihan tepat! Terbuat dari sambiloto, brotowali, dan rempah lainnya. Sangat efektif untuk mengatasi masalah kulit, membersihkan darah, dan meningkatkan sistem kekebalan tubuh.",
            'product_id' => 2,
            'product_link' => 'productdetail.php?id=2',
            'kategori' => 'Kesehatan Umum & Imunitas',
            'sub_kategori' => 'Jamu Cair'
        ],
        'pengencang_payudara' => [
            'keywords' => ['payudara', 'kencang', 'kendor', 'melahirkan', 'menyusui'],
            'response' => "Jamu Pengencang Payudara adalah solusinya! Mengandung daun sirih, kunyit putih, dan bahan alami lainnya untuk mengencangkan dan menjaga kesehatan payudara secara alami.",
            'product_id' => 3,
            'product_link' => 'productdetail.php?id=3',
            'kategori' => 'Reproduksi Wanita',
            'sub_kategori' => 'Jamu Cair'
        ],
        'subur_kandungan' => [
            'keywords' => ['subur', 'haid', 'menstruasi', 'nyeri', 'reproduksi', 'kesuburan'],
            'response' => "Jamu Subur Kandungan sangat cocok untuk Anda! Dengan kandungan kunyit, kencur, dan rempah pilihan untuk menjaga kesuburan dan kesehatan reproduksi wanita.",
            'product_id' => 4,
            'product_link' => 'productdetail.php?id=4',
            'kategori' => 'Reproduksi Wanita',
            'sub_kategori' => 'Jamu Cair'
        ],        
        'tongkat_madura' => [
            'keywords' => ['keputihan', 'organ', 'intim', 'wanita', 'rapet'],
            'response' => "Jamu Tongkat Madura adalah pilihan terbaik! Terbuat dari akar kayu rapet dan bahan alami untuk menjaga kesehatan organ kewanitaan.",
            'product_id' => 5,
            'product_link' => 'productdetail.php?id=5',
            'kategori' => 'Reproduksi Wanita',
            'sub_kategori' => 'Jamu Cair'
        ],
        'empot_empot' => [
            'keywords' => ['stamina', 'vitalitas', 'lelah', 'lesu', 'gairah', 'pria'],
            'response' => "Jamu Empot-Empot sangat cocok untuk meningkatkan stamina! Mengandung jahe merah, ginseng Jawa, dan rempah pilihan untuk menjaga vitalitas pria.",
            'product_id' => 6,
            'product_link' => 'productdetail.php?id=6',
            'kategori' => 'Vitalitas Pria',
            'sub_kategori' => 'Jamu Cair'
        ],
        'kunyit_asam_kecantikan' => [
            'keywords' => ['cerah', 'kecantikan', 'jerawat', 'kulit', 'anti-inflamasi', 'pencernaan'],
            'response' => "Jamu Kunyit Asam Kecantikan sangat tepat! Diformulasikan khusus untuk mencerahkan kulit dan menjaga kesehatan dari dalam dengan kunyit dan asam jawa.",
            'product_id' => 59,
            'product_link' => 'productdetail.php?id=59',
            'kategori' => 'Perawatan Kecantikan dan Tubuh',
            'sub_kategori' => 'Jamu Cair'
        ],        
        'masker_bengkoang' => [
            'keywords' => ['putih', 'wajah', 'masker', 'halus', 'pori', 'sel mati'],
            'response' => "Masker Bengkoang Tradisional adalah pilihan tepat! Terbuat dari bengkoang alami untuk memutihkan dan menghaluskan kulit wajah secara natural.",
            'product_id' => 60,
            'product_link' => 'productdetail.php?id=60',
            'kategori' => 'Perawatan Kecantikan dan Tubuh',
            'sub_kategori' => 'Jamu Bubuk'
        ],
        'scrub_kopi' => [
            'keywords' => ['lulur', 'scrub', 'halus', 'kencang', 'tubuh', 'darah'],
            'response' => "Body Scrub Kopi Jawa cocok untuk Anda! Menggunakan kopi robusta untuk menghaluskan dan mengencangkan kulit tubuh secara alami.",
            'product_id' => 61,
            'product_link' => 'productdetail.php?id=61',
            'kategori' => 'Perawatan Kecantikan dan Tubuh',
            'sub_kategori' => 'Lulur'
        ],
        'galih_asem' => [
            'keywords' => ['keputihan', 'wanita', 'organ', 'kesehatan', 'reproduksi'],
            'response' => "Jamu Galih Asem adalah solusi terbaik! Khusus untuk mengatasi keputihan dan menjaga kesehatan reproduksi wanita dengan bahan alami.",
            'product_id' => 62,
            'product_link' => 'productdetail.php?id=62',
            'kategori' => 'Reproduksi Wanita',
            'sub_kategori' => 'Jamu Cair'
        ],        
        'sari_rapat' => [
            'keywords' => ['rapat', 'organ', 'wanita', 'bau', 'kewanitaan', 'ph'],
            'response' => "Sari Rapat Manjakani sangat cocok! Terbuat dari manjakani untuk merapatkan organ kewanitaan dan menjaga pH alami.",
            'product_id' => 63,
            'product_link' => 'productdetail.php?id=63',
            'kategori' => 'Reproduksi Wanita',
            'sub_kategori' => 'Kapsul'
        ],
        'pelancar_haid' => [
            'keywords' => ['haid', 'menstruasi', 'nyeri', 'siklus', 'teratur'],
            'response' => "Jamu Pelancar Haid adalah pilihan tepat! Dengan empon-empon dan herbal alami untuk melancarkan dan mengurangi nyeri haid.",
            'product_id' => 64,
            'product_link' => 'productdetail.php?id=64',
            'kategori' => 'Reproduksi Wanita',
            'sub_kategori' => 'Jamu Bubuk'
        ],
        'kuat_lelaki' => [
            'keywords' => ['stamina', 'pria', 'vitalitas', 'tenaga', 'daya tahan'],
            'response' => "Jamu Kuat Lelaki Purbalingga sangat cocok! Dengan pasak bumi dan jahe merah untuk meningkatkan stamina pria dewasa.",
            'product_id' => 65,
            'product_link' => 'productdetail.php?id=65',
            'kategori' => 'Vitalitas Pria',
            'sub_kategori' => 'Jamu Cair'
        ],        
        'tongkat_ali' => [
            'keywords' => ['libido', 'prostat', 'energi', 'pria', 'kapsul'],
            'response' => "Kapsul Tongkat Ali adalah pilihan terbaik! Suplemen herbal dengan tongkat ali dan ginseng untuk vitalitas pria.",
            'product_id' => 66,
            'product_link' => 'productdetail.php?id=66',
            'kategori' => 'Vitalitas Pria',
            'sub_kategori' => 'Kapsul'
        ],
        'temulawak' => [
            'keywords' => ['liver', 'hati', 'pencernaan', 'nafsu makan'],
            'response' => "Jamu Temulawak Asli sangat tepat! Untuk menjaga kesehatan liver dan melancarkan pencernaan dengan temulawak murni.",
            'product_id' => 67,
            'product_link' => 'productdetail.php?id=67',
            'kategori' => 'Kesehatan Pencernaan',
            'sub_kategori' => 'Jamu Cair'
        ],
        'kunir_asem' => [
            'keywords' => ['detoks', 'bab', 'usus', 'pencernaan', 'bubuk'],
            'response' => "Bubuk Kunir Asem Premium cocok untuk Anda! Jamu bubuk untuk detoksifikasi dan menjaga kesehatan pencernaan.",
            'product_id' => 68,
            'product_link' => 'productdetail.php?id=68',
            'kategori' => 'Kesehatan Pencernaan',
            'sub_kategori' => 'Jamu Bubuk'
        ],        
        'sambiloto' => [
            'keywords' => ['racun', 'gula darah', 'imunitas', 'detoks', 'diabetes'],
            'response' => "Kapsul Sambiloto Pahit adalah pilihan tepat! Untuk detoksifikasi dan menurunkan gula darah dengan sambiloto alami.",
            'product_id' => 69,
            'product_link' => 'productdetail.php?id=69',
            'kategori' => 'Kesehatan Pencernaan',
            'sub_kategori' => 'Kapsul'
        ],
        'beras_kencur' => [
            'keywords' => ['stamina', 'pegal', 'linu', 'tradisional', 'segar'],
            'response' => "Jamu Beras Kencur Segar sangat cocok! Minuman tradisional untuk menambah stamina dan meredakan pegal linu.",
            'product_id' => 70,
            'product_link' => 'productdetail.php?id=70',
            'kategori' => 'Kesehatan Umum & Imunitas',
            'sub_kategori' => 'Jamu Cair'
        ],
        'jahe_merah' => [
            'keywords' => ['hangat', 'masuk angin', 'imunitas', 'instant', 'flu'],
            'response' => "Wedang Jahe Merah Instant adalah pilihan terbaik! Untuk menghangatkan tubuh dan meningkatkan imunitas.",
            'product_id' => 71,
            'product_link' => 'productdetail.php?id=71',
            'kategori' => 'Kesehatan Umum & Imunitas',
            'sub_kategori' => 'Minuman Instant'
        ],        
        'kayu_putih' => [
            'keywords' => ['masuk angin', 'aromaterapi', 'relaksasi', 'minyak'],
            'response' => "Minyak Kayu Putih Murni sangat tepat! Untuk meredakan masuk angin dan aromaterapi relaksasi.",
            'product_id' => 72,
            'product_link' => 'productdetail.php?id=72',
            'kategori' => 'Kesehatan Umum & Imunitas',
            'sub_kategori' => 'Minyak Herbal'
        ],
        'pegal_linu' => [
            'keywords' => ['pegal', 'linu', 'sendi', 'nyeri', 'darah'],
            'response' => "Jamu Pegal Linu Tradisional cocok untuk Anda! Ramuan herbal untuk mengatasi pegal linu dan nyeri sendi.",
            'product_id' => 73,
            'product_link' => 'productdetail.php?id=73',
            'kategori' => 'Kesehatan Tulang & Sendi',
            'sub_kategori' => 'Jamu Cair'
        ],
        'balsem_herbal' => [
            'keywords' => ['nyeri', 'otot', 'pijat', 'sakit', 'gosok'],
            'response' => "Balsem Gosok Herbal adalah solusinya! Untuk meredakan nyeri otot dan melancarkan sirkulasi darah.",
            'product_id' => 74,
            'product_link' => 'productdetail.php?id=74',
            'kategori' => 'Kesehatan Tulang & Sendi',
            'sub_kategori' => 'Balsem'
        ],        
        'madu_anak' => [
            'keywords' => ['anak', 'nafsu makan', 'imunitas', 'pertumbuhan', 'madu'],
            'response' => "Madu Herbal Anak sangat cocok! Untuk meningkatkan nafsu makan dan daya tahan tubuh anak dengan madu murni.",
            'product_id' => 75,
            'product_link' => 'productdetail.php?id=75',
            'kategori' => 'Kesehatan Anak',
            'sub_kategori' => 'Madu Herbal'
        ],
        'susu_kunyit' => [
            'keywords' => ['anak', 'susu', 'imunitas', 'pencernaan', 'vitamin'],
            'response' => "Bubuk Kunyit Susu Anak adalah pilihan tepat! Minuman sehat untuk meningkatkan imunitas dan pencernaan anak.",
            'product_id' => 76,
            'product_link' => 'productdetail.php?id=76',
            'kategori' => 'Kesehatan Anak',
            'sub_kategori' => 'Minuman Bubuk'
        ],
        'sehat_lansia' => [
            'keywords' => ['lansia', 'jantung', 'kolesterol', 'ingat', 'usia'],
            'response' => "Jamu Sehat Lansia sangat cocok! Ramuan khusus untuk menjaga kesehatan jantung dan daya ingat di usia lanjut.",
            'product_id' => 77,
            'product_link' => 'productdetail.php?id=77',
            'kategori' => 'Kesehatan Lansia',
            'sub_kategori' => 'Kapsul'
        ],        
        'wedang_uwuh' => [
            'keywords' => ['hangat', 'lansia', 'darah', 'stamina', 'tradisional'],
            'response' => "Wedang Uwuh Klasik adalah pilihan terbaik! Minuman tradisional untuk kehangatan dan stamina lansia.",
            'product_id' => 78,
            'product_link' => 'productdetail.php?id=78',
            'kategori' => 'Kesehatan Lansia',
            'sub_kategori' => 'Minuman Tradisional'
        ],
        'lulur_boreh' => [
            'keywords' => ['lulur', 'halus', 'kulit mati', 'harum', 'tubuh'],
            'response' => "Lulur Boreh Tradisional sangat tepat! Lulur Jawa dengan rempah untuk menghaluskan dan mengharumkan tubuh.",
            'product_id' => 79,
            'product_link' => 'productdetail.php?id=79',
            'kategori' => 'Perawatan Kecantikan dan Tubuh',
            'sub_kategori' => 'Lulur'
        ],
        'sabun_noni' => [
            'keywords' => ['sabun', 'jerawat', 'cerah', 'penuaan', 'noni'],
            'response' => "Sabun Herbal Noni cocok untuk Anda! Sabun alami untuk mengatasi jerawat dan mencerahkan kulit.",
            'product_id' => 80,
            'product_link' => 'productdetail.php?id=80',
            'kategori' => 'Perawatan Kecantikan dan Tubuh',
            'sub_kategori' => 'Sabun Herbal'
        ],        
        'serum_centella' => [
            'keywords' => ['serum', 'sensitif', 'kemerahan', 'lembab', 'wajah'],
            'response' => "Serum Wajah Centella adalah solusinya! Untuk menenangkan kulit sensitif dan mengurangi kemerahan.",
            'product_id' => 81,
            'product_link' => 'productdetail.php?id=81',
            'kategori' => 'Perawatan Kecantikan dan Tubuh',
            'sub_kategori' => 'Serum'
        ],
        'toner_sirih' => [
            'keywords' => ['toner', 'makeup', 'pori', 'segar', 'bersih'],
            'response' => "Toner Daun Sirih sangat cocok! Untuk membersihkan sisa makeup dan mengecilkan pori-pori wajah.",
            'product_id' => 82,
            'product_link' => 'productdetail.php?id=82',
            'kategori' => 'Perawatan Kecantikan dan Tubuh',
            'sub_kategori' => 'Toner'
        ],
        'kunir_putih' => [
            'keywords' => ['rahim', 'miom', 'kunyit putih', 'kesehatan', 'wanita'],
            'response' => "Jamu Kunir Putih adalah pilihan tepat! Khusus untuk kesehatan rahim dan mengatasi miom dengan kunyit putih.",
            'product_id' => 83,
            'product_link' => 'productdetail.php?id=83',
            'kategori' => 'Reproduksi Wanita',
            'sub_kategori' => 'Jamu Cair'
        ],        
        'pil_kesuburan' => [
            'keywords' => ['kesuburan', 'hormon', 'telur', 'hamil', 'katuk'],
            'response' => "Pil Herbal Kesuburan sangat cocok! Untuk meningkatkan kesuburan dan menyeimbangkan hormon wanita.",
            'product_id' => 84,
            'product_link' => 'productdetail.php?id=84',
            'kategori' => 'Reproduksi Wanita',
            'sub_kategori' => 'Pil Herbal'
        ],
        'herbal_hamil' => [
            'keywords' => ['hamil', 'asi', 'mual', 'kandungan', 'menyusui'],
            'response' => "Minuman Herbal Ibu Hamil adalah pilihan terbaik! Aman untuk ibu hamil dan menyusui, melancarkan ASI.",
            'product_id' => 85,
            'product_link' => 'productdetail.php?id=85',
            'kategori' => 'Reproduksi Wanita',
            'sub_kategori' => 'Minuman Herbal'
        ],
        'akar_fatimah' => [
            'keywords' => ['libido', 'stamina', 'prostat', 'fatimah', 'pria'],
            'response' => "Jamu Akar Fatimah Pria sangat tepat! Untuk meningkatkan libido dan menjaga kesehatan prostat pria.",
            'product_id' => 86,
            'product_link' => 'productdetail.php?id=86',
            'kategori' => 'Vitalitas Pria',
            'sub_kategori' => 'Jamu Cair'
        ],        
        'minyak_urut' => [
            'keywords' => ['urut', 'peredaran', 'pegal', 'minyak', 'pria'],
            'response' => "Minyak Urut Pria cocok untuk Anda! Minyak herbal untuk melancarkan peredaran darah dan meredakan pegal.",
            'product_id' => 87,
            'product_link' => 'productdetail.php?id=87',
            'kategori' => 'Vitalitas Pria',
            'sub_kategori' => 'Minyak Herbal'
        ],
        'teh_stamina' => [
            'keywords' => ['energi', 'vitalitas', 'lelah', 'teh', 'ginseng'],
            'response' => "Teh Herbal Stamina adalah pilihan tepat! Untuk meningkatkan energi dan mengurangi kelelahan pria.",
            'product_id' => 88,
            'product_link' => 'productdetail.php?id=88',
            'kategori' => 'Vitalitas Pria',
            'sub_kategori' => 'Teh Herbal'
        ],
        'lidah_buaya' => [
            'keywords' => ['bab', 'detoks', 'usus', 'maag', 'lidah buaya'],
            'response' => "Jamu Lidah Buaya sangat cocok! Untuk melancarkan BAB dan detoksifikasi usus secara alami.",
            'product_id' => 89,
            'product_link' => 'productdetail.php?id=89',
            'kategori' => 'Kesehatan Pencernaan',
            'sub_kategori' => 'Jamu Cair'
        ],        
        'chia_seed' => [
            'keywords' => ['kolesterol', 'gula darah', 'chia', 'fiber', 'pencernaan'],
            'response' => "Bubuk Chia Seed Herbal adalah pilihan terbaik! Untuk melancarkan pencernaan dan mengontrol kolesterol.",
            'product_id' => 90,
            'product_link' => 'productdetail.php?id=90',
            'kategori' => 'Kesehatan Pencernaan',
            'sub_kategori' => 'Suplemen Herbal'
        ],        
        'probiotik' => [
            'keywords' => ['flora', 'usus', 'probiotik', 'pencernaan', 'bakteri'],
            'response' => "Kapsul Probiotik Herbal sangat tepat! Untuk menjaga flora usus dan meningkatkan pencernaan.",
            'product_id' => 91,
            'product_link' => 'productdetail.php?id=91',
            'kategori' => 'Kesehatan Pencernaan',
            'sub_kategori' => 'Kapsul'
        ],       
        'sirup_imun' => [
            'keywords' => ['imunitas', 'flu', 'sembuh', 'keluarga', 'daya tahan'],
            'response' => "Sirup Herbal Imunitas cocok untuk Anda! Untuk meningkatkan daya tahan tubuh keluarga dan mencegah flu.",
            'product_id' => 92,
            'product_link' => 'productdetail.php?id=92',
            'kategori' => 'Kesehatan Umum & Imunitas',
            'sub_kategori' => 'Sirup Herbal'
        ],        
        'permen_tenggorokan' => [
            'keywords' => ['batuk', 'tenggorokan', 'napas', 'permen', 'sakit'],
            'response' => "Permen Herbal Tenggorokan adalah solusinya! Untuk meredakan batuk dan melembabkan tenggorokan.",
            'product_id' => 93,
            'product_link' => 'productdetail.php?id=93',
            'kategori' => 'Kesehatan Umum & Imunitas',
            'sub_kategori' => 'Permen Herbal'
        ],        
        'inhaler' => [
            'keywords' => ['hidung', 'tersumbat', 'sinusitis', 'napas', 'inhaler'],
            'response' => "Inhaler Herbal Alami sangat cocok! Untuk melegakan hidung tersumbat dan meredakan sinusitis.",
            'product_id' => 94,
            'product_link' => 'productdetail.php?id=94',
            'kategori' => 'Kesehatan Umum & Imunitas',
            'sub_kategori' => 'Inhaler'
        ],        
        'krim_sendi' => [
            'keywords' => ['sendi', 'peradangan', 'krim', 'nyeri', 'bengkak'],
            'response' => "Krim Nyeri Sendi adalah pilihan tepat! Untuk meredakan nyeri sendi dan mengurangi peradangan.",
            'product_id' => 95,
            'product_link' => 'productdetail.php?id=95',
            'kategori' => 'Kesehatan Tulang & Sendi',
            'sub_kategori' => 'Krim'
        ],        
        'patch_nyeri' => [
            'keywords' => ['koyo', 'otot', 'tahan lama', 'patch', 'sakit'],
            'response' => "Patch Herbal Nyeri sangat cocok! Koyo herbal tahan lama untuk meredakan nyeri otot dan sendi.",
            'product_id' => 96,
            'product_link' => 'productdetail.php?id=96',
            'kategori' => 'Kesehatan Tulang & Sendi',
            'sub_kategori' => 'Koyo Herbal'
        ],        
        'kalsium' => [
            'keywords' => ['tulang', 'osteoporosis', 'kalsium', 'kepadatan', 'vitamin d'],
            'response' => "Suplemen Kalsium Herbal adalah pilihan terbaik! Untuk menguatkan tulang dan mencegah osteoporosis.",
            'product_id' => 97,
            'product_link' => 'productdetail.php?id=97',
            'kategori' => 'Kesehatan Tulang & Sendi',
            'sub_kategori' => 'Suplemen Herbal'
        ],        
        'sirup_batuk_anak' => [
            'keywords' => ['batuk', 'anak', 'tenggorokan', 'sirup', 'aman'],
            'response' => "Sirup Batuk Anak Herbal sangat tepat! Sirup herbal aman untuk meredakan batuk anak-anak.",
            'product_id' => 98,
            'product_link' => 'productdetail.php?id=98',
            'kategori' => 'Kesehatan Anak',
            'sub_kategori' => 'Sirup Herbal'
        ],        
        'vitamin_gummy' => [
            'keywords' => ['vitamin', 'gummy', 'nutrisi', 'anak', 'pertumbuhan'],
            'response' => "Vitamin Gummy Anak cocok untuk Anda! Vitamin rasa buah untuk melengkapi nutrisi dan mendukung pertumbuhan anak.",
            'product_id' => 99,
            'product_link' => 'productdetail.php?id=99',
            'kategori' => 'Kesehatan Anak',
            'sub_kategori' => 'Vitamin'
        ],        
        'telon' => [
            'keywords' => ['bayi', 'telon', 'hangat', 'masuk angin', 'perut'],
            'response' => "Minyak Telon Herbal adalah pilihan tepat! Untuk menghangatkan tubuh bayi dan mencegah masuk angin.",
            'product_id' => 100,
            'product_link' => 'productdetail.php?id=100',
            'kategori' => 'Kesehatan Anak',
            'sub_kategori' => 'Minyak Herbal'
        ],        
        'teh_kolesterol' => [
            'keywords' => ['kolesterol', 'jantung', 'teh', 'darah', 'lansia'],
            'response' => "Teh Herbal Kolesterol sangat cocok! Untuk menurunkan kolesterol dan menjaga kesehatan jantung lansia.",
            'product_id' => 101,
            'product_link' => 'productdetail.php?id=101',
            'kategori' => 'Kesehatan Jantung',
            'sub_kategori' => 'Teh Herbal'
        ],        
        'ginkgo' => [
            'keywords' => ['ingat', 'otak', 'pikun', 'ginkgo', 'sirkulasi'],
            'response' => "Kapsul Ginkgo Senior adalah pilihan terbaik! Untuk meningkatkan daya ingat dan melancarkan sirkulasi ke otak.",
            'product_id' => 102,
            'product_link' => 'productdetail.php?id=102',
            'kategori' => 'Kesehatan Lansia',
            'sub_kategori' => 'Kapsul'
        ],        
        'hipertensi' => [
            'keywords' => ['tekanan darah', 'hipertensi', 'stress', 'tinggi', 'sirsak'],
            'response' => "Minuman Herbal Hipertensi sangat tepat! Untuk menurunkan tekanan darah tinggi dan mengurangi stress.",
            'product_id' => 103,
            'product_link' => 'productdetail.php?id=103',
            'kategori' => 'Kesehatan Jantung',
            'sub_kategori' => 'Minuman Herbal'
        ],        
        'bilberry_mata' => [
            'keywords' => ['mata', 'lelah', 'penglihatan', 'bilberry', 'lutein'],
            'response' => "Kapsul Bilberry Mata cocok untuk Anda! Untuk menjaga kesehatan mata dan mengurangi mata lelah.",
            'product_id' => 104,
            'product_link' => 'productdetail.php?id=104',
            'kategori' => 'Kesehatan Mata',
            'sub_kategori' => 'Kapsul'
        ],        
        'tetes_mata' => [
            'keywords' => ['mata kering', 'iritasi', 'tetes', 'lembab', 'cornflower'],
            'response' => "Tetes Mata Herbal adalah solusinya! Untuk melembabkan mata kering dan meredakan iritasi mata.",
            'product_id' => 105,
            'product_link' => 'productdetail.php?id=105',
            'kategori' => 'Kesehatan Mata',
            'sub_kategori' => 'Tetes Mata'
        ],        
        'omega3' => [
            'keywords' => ['jantung', 'omega', 'kolesterol', 'fish oil', 'bawang putih'],
            'response' => "Suplemen Omega-3 Herbal sangat cocok! Untuk menjaga kesehatan jantung dan menurunkan kolesterol.",
            'product_id' => 106,
            'product_link' => 'productdetail.php?id=106',
            'kategori' => 'Kesehatan Jantung',
            'sub_kategori' => 'Suplemen Herbal'
        ],        
        'teh_jantung' => [
            'keywords' => ['jantung', 'sirkulasi', 'tekanan', 'hawthorn', 'hibiscus'],
            'response' => "Teh Herbal Jantung adalah pilihan tepat! Untuk menjaga kesehatan jantung dan melancarkan sirkulasi.",
            'product_id' => 107,
            'product_link' => 'productdetail.php?id=107',
            'kategori' => 'Kesehatan Jantung',
            'sub_kategori' => 'Teh Herbal'
        ],        
        'aromaterapi' => [
            'keywords' => ['stress', 'tidur', 'relaksasi', 'lavender', 'tenang'],
            'response' => "Aromaterapi Lavender sangat tepat! Minyak aromaterapi untuk mengurangi stress dan membantu tidur nyenyak.",
            'product_id' => 108,
            'product_link' => 'productdetail.php?id=108',
            'kategori' => 'Kesehatan Mental & Relaksasi',
            'sub_kategori' => 'Aromaterapi'
        ],        
        'teh_stress' => [
            'keywords' => ['stress', 'cemas', 'pikiran', 'tidur', 'chamomile'],
            'response' => "Teh Herbal Anti Stress cocok untuk Anda! Untuk mengurangi stress dan meningkatkan kualitas tidur.",
            'product_id' => 109,
            'product_link' => 'productdetail.php?id=109',
            'kategori' => 'Kesehatan Mental & Relaksasi',
            'sub_kategori' => 'Teh Herbal'
        ]
    ];    // Mapping untuk pencarian kategori dan subkategori
    $category_mapping = [
        // Kategori Kecantikan
        'kecantikan' => 'Perawatan Kecantikan dan Tubuh',
        'wajah' => 'Perawatan Kecantikan dan Tubuh',
        'kulit' => 'Perawatan Kecantikan dan Tubuh',
        'perawatan' => 'Perawatan Kecantikan dan Tubuh',
        'kecantikan tubuh' => 'Perawatan Kecantikan dan Tubuh',
        'skincare' => 'Perawatan Kecantikan dan Tubuh',
        
        // Kategori Reproduksi Wanita
        'kewanitaan' => 'Reproduksi Wanita', 
        'keputihan' => 'Reproduksi Wanita',
        'haid' => 'Reproduksi Wanita',
        'wanita' => 'Reproduksi Wanita',
        'reproduksi' => 'Reproduksi Wanita',
        'hamil' => 'Reproduksi Wanita',
        'kesuburan' => 'Reproduksi Wanita',
        
        // Kategori Vitalitas Pria
        'pria' => 'Vitalitas Pria',
        'stamina' => 'Vitalitas Pria',
        'vitalitas' => 'Vitalitas Pria',
        'lelaki' => 'Vitalitas Pria',
        
        // Kategori Pencernaan
        'pencernaan' => 'Kesehatan Pencernaan',
        'perut' => 'Kesehatan Pencernaan',
        'maag' => 'Kesehatan Pencernaan',
        'usus' => 'Kesehatan Pencernaan',
        
        // Kategori Umum & Imunitas
        'imunitas' => 'Kesehatan Umum & Imunitas',
        'daya tahan' => 'Kesehatan Umum & Imunitas',
        'immune' => 'Kesehatan Umum & Imunitas',
        'kekebalan' => 'Kesehatan Umum & Imunitas',
        'flu' => 'Kesehatan Umum & Imunitas',
        'batuk' => 'Kesehatan Umum & Imunitas',
        
        // Kategori Tulang & Sendi
        'sendi' => 'Kesehatan Tulang & Sendi',
        'tulang' => 'Kesehatan Tulang & Sendi',
        'pegal' => 'Kesehatan Tulang & Sendi',
        'linu' => 'Kesehatan Tulang & Sendi',
        'nyeri' => 'Kesehatan Tulang & Sendi',
        
        // Kategori Anak
        'anak' => 'Kesehatan Anak',
        'anak-anak' => 'Kesehatan Anak',
        'balita' => 'Kesehatan Anak',
        'bayi' => 'Kesehatan Anak',
        
        // Kategori Lansia
        'lansia' => 'Kesehatan Lansia',
        'tua' => 'Kesehatan Lansia',
        'manula' => 'Kesehatan Lansia',
        'ingatan' => 'Kesehatan Lansia',
        
        // Kategori Mata
        'mata' => 'Kesehatan Mata',
        'penglihatan' => 'Kesehatan Mata',
        'rabun' => 'Kesehatan Mata',
        
        // Kategori Jantung
        'jantung' => 'Kesehatan Jantung',
        'kolesterol' => 'Kesehatan Jantung',
        'darah tinggi' => 'Kesehatan Jantung',
        'hipertensi' => 'Kesehatan Jantung',
        
        // Kategori Mental & Relaksasi
        'stress' => 'Kesehatan Mental & Relaksasi',
        'relaksasi' => 'Kesehatan Mental & Relaksasi',
        'tidur' => 'Kesehatan Mental & Relaksasi',
        'cemas' => 'Kesehatan Mental & Relaksasi',
        'gelisah' => 'Kesehatan Mental & Relaksasi',
        'mental' => 'Kesehatan Mental & Relaksasi'
    ];    // Cek apakah user ingin melihat semua kategori
    $show_all_categories = false;
    $category_search = null;
    $subcategory_search = null;
    
    // Deteksi permintaan untuk melihat semua kategori
    if (stripos($pesan, 'kategori') !== false && 
        (stripos($pesan, 'semua') !== false || stripos($pesan, 'apa saja') !== false || 
         stripos($pesan, 'daftar') !== false || stripos($pesan, 'list') !== false || 
         stripos($pesan, 'lihat') !== false)) {
        $show_all_categories = true;
    }
    // Jika tidak, cek apakah pencarian berdasarkan kategori spesifik
    else {
        foreach ($category_mapping as $key => $category) {
            if (stripos($pesan, $key) !== false) {
                $category_search = $category;
                break;
            }
        }
        
        // Cek pencarian berdasarkan subkategori
        $subcategory_mapping = [
            'jamu cair' => 'Jamu Cair',
            'jamu bubuk' => 'Jamu Bubuk',
            'kapsul' => 'Kapsul',
            'teh herbal' => 'Teh Herbal',
            'minyak herbal' => 'Minyak Herbal',
            'sirup herbal' => 'Sirup Herbal',
            'minuman instan' => 'Minuman Instant',
            'lulur' => 'Lulur',
            'balsem' => 'Balsem',
            'koyo herbal' => 'Koyo Herbal',
            'krim' => 'Krim',
            'vitamin' => 'Vitamin',
            'tetes mata' => 'Tetes Mata',
            'inhaler' => 'Inhaler',
            'suplemen herbal' => 'Suplemen Herbal',
            'aromaterapi' => 'Aromaterapi',
            'permen herbal' => 'Permen Herbal'
        ];
        
        foreach ($subcategory_mapping as $key => $subcategory) {
            if (stripos($pesan, $key) !== false) {
                $subcategory_search = $subcategory;
                break;
            }
        }
    }
      // Hitung probabilitas untuk setiap produk jamu
    $scores = [];
    $matching_products = [];
    $words = explode(' ', $pesan);
    
    foreach ($dataset as $product_name => $data) {
        // Filter berdasarkan kategori dan subkategori jika ada
        $skip_product = false;
        
        // Lewati jika pencarian kategori dan produk tidak dalam kategori tersebut
        if ($category_search && isset($data['kategori']) && $data['kategori'] !== $category_search) {
            $skip_product = true;
        }
        
        // Lewati jika pencarian subkategori dan produk tidak dalam subkategori tersebut
        if ($subcategory_search && isset($data['sub_kategori']) && $data['sub_kategori'] !== $subcategory_search) {
            $skip_product = true;
        }
        
        if ($skip_product) {
            continue;
        }
        
        $score = 0;
        $matched_keywords = [];
        
        foreach ($words as $word) {
            if (in_array($word, $data['keywords'])) {
                $score++;
                $matched_keywords[] = $word;
            }
        }
        
        if ($score > 0) {
            $scores[$product_name] = $score / count($words);
            $matching_products[$product_name] = [
                'score' => $scores[$product_name],
                'matched_keywords' => $matched_keywords,
                'data' => $data
            ];
        }
    }
    
    // Jika pencarian kategori atau subkategori dan tidak ada produk yang cocok, tampilkan semua produk dalam kategori/subkategori tersebut
    if (($category_search || $subcategory_search) && empty($matching_products)) {
        foreach ($dataset as $product_name => $data) {
            $matches_category = true;
            $matches_subcategory = true;
            
            // Cek kategori
            if ($category_search && isset($data['kategori']) && $data['kategori'] !== $category_search) {
                $matches_category = false;
            }
            
            // Cek subkategori
            if ($subcategory_search && isset($data['sub_kategori']) && $data['sub_kategori'] !== $subcategory_search) {
                $matches_subcategory = false;
            }
            
            if ($matches_category && $matches_subcategory) {
                $matching_products[$product_name] = [
                    'score' => 1, // Skor maksimum untuk kategori yang cocok
                    'matched_keywords' => [],
                    'data' => $data
                ];
            }
        }
    }// Siapkan respons berdasarkan permintaan user
    
    // Bila user ingin melihat semua kategori
    if ($show_all_categories) {
        $unique_categories = [];
        foreach ($dataset as $product) {
            if (isset($product['kategori'])) {
                $unique_categories[$product['kategori']] = true;
            }
        }
        
        $response = "<strong>Kami menyediakan produk jamu dalam berbagai kategori berikut:</strong><br><br>";
        
        $categories_description = [
            'Perawatan Kecantikan dan Tubuh' => "Jamu untuk menjaga kecantikan wajah, kulit, dan tubuh secara alami.",
            'Reproduksi Wanita' => "Jamu untuk kesehatan reproduksi dan kewanitaan.",
            'Vitalitas Pria' => "Jamu untuk meningkatkan stamina dan vitalitas pria.",
            'Kesehatan Pencernaan' => "Jamu untuk menjaga kesehatan sistem pencernaan.",
            'Kesehatan Umum & Imunitas' => "Jamu untuk meningkatkan daya tahan tubuh dan kesehatan secara umum.",
            'Kesehatan Tulang & Sendi' => "Jamu untuk menjaga kesehatan tulang dan mengatasi masalah sendi.",
            'Kesehatan Anak' => "Jamu dan produk herbal khusus untuk kesehatan anak-anak.",
            'Kesehatan Lansia' => "Jamu khusus untuk menjaga kesehatan dan vitalitas lansia.",
            'Kesehatan Mata' => "Produk herbal untuk menjaga kesehatan mata.",
            'Kesehatan Jantung' => "Jamu untuk menjaga kesehatan jantung dan sirkulasi darah.",
            'Kesehatan Mental & Relaksasi' => "Jamu untuk relaksasi dan kesehatan mental."
        ];
        
        foreach (array_keys($unique_categories) as $category) {
            $response .= "<strong>$category</strong><br>";
            if (isset($categories_description[$category])) {
                $response .= $categories_description[$category] . "<br>";
            }
            $response .= "<br>";
        }
        
        $response .= "Untuk melihat produk berdasarkan kategori, Anda bisa menanyakan misalnya: \"Tampilkan jamu untuk kesehatan mata\" atau \"Saya ingin produk jamu vitalitas pria\".<br><br>";
        
        $best_category = "";
    }
    // Selain itu, tampilkan produk yang cocok dengan pencarian
    else if (!empty($matching_products)) {
        // Urutkan produk berdasarkan skor tertinggi ke terendah
        uasort($matching_products, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        // Ambil produk dengan skor tertinggi untuk database
        reset($matching_products);
        $best_category = key($matching_products);
          // Tentukan header response berdasarkan pencarian kategori
        if ($category_search) {
            // Pesan header khusus untuk setiap kategori
            $category_headers = [
                'Perawatan Kecantikan dan Tubuh' => "Berikut rekomendasi produk jamu untuk kecantikan dan perawatan tubuh:<br><br>",
                'Reproduksi Wanita' => "Berikut rekomendasi produk jamu untuk kesehatan reproduksi wanita:<br><br>",
                'Vitalitas Pria' => "Berikut rekomendasi produk jamu untuk vitalitas dan stamina pria:<br><br>",
                'Kesehatan Pencernaan' => "Berikut rekomendasi produk jamu untuk kesehatan pencernaan:<br><br>",
                'Kesehatan Umum & Imunitas' => "Berikut rekomendasi produk jamu untuk meningkatkan daya tahan tubuh:<br><br>",
                'Kesehatan Tulang & Sendi' => "Berikut rekomendasi produk jamu untuk kesehatan tulang dan sendi:<br><br>",
                'Kesehatan Anak' => "Berikut rekomendasi produk jamu khusus untuk kesehatan anak:<br><br>",
                'Kesehatan Lansia' => "Berikut rekomendasi produk jamu untuk kesehatan lansia:<br><br>",
                'Kesehatan Mata' => "Berikut rekomendasi produk jamu untuk kesehatan mata:<br><br>",
                'Kesehatan Jantung' => "Berikut rekomendasi produk jamu untuk kesehatan jantung dan sirkulasi:<br><br>",
                'Kesehatan Mental & Relaksasi' => "Berikut rekomendasi produk jamu untuk relaksasi dan kesehatan mental:<br><br>"
            ];
            
            $response = isset($category_headers[$category_search]) ? $category_headers[$category_search] : "Berikut produk jamu yang sesuai dengan kategori {$category_search}:<br><br>";
            
            // Tambahkan informasi subkategori jika ada banyak produk
            if (count($matching_products) > 5) {
                $subcategories = [];
                foreach ($matching_products as $product) {
                    if (isset($product['data']['sub_kategori'])) {
                        $subcategories[$product['data']['sub_kategori']] = true;
                    }
                }
                
                if (count($subcategories) > 1) {
                    $response .= "<strong>Subkategori dalam {$category_search}:</strong> ";
                    $response .= implode(", ", array_keys($subcategories));
                    $response .= "<br><br>Anda dapat bertanya lebih spesifik, misalnya: \"Tampilkan jamu kapsul untuk {$category_search}\"<br><br>";
                }
            }
        } 
        else if ($subcategory_search) {
            $response = "Berikut produk jamu dalam bentuk {$subcategory_search} sesuai dengan pencarian Anda:<br><br>";
        } 
        else {
            $response = "Berikut beberapa produk jamu yang sesuai dengan pencarian Anda:<br><br>";
        }
        
        // Tambahkan produk yang cocok ke response
        foreach ($matching_products as $product_name => $product) {
            $product_link = $product['data']['product_link'];
            $display_name = ucwords(str_replace('_', ' ', $product_name));
            
            $response .= "<strong>{$display_name}</strong><br>";
            if (isset($product['data']['kategori']) && isset($product['data']['sub_kategori'])) {
                $response .= "<em>Kategori: {$product['data']['kategori']} | {$product['data']['sub_kategori']}</em><br>";
            }
            $response .= $product['data']['response'] . "<br>";
            $response .= "<a href='{$product_link}' class='jamu-link'>Lihat Detail Jamu</a><br><br>";
        }
    } else {
        $response = "Maaf, saya tidak dapat menemukan produk jamu yang sesuai dengan pencarian Anda. Silakan tanyakan tentang jamu untuk kesehatan, vitalitas, kecantikan, atau kewanitaan.<br><br>Anda juga dapat melihat daftar semua kategori dengan mengetik \"tampilkan semua kategori jamu\".";
        $best_category = "";
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