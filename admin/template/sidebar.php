<div class="sidebar">
    <div class="logo">
        <img src="./style/img/jamadas.jpg" alt="Logo">
        <span>JAMADAS</span>
    </div>
    <nav>
        <ul>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'product.php' ? 'active' : ''; ?>">
                <a href="./product.php">
                    <i class="fas fa-box"></i>
                    <span>All Product</span>
                </a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'orderlist.php' ? 'active' : ''; ?>">
                <a href="./orderlist.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Order List</span>
                </a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'daftar_customer.php' ? 'active' : ''; ?>">
                <a href="./daftar_customer.php">
                    <i class="fas fa-users"></i>
                    <span>Daftar Customer</span>
                </a>
            </li>
        </ul>
    </nav>


    <div class="sidebar-footer">
        <div class="user-info">
            <!-- <img src="./style/img/admin-avatar.png" alt="Admin" class="user-avatar"> -->
            <div class="user-details">
                <div class="user-name"><?php echo isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'Admin'; ?></div>
                <div class="user-role">Administrator</div>
            </div>
        </div>
    </div>
</div>

<!-- Tambahkan Font Awesome untuk ikon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categoryToggle = document.querySelector('.category-toggle');
        const categoryDropdown = document.querySelector('.category-dropdown');
        const categoryMenu = document.querySelector('.category-menu');

        // Toggle dropdown
        categoryToggle.addEventListener('click', function(e) {
            e.preventDefault();
            categoryDropdown.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!categoryDropdown.contains(e.target)) {
                categoryDropdown.classList.remove('active');
            }
        });

        // Handle category selection
        const categoryLinks = document.querySelectorAll('.category-menu a');
        categoryLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Remove active class from all links
                categoryLinks.forEach(l => l.parentElement.classList.remove('active'));
                // Add active class to clicked link
                this.parentElement.classList.add('active');
                // Update toggle button text
                categoryToggle.querySelector('span').textContent = this.textContent;
                // Close dropdown
                categoryDropdown.classList.remove('active');
            });
        });
    });
</script>
</body>

</html>