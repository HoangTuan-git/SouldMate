<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kết Nối Bốn Phương - Ứng dụng hẹn hò số 1 Việt Nam</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --accent-color: #ff4458;
            --text-dark: #2d3748;
            --text-light: #718096;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
        }

        /* Hero Section */
        .hero-section {
            background: var(--primary-gradient);
            color: white;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="20" cy="20" r="1" fill="white" opacity="0.1"/><circle cx="80" cy="40" r="0.5" fill="white" opacity="0.1"/><circle cx="40" cy="80" r="1.5" fill="white" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
            pointer-events: none;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            color: white !important;
        }

        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: white !important;
            transform: translateY(-1px);
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            margin-bottom: 2.5rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        .btn-hero {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            border: none;
            margin: 0.5rem;
        }

        .btn-primary-hero {
            background: white;
            color: var(--primary-gradient);
            background: white;
            color: #667eea;
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.3);
        }

        .btn-primary-hero:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(255, 255, 255, 0.4);
            color: #5a67d8;
        }

        .btn-outline-hero {
            background: transparent;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .btn-outline-hero:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
            transform: translateY(-3px);
            color: white;
        }

        /* Features Section */
        .features-section {
            padding: 6rem 0;
            background: #f8fafc;
        }

        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: none;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
        }

        .feature-icon-1 {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .feature-icon-2 {
            background: linear-gradient(135deg, #f093fb, #f5576c);
        }

        .feature-icon-3 {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
        }

        /* Stats Section */
        .stats-section {
            background: var(--primary-gradient);
            color: white;
            padding: 4rem 0;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            display: block;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* CTA Section */
        .cta-section {
            padding: 6rem 0;
            background: white;
        }

        .cta-card {
            background: var(--secondary-gradient);
            color: white;
            border-radius: 30px;
            padding: 4rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .cta-content {
            position: relative;
            z-index: 2;
        }

        /* Footer */
        .footer {
            background: #2d3748;
            color: white;
            padding: 3rem 0 1rem;
        }

        .footer-link {
            color: #a0aec0;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-link:hover {
            color: white;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .btn-hero {
                display: block;
                margin: 0.5rem 0;
                text-align: center;
            }

            .stat-number {
                font-size: 2rem;
            }
        }

        /* Animations */
        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .delay-1 {
            animation-delay: 0.2s;
        }

        .delay-2 {
            animation-delay: 0.4s;
        }

        .delay-3 {
            animation-delay: 0.6s;
        }
    </style>
</head>

<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-dark">
                <div class="container">
                    <a class="navbar-brand" href="#">
                        <i class="bi bi-heart-fill me-2" style="color: #ff4458;"></i>
                        Kết Nối Bốn Phương
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="#features">Tính năng</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#about">Về chúng tôi</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="home_test.php?page=dangnhap">Đăng nhập</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Hero Content -->
            <div class="container">
                <div class="row align-items-center min-vh-100">
                    <div class="col-lg-6">
                        <h1 class="hero-title fade-in-up">
                            Tìm kiếm tình yêu đích thực
                        </h1>
                        <p class="hero-subtitle fade-in-up delay-1">
                            Kết nối với hàng triệu người dùng trên khắp Việt Nam.
                            Tìm kiếm người bạn đời hoàn hảo với công nghệ AI thông minh.
                        </p>
                        <div class="fade-in-up delay-2">
                            <a href="view/dangky.php" class="btn-hero btn-primary-hero">
                                <i class="bi bi-heart-fill me-2"></i>
                                Tạo tài khoản miễn phí
                            </a>
                            <a href="home_test.php" class="btn-hero btn-outline-hero">
                                <i class="bi bi-play-circle me-2"></i>
                                Dùng thử ngay
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 text-center">
                        <div class="fade-in-up delay-3">
                            <i class="bi bi-phone" style="font-size: 15rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-5 fw-bold mb-3">Tại sao chọn chúng tôi?</h2>
                    <p class="lead text-muted">
                        Khám phá những tính năng độc đáo giúp bạn tìm được tình yêu đích thực
                    </p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center">
                        <div class="feature-icon feature-icon-1 text-white">
                            <i class="bi bi-heart-pulse"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Matching theo nhu cầu</h4>
                        <p class="text-muted">
                            Tìm kiếm những người phù hợp nhất với bạn dựa trên sở thích và tính cách.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center">
                        <div class="feature-icon feature-icon-2 text-white">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4 class="fw-bold mb-3">An toàn & bảo mật</h4>
                        <p class="text-muted">
                            Hệ thống xác thực nghiêm ngặt và bảo mật thông tin cá nhân tối đa cho trải nghiệm an toàn.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center">
                        <div class="feature-icon feature-icon-3 text-white">
                            <i class="bi bi-chat-dots-fill"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Chat thú vị</h4>
                        <p class="text-muted">
                            Trò chuyện với nhiều tính năng độc đáo: sticker, voice message, và video call chất lượng cao.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-lg-3 col-md-6 mb-4">
                    <span class="stat-number">5M+</span>
                    <div class="stat-label">Người dùng</div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <span class="stat-number">2M+</span>
                    <div class="stat-label">Cặp đôi thành công</div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <span class="stat-number">50K+</span>
                    <div class="stat-label">Cuộc hẹn mỗi ngày</div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <span class="stat-number">95%</span>
                    <div class="stat-label">Người dùng hài lòng</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-card">
                <div class="cta-content">
                    <h2 class="display-6 fw-bold mb-3">Sẵn sàng tìm kiếm tình yêu?</h2>
                    <p class="lead mb-4">
                        Tham gia cộng đồng hàng triệu người dùng và bắt đầu hành trình tìm kiếm người bạn đời của bạn ngay hôm nay!
                    </p>
                    <div>
                        <a href="view/dangky.php" class="btn btn-light btn-lg me-3 rounded-pill px-4">
                            <i class="bi bi-heart-fill me-2"></i>
                            Đăng ký tài khoản ngay!
                        </a>
                        <a href="home_test.php" class="btn btn-outline-light btn-lg rounded-pill px-4">
                            Dùng thử miễn phí
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Add scroll effect to navbar
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(102, 126, 234, 0.95)';
                navbar.style.backdropFilter = 'blur(10px)';
            } else {
                navbar.style.background = 'transparent';
                navbar.style.backdropFilter = 'none';
            }
        });
    </script>
</body>

</html>