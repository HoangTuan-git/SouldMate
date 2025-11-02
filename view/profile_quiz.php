<!-- Page tạo hồ sơ sau khi đăng ký -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once(__DIR__ . "/../controller/cHoSo.php");
$cHoSo = new controlHoSo();
// Nếu đã đăng nhập và đã có hồ sơ thì không cho vào trang này nữa
if (isset($_SESSION['uid']) && $cHoSo->checkHoSoExists($_SESSION['uid'])) {
    header("Location: home_test.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chào mừng đến với SoulMatch</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    
</head>

<body class="d-flex flex-column vh-100">
    <main class="main-content">
        <div class="container text-center mt-5">
            <h1 class="display-5 fw-bold mb-4">Chào mừng đến với SoulMatch!</h1>
            <div class="card question-card shadow-sm">
                <!-- Nút X ở góc phải -->
                <button type="button" class="btn-close-custom" onclick="window.location.href='home_test.php?page=dangnhap'" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div class="card-body p-4 p-md-5">

                    <h4 class="fw-bold mb-3">Bạn có muốn hẹn hò nghiêm túc?</h4>

                    <p class="text-secondary mb-4">Lựa chọn này sẽ giúp chúng tôi tùy chỉnh trải nghiệm hồ sơ của bạn. Nếu chọn <b>Có</b> bạn cần cung cấp thêm các thông tin chi tiết.</p>

                    <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                        <button type="button" class="btn btn-primary btn-lg px-5"><a href="home_test.php?page=taohosonghiemtuc">Có</a></button>
                        <button type="button" class="btn btn-light btn-lg px-5 border"><a href="home_test.php?page=taohosotrainghiem">Không</a></button>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>