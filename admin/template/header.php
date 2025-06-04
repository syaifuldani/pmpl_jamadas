<header class="header">
    <div class="header-left">
        <h2> <?= $jenishalaman ?></h2>
        <div class="date">
            <i class="far fa-calendar-alt"></i>
            <?php echo date('d M Y'); ?>
        </div>
    </div>
    <div class="admin-dropdown">
        <button class="dropdown-toggle">
            <img src="./style/img/admin-avatar.png" alt="Admin" class="admin-avatar">
            <span class="admin-name"><?php echo isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'Admin'; ?></span>
            <i class="fas fa-chevron-down"></i>
        </button>
        <div class="dropdown-menu">
            <a href="../admin/profile.php" class="dropdown-item">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
            <a href="../admin/process/logout.php" class="dropdown-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</header>

<!-- Tambahkan Font Awesome jika belum ada -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">