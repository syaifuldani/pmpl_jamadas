<?php
// Include koneksi dan konfigurasi
require '../config/connection.php';

// Ambil data dari form
$bulan = isset($_POST['bulan']) ? $_POST['bulan'] : '';
$tahun = isset($_POST['tahun']) ? $_POST['tahun'] : '';

// Nama bulan dalam bahasa Indonesia
$nama_bulan = [
    '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

try {
    $db = $GLOBALS['db'];

    // Query data berdasarkan periode
    $query = "SELECT 
                order_id, 
                nama_penerima, 
                nomor_penerima, 
                alamat_penerima, 
                kota, 
                kodepos, 
                keterangan_order, 
                payment_type, 
                transaction_status, 
                total_harga, 
                transaction_time 
              FROM orders WHERE 1";

    $params = [];
    if ($bulan) {
        $query .= " AND MONTH(transaction_time) = ?";
        $params[] = $bulan;
    }
    if ($tahun) {
        $query .= " AND YEAR(transaction_time) = ?";
        $params[] = $tahun;
    }

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $periode = ($bulan ? $nama_bulan[$bulan] . ' ' : '') . ($tahun ?: 'Semua Tahun');

    // Fungsi untuk mengunduh Excel
    if (isset($_POST['download_excel'])) {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Laporan_Penjualan_' . $periode . '.xls"');
        header('Cache-Control: max-age=0');

        echo "Laporan Penjualan - " . $periode . "\n\n";
        echo "Order ID\tNama Penerima\tAlamat\tKota\tTotal Harga\tWaktu Transaksi\tStatus\n";

        foreach ($data as $order) {
            echo $order['order_id'] . "\t";
            echo $order['nama_penerima'] . "\t";
            echo $order['alamat_penerima'] . "\t";
            echo $order['kota'] . "\t";
            echo "Rp. " . number_format($order['total_harga'], 0, ',', '.') . "\t";
            echo date('d M Y H:i', strtotime($order['transaction_time'])) . "\t";
            echo $order['transaction_status'] . "\n";
        }
        exit;
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            padding: 20px;
        }

        .report-container {
            max-width: 1200px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            padding: 30px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .header .logo {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .header h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .header p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 5px;
        }

        .header .contact-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            color: #666;
            font-size: 14px;
            margin-top: 10px;
        }

        .header .contact-info i {
            color: #77dd77;
        }

        .table-container {
            margin-bottom: 30px;
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 20px;
        }

        .table th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: 600;
            font-size: 14px;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid #eee;
        }

        .table td {
            padding: 15px;
            font-size: 14px;
            color: #666;
            border-bottom: 1px solid #eee;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .status-badge.pending {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .status-badge.success {
            background-color: rgba(119, 221, 119, 0.1);
            color: #77dd77;
        }

        .status-badge.failed {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .download-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn-download {
            background-color: #77dd77;
            color: #ffffff;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-download.excel {
            background-color: #217346;
        }

        .btn-download.excel:hover {
            background-color: #1a5c38;
        }

        .btn-download.pdf:hover {
            background-color: #5cb85c;
        }

        .btn-download i {
            font-size: 16px;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }

            .report-container {
                box-shadow: none;
                padding: 0;
            }

            .btn-download {
                display: none;
            }
        }

        @media screen and (max-width: 768px) {
            .report-container {
                padding: 20px;
            }

            .header .logo {
                width: 100px;
                height: 100px;
            }

            .header h1 {
                font-size: 20px;
            }

            .table th,
            .table td {
                padding: 10px;
                font-size: 13px;
            }

            .download-buttons {
                flex-direction: column;
            }

            .btn-download {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<div class="report-container" id="report-container">
    <div class="header">
        <img src="./style/img/jamadas.jpg" class="logo" alt="Logo">
        <h1>Laporan Penjualan - <?= $periode ?></h1>
        <p>
            Jl. A Yani Dsn.Sumberjo Ds.Sumbertanggul Kec. Mojosari<br>
            Kab. Mojokerto, 41382, Jawa Timur Indonesia
        </p>
        <div class="contact-info">
            <i class="fas fa-phone"></i>
            <span>+62 878-5333-8254</span>
        </div>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Nama Penerima</th>
                    <th>Alamat</th>
                    <th>Kota</th>
                    <th>Total Harga</th>
                    <th>Waktu Transaksi</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['order_id']) ?></td>
                        <td><?= htmlspecialchars($order['nama_penerima']) ?></td>
                        <td><?= htmlspecialchars($order['alamat_penerima']) ?></td>
                        <td><?= htmlspecialchars($order['kota']) ?></td>
                        <td>Rp. <?= number_format($order['total_harga'], 0, ',', '.') ?></td>
                        <td><?= date('d M Y H:i', strtotime($order['transaction_time'])) ?></td>
                        <td>
                            <span class="status-badge <?= strtolower($order['transaction_status']) ?>">
                                <?= ucfirst($order['transaction_status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="download-buttons">
        <button class="btn-download pdf" id="download-btn">
            <i class="fas fa-file-pdf"></i>
            Download PDF
        </button>
        <form method="POST" style="display: inline;">
            <button type="submit" name="download_excel" class="btn-download excel">
                <i class="fas fa-file-excel"></i>
                Download Excel
            </button>
        </form>
    </div>
</div>

<script>
    document.getElementById('download-btn').addEventListener('click', function () {
        const element = document.getElementById('report-container');
        const buttons = document.querySelectorAll('.btn-download');

        buttons.forEach(button => button.style.display = 'none');

        const options = {
            margin: 10,
            filename: 'Laporan_Penjualan_<?= $periode ?>.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: {
                scale: 2,
                useCORS: true,
                logging: true
            },
            jsPDF: {
                unit: 'mm',
                format: 'a4',
                orientation: 'portrait'
            }
        };

        html2pdf().from(element).set(options).save().then(() => {
            buttons.forEach(button => button.style.display = 'flex');
        });
    });
</script>

</body>
</html>
