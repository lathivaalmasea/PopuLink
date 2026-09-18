<?php
 session_start();
 require "../config/koneksi.php";

 $daftarFoto = [];
 $sql = "SELECT * FROM galeri_kegiatan ORDER BY id_galeri DESC";
 $qFoto = mysqli_query($koneksi, $sql);

 if ($qFoto && mysqli_num_rows($qFoto) > 0) {
      while ($row = mysqli_fetch_assoc($qFoto)) {
           $daftarFoto[] = $row;
      }
 }
 ?>

 <!DOCTYPE html>
 <html lang="id">
 <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width,
 initial-scale=1.0">
      <title>Galeri Kegiatan | PopuLink</title>

      <link
 href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;6

00;700&display=swap" rel="stylesheet">
   <link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/galeri.css">
</head>
<body>
   <header>
          <div class="logo">
              <img src="../assets/img/populink_logo2.png"
alt="logo">
              <h1>PopuLink</h1>
          </div>
          <div class="nav-right">
              <div class="nav-buttons">
                   <a href="../index.php"
class="login-btn">Beranda</a>
                   <?php if (!isset($_SESSION['role'])): ?>
                       <a href="../login.php"
class="daftar-btn">Login</a>
                   <?php else: ?>
                       <a href="../logic/logout.php"
class="daftar-btn">Logout</a>
                   <?php endif; ?>
              </div>
          </div>
   </header>
   <main>
          <div class="galeri-container">
              <h2 class="galeri-title">Galeri Dokumentasi
Kegiatan</h2>
              <a href="../index.php" class="back-btn">
                   <i class="fa-solid fa-arrow-left"></i> Kembali ke
Beranda
              </a>
              <div class="galeri-grid">
                   <?php if (empty($daftarFoto)): ?>
                       <p>Belum ada foto kegiatan yang diunggah.</p>

                   <?php else: ?>
                      <?php foreach ($daftarFoto as $foto): ?>
                          <div class="galeri-card">
                              <img src="../assets/galeri/<?=
htmlspecialchars($foto['foto']); ?>"
                                     alt="<?=
htmlspecialchars($foto['judul']); ?>">
                              <div class="galeri-info">
                                     <h4><?=
htmlspecialchars($foto['judul']); ?></h4>
                                     <?php if
(!empty($foto['tanggal'])): ?>
                                        <span><?=
htmlspecialchars($foto['tanggal']); ?></span>
                                     <?php endif; ?>
                              </div>
                          </div>
                      <?php endforeach; ?>
                   <?php endif; ?>
             </div>
       </div>
   </main>
   <div class="lightbox-bg" id="lightbox">
       <span class="lightbox-close"
id="closeLightbox">&times;</span>
       <img id="lightbox-img" src="">
   </div>
   <script>
       const lb         = document.getElementById('lightbox');
       const lbImg      = document.getElementById('lightbox-img');
       const lbClose = document.getElementById('closeLightbox');
       document.querySelectorAll('.galeri-card
img').forEach(function (img) {
             img.addEventListener('click', function () {
                   lbImg.src = this.src;
                   lb.classList.add('active');
             });
       });
       lbClose.addEventListener('click', function () {

                 lb.classList.remove('active');
           });
           lb.addEventListener('click', function (e) {
                 if (e.target === lb) {
                     lb.classList.remove('active');
                 }
           });
     </script>
 </body>
 </html>
