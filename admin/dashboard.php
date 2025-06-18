<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id']) && $_SESSION['user_id'] != 'admin') {
    // Jika tidak ada session login, redirect ke halaman login
    header("Location: login_admin.php");
    exit();
}

// Include file konfigurasi koneksi database
require '../config/connection.php';
require '../config/function.php';

// Handle AJAX request for chart data
if (isset($_GET['action']) && $_GET['action'] === 'get_chart_data') {
    $period = $_GET['period'] ?? 'monthly';

    $salesData = getPenjualanByPeriod($period);
    $revenueData = getRevenueByPeriod($period);

    // Format data for charts
    $formattedSalesData = [];
    $formattedRevenueData = [];

    foreach ($salesData as $item) {
        $formattedSalesData[] = [
            'period' => $item['period'],
            'total' => (int)$item['total']
        ];
    }

    foreach ($revenueData as $item) {
        $formattedRevenueData[] = [
            'period' => $item['period'],
            'total_pendapatan' => (float)$item['total_pendapatan']
        ];
    }

    header('Content-Type: application/json');
    echo json_encode([
        'sales' => $formattedSalesData,
        'revenue' => $formattedRevenueData
    ]);
    exit;
}

// Data untuk halaman
$title = "Jamadas";
$jenishalaman = "Dashboard";
$user_email = $_SESSION['user_email']; // Email user yang diambil dari session

// Mengambil data menggunakan fungsi yang sudah dibuat
$total_pemesanan = getTotalCancelledOrders();
$total_penjualan_selesai = getTotalPenjualanSelesai();
$total_pengguna = getTotalUsers();
$penjualan_per_bulan = getPenjualanPerBulan();
$penjualan_terbanyak = getPenjualanTerbanyak();
$pesanan_terbaru = getPesananTerbaru();
$penjualan_chart = getPenjualanChart();
$total_pendapatan_per_bulan = getTotalRevenueByMonth();

// Get stock data
$stock_stats = getStockStatistics();
$low_stock_products = getLowStockProducts(5);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="icon" href="resources/img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="./style/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js"></script>
</head>

