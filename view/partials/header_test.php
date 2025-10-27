<?php
// Determine active page for highlighting
$activePage = isset($page) && $page ? $page : ($_GET['page'] ?? 'bantin');
?>

<nav class="navbar navbar-expand-lg header-navbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <span class="app-badge me-2 d-inline-flex align-items-center justify-content-center">
                <i class="bi bi-heart-fill"></i>
            </span>
            <span class="brand-text">SoulMatch</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav mx-auto header-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($activePage === 'bantin') ? 'active' : ''; ?>" href="home_test.php?page=bantin">Trang chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($activePage === 'timkiem') ? 'active' : ''; ?>" href="home_test.php.php?page=timkiem">Tìm kiếm</a>
                </li>
            </ul>

        </div>
    </div>
    <div class="header-divider"></div>
</nav>