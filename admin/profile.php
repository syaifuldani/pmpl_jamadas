<?php
session_start();
// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['jenis_pengguna'] != 'admin') {
    // Jika tidak ada session login, redirect ke halaman login
    header("Location: login_admin.php");
    exit();
}
require '../config/connection.php'; // Menghubungkan ke database
require '../config/function.php'; //

// Variabel untuk status pesan
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $update = updateProfileAdmin($_POST);

    // Jika berhasil, redirect atau tampilkan pesan sukses
    if ($update['status'] === true) {
        $success_message = $update['message'];
    } else {
        $error_message = $update['message'];
    }
}
// var_dump($_SESSION);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Admin - <?= $_SESSION['user_name'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            padding: 30px;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #666;
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            color: #77dd77;
        }

        .back-button i {
            font-size: 16px;
        }

        h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        h1 i {
            color: #77dd77;
        }

        .content-wrapper {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }

        .profile-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
        }

        .profile-pic {
            position: relative;
            width: 200px;
            height: 200px;
            margin: 0 auto 20px;
            cursor: pointer;
        }

        .profile-pic img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #ffffff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .profile-pic:hover img {
            transform: scale(1.05);
        }

        .edit-text {
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.7);
            color: #ffffff;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .profile-pic:hover .edit-text {
            opacity: 1;
        }

        .profile-name {
            color: #333;
            font-size: 20px;
            margin-bottom: 5px;
        }

        .profile-role {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .form-container {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #333;
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            color: #333;
            transition: all 0.3s ease;
            background-color: #ffffff;
        }

        .form-group input:focus {
            border-color: #77dd77;
            box-shadow: 0 0 0 3px rgba(119, 221, 119, 0.1);
            outline: none;
        }

        .btn {
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
            width: 100%;
            justify-content: center;
        }

        .btn:hover {
            background-color: #5cb85c;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn i {
            font-size: 16px;
        }

        @media screen and (max-width: 768px) {
            .content-wrapper {
                grid-template-columns: 1fr;
            }

            .profile-info {
                padding: 20px;
            }

            .profile-pic {
                width: 150px;
                height: 150px;
            }

            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="dashboard.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Kembali ke Dashboard
        </a>
        <h1>
            <i class="fas fa-user-circle"></i>
            Profil Admin
        </h1>

        <div class="content-wrapper">
            <div class="profile-info">
                <!-- <div class="profile-pic">
                    <img id="profileImage"
                        src="<?= isset($_SESSION['user_profile']) ? $_SESSION['user_profile'] : '../resources/img/profiledefault.png' ?>"
                        alt="Profile Picture">
                    <span class="edit-text">Klik untuk mengubah foto</span>
                    <input type="file" id="imageUpload" name="profile-image" accept="image/*" style="display: none;">
                </div> -->
                <h2 class="profile-name"><?= $_SESSION['user_name'] ?></h2>
                <p class="profile-role">Administrator</p>
            </div>

            <div class="form-container">
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nama_lengkap">
                            <i class="fas fa-user"></i>
                            Nama Lengkap
                        </label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama lengkap"
                            value="<?= isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="alamat">
                            <i class="fas fa-map-marker-alt"></i>
                            Alamat
                        </label>
                        <input type="text" id="alamat" name="alamat" placeholder="Masukkan alamat lengkap"
                            value="<?= isset($_SESSION['alamat']) ? $_SESSION['alamat'] : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="nomor_telepon">
                            <i class="fas fa-phone"></i>
                            Nomor Telepon
                        </label>
                        <input type="text" id="nomor_telepon" name="nomor_telepon" placeholder="Masukkan nomor telepon"
                            value="<?= isset($_SESSION['nomor_telepon']) ? $_SESSION['nomor_telepon'] : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            Email
                        </label>
                        <input type="email" id="email" name="email" placeholder="Masukkan email"
                            value="<?= isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '' ?>">
                    </div>

                    <button class="btn" type="submit">
                        <i class="fas fa-save"></i>
                        Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>

    <script>
        <?php if ($success_message): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '<?= $success_message ?>',
                confirmButtonColor: '#77dd77'
            });
        <?php elseif ($error_message): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: '<?= $error_message ?>',
                confirmButtonColor: '#77dd77'
            });
        <?php endif; ?>

        document.querySelector('.profile-pic').addEventListener('click', function () {
            document.querySelector('#imageUpload').click();
        });

        document.querySelector('#imageUpload').addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.querySelector('#profileImage').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html>