<body>
    <div class="container">
        <?php require "template/sidebar.php"; ?>
        <main id="content-to-download" class="main-content">
            <?php require "template/header.php"; ?>            <!-- Dashboard Cards -->
            <section class="dashboard-cards">
                <div class="card">
                    <h3>Total Pesanan Dibatalkan</h3>
                    <p><?= htmlspecialchars($total_pemesanan) ?></p>
                </div>
                <div class="card">
                    <h3>Pesanan Selesai</h3>
                    <p><?= htmlspecialchars($total_penjualan_selesai) ?></p>
                </div>
                <div class="card">
                    <h3>Total Pengguna</h3>
                    <p><?= htmlspecialchars($total_pengguna) ?></p>
                </div>
                <div class="card <?= $stock_stats['low_stock'] > 0 ? 'warning' : '' ?>">
                    <h3>Stok Menipis</h3>
                    <p><?= htmlspecialchars($stock_stats['low_stock']) ?> Produk</p>
                    <?php if ($stock_stats['low_stock'] > 0): ?>
                        <small style="color: #856404;">⚠️ Perlu perhatian</small>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Chart Section -->
            <section class="chart-section">
                <div class="chart-header">
                    <h3>Grafik Penjualan</h3>
                    <div class="chart-filters">
                        <label for="period-filter">Filter Periode:</label>
                        <select id="period-filter">
                            <option value="monthly">Per Bulan</option>
                            <option value="weekly">Per Minggu</option>
                            <option value="daily">Per Hari</option>
                        </select>
                    </div>
                </div>
                <div class="charts">
                    <!-- <div class="chart">
                        <canvas id="myChart1"></canvas>
                    </div> -->
                    <div class="chart">
                        <canvas id="myChart2"></canvas>
                    </div>
                    <div class="chart">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </section>

            <!-- Sales Section -->
            <section class="recent-orders">
                <h3>Penjualan Terbanyak</h3>
                <div class="sales-list">
                    <div class="sales-table-container">
                        <table class="sales-table">
                            <thead>
                                <tr>
                                    <th>Nama Produk</th>
                                    <th>Jumlah Order</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($penjualan_terbanyak as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['nama_produk']) ?></td>
                                        <td><?= htmlspecialchars($item['jumlah_terjual']) ?> orders</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Download Report Section Inside Border -->
                    <div class="download-report-container">
                        <br>
                        <h3>Download Laporan Penjualan</h3>
                        <form id="form-report" method="POST" action="generate_report.php">
                            <label for="bulan">Pilih Bulan:</label>
                            <select name="bulan" id="bulan">
                                <option value="">Semua Bulan</option>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i ?>"><?= date("F", mktime(0, 0, 0, $i, 1)) ?></option>
                                <?php endfor; ?>
                            </select>

                            <label for="tahun">Pilih Tahun:</label>
                            <select name="tahun" id="tahun">
                                <option value="">Semua Tahun</option>
                                <?php
                                $currentYear = date('Y');
                                for ($i = $currentYear; $i >= $currentYear - 10; $i--): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>

                            <button type="submit">Download Report</button>
                        </form>
                    </div>
                </div>
            </section>

            <!-- Recent Orders -->
            <section class="recent-orders">
                <h3>Pesanan Terbaru</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Nama Customer</th>
                            <th>Nomor Penerima</th>
                            <th>Alamat</th>
                            <th>Kode Pos</th>
                            <th>Keterangan Order</th>
                            <th>Pembayaran</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pesanan_terbaru as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['nama_penerima']) ?></td>
                                <td><?= htmlspecialchars($item['nomor_penerima']) ?></td>
                                <td><?= htmlspecialchars($item['alamat_penerima']) ?></td>
                                <td><?= htmlspecialchars($item['kodepos']) ?></td>
                                <td><?= htmlspecialchars($item['keterangan_order']) ?></td>
                                <td><?= htmlspecialchars($item['payment_type']) ?></td>
                                <td>Rp.<?= number_format($item['total_harga']) ?></td>
                                <td><?= htmlspecialchars($item['transaction_status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>                </table>
            </section>

            <!-- Low Stock Products Section -->
            <?php if (!empty($low_stock_products)): ?>
            <section class="recent-orders">
                <div class="header">
                    <h3><i class="fas fa-exclamation-triangle" style="color: #ffc107;"></i> Produk Stok Menipis</h3>
                    <small><?= count($low_stock_products) ?> produk perlu diperhatikan</small>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Sub Kategori</th>
                            <th>Stok Saat Ini</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($low_stock_products as $product): ?>
                            <tr>
                                <td><?= htmlspecialchars($product['nama_produk']) ?></td>
                                <td><?= htmlspecialchars($product['kategori']) ?></td>
                                <td><?= htmlspecialchars($product['sub_kategori']) ?></td>
                                <td>
                                    <span class="stock-badge <?= $product['stok'] == 0 ? 'out-of-stock' : 'low-stock' ?>">
                                        <?= $product['stok'] ?>
                                    </span>
                                </td>
                                <td>Rp.<?= number_format($product['harga_produk'], 0, ',', '.') ?></td>
                                <td>
                                    <a href="edit_barang.php?id=<?= $product['product_id'] ?>" class="btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Global variables for charts
        // let myChart1, myChart2, revenueChart;
        let myChart2, revenueChart;

        // Function to format period labels
        function formatPeriodLabel(period, periodType) {
            switch (periodType) {
                case 'daily':
                    return new Date(period).toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'short'
                    });
                case 'weekly':
                    // Convert YEARWEEK to readable format
                    const yearWeek = period.substring(0, 4);
                    const weekNum = period.substring(4);
                    return `Minggu ${weekNum} ${yearWeek}`;
                case 'monthly':
                default:
                    const [yearMonth, monthNum] = period.split('-');
                    return new Date(yearMonth, monthNum - 1).toLocaleDateString('id-ID', {
                        year: 'numeric',
                        month: 'long'
                    });
            }
        }

        // Function to update charts
        function updateCharts(period) {
            fetch(`dashboard.php?action=get_chart_data&period=${period}`)
                .then(response => response.json())
                .then(data => {
                    // Format labels
                    const salesLabels = data.sales.map(item => formatPeriodLabel(item.period, period));
                    const salesData = data.sales.map(item => item.total);
                    const revenueLabels = data.revenue.map(item => formatPeriodLabel(item.period, period));
                    const revenueData = data.revenue.map(item => item.total_pendapatan);

                    // Update sales charts
                    // if (myChart1) {
                    //     myChart1.data.labels = salesLabels;
                    //     myChart1.data.datasets[0].data = salesData;
                    //     myChart1.update();
                    // }

                    if (myChart2) {
                        myChart2.data.labels = salesLabels;
                        myChart2.data.datasets[0].data = salesData;
                        myChart2.update();
                    }

                    // Update revenue chart
                    if (revenueChart) {
                        revenueChart.data.labels = revenueLabels;
                        revenueChart.data.datasets[0].data = revenueData;
                        revenueChart.update();
                    }
                })
                .catch(error => {
                    console.error('Error fetching chart data:', error);
                });
        }

        // Initialize charts with default data
        document.addEventListener('DOMContentLoaded', function() {
            // Get initial data from PHP
            const bulan = <?php echo json_encode(array_column($penjualan_chart, 'bulan')); ?>;
            const totalPenjualan = <?php echo json_encode(array_column($penjualan_chart, 'total')); ?>;
            const revenueData = <?php echo json_encode($total_pendapatan_per_bulan); ?>;
            const revenueMonths = revenueData.map(item => item.bulan);
            const totalRevenue = revenueData.map(item => item.total_pendapatan);

            // Initialize line chart
            // const ctx1 = document.getElementById('myChart1').getContext('2d');
            // myChart1 = new Chart(ctx1, {
            //     type: 'bar',
            //     data: {
            //         labels: bulan,
            //         datasets: [{
            //             label: 'Produk Terlaris',
            //             data: totalPenjualan,
            //             borderColor: 'rgba(75, 192, 192, 1)',
            //             backgroundColor: 'rgba(75, 192, 192, 0.2)',
            //             borderWidth: 1
            //         }]
            //     },
            //     options: {
            //         responsive: true,
            //         scales: {
            //             y: {
            //                 beginAtZero: true
            //             }
            //         }
            //     }
            // });

            // Initialize bar chart
            const ctx2 = document.getElementById('myChart2').getContext('2d');
            myChart2 = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: bulan,
                    datasets: [{
                        label: 'Total Produk Terjual',
                        data: totalPenjualan,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Initialize revenue chart
            const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
            revenueChart = new Chart(ctxRevenue, {
                type: 'line',
                data: {
                    labels: revenueMonths,
                    datasets: [{
                        label: 'Total Pendapatan (Pesanan Selesai)',
                        data: totalRevenue,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return data.datasets[tooltipItem.datasetIndex].label + ': Rp ' + tooltipItem.yLabel.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            });

            // Add event listener for period filter
            document.getElementById('period-filter').addEventListener('change', function() {
                const selectedPeriod = this.value;
                updateCharts(selectedPeriod);
            });
        });
    </script>

    <script>
        // Fungsi untuk mengunduh halaman sebagai PDF
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('download-pdf').addEventListener('click', function() {
                const element = document.getElementById(
                    'content-to-download'); // Tentukan elemen khusus untuk diunduh
                const options = {
                    margin: 7, // Mengurangi margin untuk memberi ruang lebih
                    filename: 'report_penjualan.pdf', // Nama file PDF
                    image: {
                        type: 'jpeg',
                        quality: 0.98
                    },
                    html2canvas: {
                        scale: 0.5, // Mengurangi skala untuk memastikan konten lebih kecil dan muat
                        logging: true, // Logging untuk debugging
                        letterRendering: true
                    },
                    jsPDF: {
                        unit: 'mm',
                        format: [490, 500], // Ukuran A2 dalam mm (594mm x 420mm)
                        orientation: 'landscape', // Orientasi landscape
                        pagesplit: true // Membagi konten ke beberapa halaman jika diperlukan
                    }
                };
                html2pdf().from(element).set(options)
                    .save(); // Mengunduh elemen dengan pengaturan yang ditentukan
            });
        });
    </script>

</body>

</html>