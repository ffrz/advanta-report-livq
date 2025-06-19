<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>{{ env('APP_NAME') }}</title>
  <meta name="description" content="{{ env('APP_NAME') }} adalah solusi digital untuk mengelola proses produksi maklun dan konveksi secara transparan, terstruktur, dan mudah diawasi — dari penyerahan bahan hingga pembayaran hasil kerja.">
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
  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">
      <a href="./" class="logo d-flex align-items-center me-auto">
        <h1 class="sitename">{{ env('APP_NAME') }}</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#hero" class="active">Beranda</a></li>
          <li><a href="#about">Tentang</a></li>
          <li><a href="#features">Fitur</a></li>
          <li><a href="#contact">Hubungi Kami</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <a class="btn-getstarted" href="{{ route('admin.auth.login') }}">Masuk</a>
      <a class="btn-getstarted" href="{{ route('admin.auth.register') }}">Daftar</a>

    </div>
  </header>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="section hero light-background">

      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-6 order-lg-1 d-flex flex-column justify-content-center order-2" data-aos="fade-up">
            <h2>Kelola Relasi Pelanggan dan Prospek secara Profesional</h2>
            <p>{{ env('APP_NAME') }} adalah sistem manajemen hubungan pelanggan (Customer Relationship Management)
              berbasis web yang membantu Anda melacak interaksi, memantau progres prospek, dan meningkatkan konversi
              penjualan dalam satu platform terintegrasi.</p>
          </div>
          <div class="col-lg-6 order-lg-2 hero-img order-1" data-aos="zoom-out" data-aos-delay="200">
            <img src="assets/img/hero-img.jpg" class="img-fluid" style="border-radius: 10px;" alt="">
          </div>
        </div>
      </div>

    </section><!-- /Hero Section -->

    <!-- About Section -->
    <section id="about" class="section about">

      <div class="container">

        <h3 class="text-center">Solusi CRM Fleksibel untuk Berbagai Jenis Usaha dan Proyek</h3>
        <p class="mb-5 text-center">
          Apapun bidang usaha Anda — baik jasa, produk, B2B, maupun individu — {{ env('APP_NAME') }} memberi
          Anda kendali penuh atas data pelanggan dan kinerja tim pemasaran atau penjualan.
        </p>
        <div class="row gy-3 items-center">
          <div class="col-lg-6 d-flex flex-column justify-content-center" data-aos="fade-up" data-aos-delay="100">
            <img src="assets/img/about-img.jpg" alt="" class="img-fluid" style="border-radius:10px;">
          </div>
          <div class="col-lg-6 d-flex flex-column justify-content-center" data-aos="fade-up" data-aos-delay="200">
            <div class="about-content ps-lg-3 ps-0">
              <ul>
                <li>
                  <i class="bi bi-building-check"></i>
                  <div>
                    <h4>Pelacakan Kunjungan & Interaksi</h4>
                    <p>Catat setiap pertemuan, follow-up, atau komunikasi yang dilakukan
                      tim Anda dengan prospek atau pelanggan. Lacak hasil kunjungan, respon, dan rencana tindak lanjut secara historis.</p>
                  </div>
                </li>
                <li>
                  <i class="bi bi-kanban"></i>
                  <div>
                    <h4>Manajemen Status Prospek</h4>
                    <p>Tandai setiap pelanggan berdasarkan status mereka seperti: baru, tertarik, dalam negosiasi,
                      atau ditutup (berhasil/gagal). Fokus pada prospek yang paling potensial.</p>
                  </div>
                </li>
                <li>
                  <i class="bi bi-clock-history"></i>
                  <div>
                    <h4>Riwayat Layanan & Penawaran</h4>
                    <p>Lihat data lengkap mengenai layanan atau produk yang pernah dikenalkan atau digunakan oleh masing-masing pelanggan.
                      Cocok untuk upselling dan pengembangan relasi jangka panjang.</p>
                  </div>
                </li>
                <li>
                  <i class="bi bi-person-check"></i>
                  <div>
                    <h4>Penugasan Tim</h4>
                    <p>Tentukan siapa yang bertanggung jawab terhadap tiap prospek. Semua aktivitas terhubung ke pengguna tertentu
                      untuk akuntabilitas dan pelacakan kerja.</p>
                  </div>
                </li>
                <li>
                  <i class="bi bi-speedometer"></i>
                  <div>
                    <h4>Dashboard Insight</h4>
                    <p>Pantau progres tim, status pelanggan, jumlah interaksi, dan hasil penjualan dalam satu tampilan visual yang mudah dipahami.</p>
                  </div>
                </li>
                <li>
                  <i class="bi bi-bell"></i>
                  <div>
                    <h4>Notifikasi & Log Aktivitas</h4>
                    <p>Setiap update tercatat secara otomatis: perubahan status, kunjungan baru, penambahan catatan, hingga penugasan ulang.
                      Anda tidak akan kehilangan jejak.</p>
                  </div>
                </li>
              </ul>
            </div>

          </div>
        </div>

      </div>

    </section><!-- /About Section -->

    <!-- Features Section -->
    <section id="features" class="services section light-background">

      <!-- Section Title -->
      <div class="section-title container" data-aos="fade-up">
        <h2>Manfaat Utama Shiftech CRM</h2>
      </div><!-- End Section Title -->

      <div class="container">

        <div class="row gy-4">

          <div class="col-12 col-md-3" data-aos="fade-up" data-aos-delay="100">
            <div class="service-item position-relative">
              <div class="icon"><i class="bi bi-transparency icon"></i></div>
              <h4><a href="#" class="stretched-link">Transparansi Proses Penjualan</a></h4>
              <p>Semua aktivitas terekam dan terstruktur sehingga Anda tahu apa yang sedang terjadi, siapa yang melakukannya, dan apa langkah selanjutnya.</p>
            </div>
          </div>
          <div class="col-12 col-md-3" data-aos="fade-up" data-aos-delay="200">
            <div class="service-item position-relative">
              <div class="icon"><i class="bi bi-database-up icon"></i></div>
              <h4><a href="#" class="stretched-link">Efisiensi & Produktivitas</a></h4>
              <p>Kurangi catatan manual dan tumpukan spreadsheet. Sistem digital ini membuat manajemen pelanggan menjadi jauh lebih mudah.</p>
            </div>
          </div>
          <div class="col-12 col-md-3" data-aos="fade-up" data-aos-delay="200">
            <div class="service-item position-relative">
              <div class="icon"><i class="bi bi-graph-up-arrow icon"></i></div>
              <h4><a href="#" class="stretched-link">Skalabilitas Tim & Proyek</a></h4>
              <p>Baik dikelola sendiri atau bersama tim, Shiftech CRM dirancang untuk mendukung pertumbuhan usaha tanpa ribet.</p>
            </div>
          </div>
          <div class="col-12 col-md-3" data-aos="fade-up" data-aos-delay="200">
            <div class="service-item position-relative">
              <div class="icon"><i class="bi bi-hand-thumbs-up icon"></i></div>
              <h4><a href="#" class="stretched-link">Meningkatkan Peluang Konversi</a></h4>
              <p>Dengan data yang lebih jelas dan terpusat, Anda bisa membuat keputusan yang lebih tepat dalam menawarkan produk,
                layanan, atau tindak lanjut.</p>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /Services Section -->


  </main>

  <footer id="footer" class="footer position-relative">

    <div class="footer-newsletter">
      <div class="container">
        <div class="row justify-content-center text-center">
          <div class="col-lg-12">
            <h4>Mulai Sekarang!</h4>
            <p>Tinggalkan sistem pencatatan manual. Kelola pelanggan, tim, dan prospek Anda lebih cerdas dengan {{ env('APP_NAME') }}!</p>
            <a href="https://wa.me/6285317404760?text=Halo+saya+ingin+mendaftar+aplikasi+{{ env('APP_NAME') }}+untuk+usaha+saya.+Mohon+info+selanjutnya." target="_blank" class="btn-get-started">
              Pesan Sekrang
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="container">
      <div class="row text-center">
        <div class="col-lg-12 mt-5 text-center">
          <h4>Hubungi Kami</h4>
          <p class="mt-3"><strong>Telepon / WA:</strong> <a href="https://wa.me/6285317404760">+6285-3174-04760</a>
          </p>
          <p><strong>Email:</strong> <span>crm@shiftech.my.id</span></p>
        </div>
      </div>
      

    </div>

    <div class="copyright container mt-4 text-center">
      <p>© {{ date('Y') }} <strong class="sitename px-1"><a href="https://shiftech.my.id">Shiftech
            Indonesia</a></strong> <span>All Rights Reserved</span></p>
      <!-- <div class="credits"> -->
      <!-- All the links in the footer should remain intact. -->
      <!-- You can delete the links only if you've purchased the pro version. -->
      <!-- Licensing information: https://bootstrapmade.com/license/ -->
      <!-- Purchase the pro version with working PHP/AJAX contact form: [buy-url] -->
      <!-- Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
      </div> -->
    </div>

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
      class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>