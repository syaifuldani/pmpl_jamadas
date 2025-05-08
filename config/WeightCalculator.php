<?php
class WeightCalculator
{
    // Konstanta untuk gram per m² berbagai jenis kertas
    private static $PAPER_WEIGHTS = [
        'artpaper_120' => 120,
        'artpaper_150' => 150,
        'hvs_70' => 70,
        'hvs_80' => 80,
        'art_carton' => 260,
        // Tambahkan jenis kertas lainnya sesuai kebutuhan
    ];

    // Konstanta untuk ukuran kertas (dalam cm)
    private static $PAPER_SIZES = [
        'A4' => ['width' => 21.0, 'height' => 29.7],
        'A5' => ['width' => 14.8, 'height' => 21.0],
        'F4' => ['width' => 21.5, 'height' => 33.0],
        // Tambahkan ukuran lainnya sesuai kebutuhan
    ];

    public static function calculateWeight($paperType, $paperSize, $quantity)
    {
        try {
            // Validasi input
            if (!isset(self::$PAPER_WEIGHTS[$paperType])) {
                error_log(message: "Invalid paper type: $paperType");
                throw new Exception("Jenis kertas tidak valid");
            }

            if (!isset(self::$PAPER_SIZES[$paperSize])) {
                error_log("Invalid paper size: $paperSize");
                throw new Exception("Ukuran kertas tidak valid");
            }

            if ($quantity <= 0) {
                throw new Exception("Jumlah harus lebih dari 0");
            }

            // Default values jika data tidak valid
            $paperType = strtolower($paperType);
            $paperSize = strtolower($paperSize);

            // Ambil spesifikasi kertas
            $gramPerM2 = self::$PAPER_WEIGHTS[$paperType] ?? 120;
            $dimensions = self::$PAPER_SIZES[$paperSize] ?? ['width' => 21.0, 'height' => 29.7];

            // Hitung berat per lembar (dalam gram)
            // Rumus: (panjang x lebar dalam cm) x gramatur / 10000
            $weightPerSheet = ($dimensions['width'] * $dimensions['height'] * $gramPerM2) / 10000;

            // Total berat
            $totalWeight = $weightPerSheet * $quantity;

            // Debug log
            error_log("Weight calculation: {$quantity} sheets of {$paperType} {$paperSize} = {$totalWeight}g");

            return ceil($totalWeight);

        } catch (Exception $e) {
            error_log("Weight calculation error: " . $e->getMessage());
            // Return minimal weight jika terjadi error
            return 100;
        }
    }
}
?>