<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ env('APP_NAME') }}</title>
  <meta name="description"
    content="{{ env('APP_NAME') }} adalah solusi digital untuk mengelola proses produksi maklun dan konveksi secara transparan, terstruktur, dan mudah diawasi — dari penyerahan bahan hingga pembayaran hasil kerja.">
  <meta name="keywords" content="">
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  @vite([])
</head>

<body class="index-page">
  <header class="header d-flex align-items-center sticky-top" id="header">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">
      <a class="logo d-flex align-items-center me-auto" href="./">
        <h1 class="sitename">{{ env('APP_NAME') }}</h1>
      </a>

      <nav class="navmenu" id="navmenu">
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <a class="btn-getstarted" href="{{ route('admin.auth.login') }}">Masuk</a>

    </div>
  </header>

  <main class="main">

    <!-- Hero Section -->
    <section class="section hero light-background" id="hero" style="border-top:1px solid #ddd;">
      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-6 order-lg-1 d-flex flex-column justify-content-center order-2" data-aos="fade-up">
            <h2>{{ env('APP_NAME') }}</h2>
            <p>Selamat datang di {{ env('APP_NAME') }}.</p>
          </div>
          <div class="col-lg-6 order-lg-2 hero-img order-1" data-aos="zoom-out" data-aos-delay="200">
            <img class="img-fluid" src="assets/img/hero-img.jpg" alt="" style="border-radius: 10px;">
          </div>
        </div>
      </div>
    </section><!-- /Hero Section -->


  </main>

  <footer class="footer position-relative" id="footer">
    <div class="copyright container mt-4 text-center" style="border:none;">
      <p>
        © {{ date('Y') }}
        <strong class="sitename px-1"><a href="https://shiftech.my.id">Shiftech Indonesia</a></strong>
        <span>All Rights Reserved</span>
      </p>
    </div>

  </footer>

  <!-- Scroll Top -->
  <a class="scroll-top d-flex align-items-center justify-content-center" id="scroll-top" href="#">
    <i class="bi bi-arrow-up-short"></i>
  </a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>
