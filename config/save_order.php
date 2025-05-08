<?php
session_start();
require_once '../config/connection.php';
require_once '../config/midtrans_config.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['pending_order'])) {
        throw new Exception('No pending order found');
    }

    $pendingOrder = $_SESSION['pending_order'];
    $paymentResult = json_decode(file_get_contents('php://input'), true)['payment_result'];

    // Start transaction
    $db->beginTransaction();

    try {
        // Format alamat lengkap
        $data = $pendingOrder['data'];
        $alamat_parts = [
            $data['alamatpenerima'],
            "Kelurahan " . $data['kelurahan'],
            "Kecamatan " . $data['kecamatan'],
            $data['kota'],
            $data['provinsi'],
            $data['kodepos']
        ];

        $full_address = implode(", ", array_filter($alamat_parts));

        // Hitung total dengan biaya ongkir
        $shipping_cost = !empty($data['shipping_cost']) ? floatval($data['shipping_cost']) : 0;
        $total_with_shipping = $pendingOrder['total_amount'] + $shipping_cost;

        // Prepare payment details
        $paymentDetails = json_encode([
            'transaction_id' => $paymentResult['transaction_id'],
            'payment_type' => $paymentResult['payment_type'],
            'transaction_time' => $paymentResult['transaction_time'],
            'transaction_status' => $paymentResult['transaction_status'],
            'gross_amount' => $paymentResult['gross_amount'],
            'status_code' => $paymentResult['status_code'],
            'status_message' => $paymentResult['status_message'],
            'fraud_status' => $paymentResult['fraud_status']
        ]);

        // 1. Simpan order dengan informasi pembayaran
        $sql = "INSERT INTO orders (
            order_id, 
            user_id, 
            total_harga, 
            nama_penerima, 
            nomor_penerima, 
            alamat_penerima, 
            kota, 
            kodepos, 
            transaction_status,
            payment_type,
            payment_details,
            transaction_time,
            keterangan_order,
            fraud_status,
            created_at,
            updated_at
        ) VALUES (
            :order_id, 
            :user_id, 
            :total_harga, 
            :nama_penerima, 
            :nomor_penerima, 
            :alamat_penerima, 
            :kota, 
            :kodepos, 
            :transaction_status,
            :payment_type,
            :payment_details,
            :transaction_time,
            :keterangan_order,
            :fraud_status,
            NOW(),
            NOW()
        )";

        $params = [
            ':order_id' => $pendingOrder['order_id'],
            ':user_id' => $_SESSION['user_id'],
            ':total_harga' => $total_with_shipping,
            ':nama_penerima' => $data['namapenerima'],
            ':nomor_penerima' => $data['notelppenerima'],
            ':alamat_penerima' => $full_address,
            ':kota' => $data['kota'],
            ':kodepos' => $data['kodepos'],
            ':transaction_status' => $paymentResult['transaction_status'],
            ':payment_type' => $paymentResult['payment_type'],
            ':payment_details' => $paymentDetails,
            ':transaction_time' => $paymentResult['transaction_time'],
            ':keterangan_order' => $data['keterangan_order'] ?? '',
            ':fraud_status' => $paymentResult['fraud_status']
        ];

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        // 2. Simpan order details
        $sql = "INSERT INTO order_details (
                order_id, 
                product_id, 
                jumlah_order, 
                harga_order
            ) VALUES (
                :order_id, 
                :product_id, 
                :jumlah_order, 
                :harga_order
            )";

        $stmt = $db->prepare($sql);
        foreach ($pendingOrder['cart_items'] as $item) {
            $detail_params = [
                ':order_id' => $pendingOrder['order_id'],
                ':product_id' => $item['product_id'],
                ':jumlah_order' => $item['jumlah'],
                ':harga_order' => $item['harga_produk']
            ];
            $stmt->execute($detail_params);
        }

        // 3. Insert data ke shipments
        $sql = "INSERT INTO shipments (
                    order_id, 
                    ekspedisi, 
                    biaya_ongkir, 
                    estimasi_sampai, 
                    alamat_pengiriman
                ) VALUES (
                    :order_id, 
                    :ekspedisi, 
                    :biaya_ongkir, 
                    :estimasi_sampai, 
                    :alamat_pengiriman
                )";

        $shipping_params = [
            ':order_id' => $pendingOrder['order_id'],
            ':ekspedisi' => $data['shipping_courier'] ?? null,
            ':biaya_ongkir' => $data['shipping_cost'] ?? null,
            ':estimasi_sampai' => $data['shipping_eta'] ?? null,
            ':alamat_pengiriman' => $full_address
        ];

        $stmt = $db->prepare($sql);
        $stmt->execute($shipping_params);

        // 4. Hapus items dari cart
        $sql = "DELETE FROM carts WHERE user_id = :user_id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':user_id' => $_SESSION['user_id']]);

        // Clear pending order from session
        unset($_SESSION['pending_order']);

        $db->commit();

        echo json_encode([
            'status' => 'success',
            'payment_info' => [
                'payment_type' => $paymentResult['payment_type'],
                'transaction_time' => $paymentResult['transaction_time'],
                'status' => $paymentResult['transaction_status']
            ]
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}