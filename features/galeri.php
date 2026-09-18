<?php
 session_start();
 require "../config/koneksi.php";

 $status = $_GET['status'] ?? 'semua';
 $cari = trim($_GET['cari'] ?? '');
 $today = date('Y-m-d');
 $where = "1=1";

 if ($cari !== "") {
     $keyword = mysqli_real_escape_string($koneksi, $cari);
     $where .= "AND (judul LIKE '%$keyword%' OR lokasi LIKE
 '%$keyword%' OR deskripsi LIKE '%$keyword%')";
 }

 if ($status === 'akan') {
     $where .= " AND tanggal >= '$today'";
 } elseif ($status === 'selesai') {
     $where .= " AND tanggal < '$today'";
 }

$sqlKegiatan = "SELECT * FROM kegiatan WHERE $where ORDER BY
tanggal DESC";
$resultKegiatan = mysqli_query($koneksi, $sqlKegiatan);

function formatTanggalPendek($tgl) {
     if (!$tgl)
         return "-";
     $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul',
'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
     $ts = strtotime($tgl);
     $idx = (int) date('n', $ts) - 1;
     return date('d', $ts) . ' ' . $bulan[$idx] . ' ' . date('Y',
$ts);
}

function statusKegiatan($tgl) {
     if (!$tgl)
         return "Belum dijadwalkan";
     $hariIni = date('Y-m-d');
     if ($tgl < $hariIni)
         return "Selesai";
     if ($tgl == $hariIni)
         return "Berlangsung";
     return "Akan Datang";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width,
initial-scale=1.0">
     <title>Informasi Kegiatan | PopuLink</title>
     <link
href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
     <link rel="stylesheet"

href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/informasi_kegiatan.css">
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
   <section class="info-wrapper">
          <div class="info-header">
              <h2>Informasi Kegiatan</h2>
              <p>Rekap kegiatan yang pernah dan akan dilaksanakan
di lingkungan.</p>
          </div>
          <form class="info-toolbar" method="get">
              <div class="info-search">
                   <i class="fa-solid fa-magnifying-glass"></i>
                   <input type="text" name="cari" placeholder="Cari
judul atau lokasi kegiatan..."
                       value="<?= htmlspecialchars($cari); ?>">
              </div>

           <div class="info-filter">
                  <button type="submit" name="status" value="semua"
                     class="filter-pill <?= $status === 'semua' ?
'active' : ''; ?>">Semua</button>
                  <button type="submit" name="status" value="akan"
                     class="filter-pill <?= $status === 'akan' ?
'active' : ''; ?>"> Akan Datang</button>
                  <button type="submit" name="status"
value="selesai"
                     class="filter-pill <?= $status === 'selesai'
? 'active' : ''; ?>">Selesai</button>
           </div>
       </form>
       <div class="info-list">
           <?php if ($resultKegiatan &&
mysqli_num_rows($resultKegiatan) > 0): ?>
                  <?php while ($k =
mysqli_fetch_assoc($resultKegiatan)): ?>
                     <?php
                     $tgl = $k['tanggal'] ?? null;
                     $statusText = statusKegiatan($tgl);
                     $statusClass = 'status-badge--lain';
                     if ($statusText === 'Akan Datang')
                          $statusClass = 'status-badge--akan';
                     elseif ($statusText === 'Selesai')
                          $statusClass = 'status-badge--selesai';
                     elseif ($statusText === 'Berlangsung')
                          $statusClass = 'status-badge--now';
                     ?>
                     <article class="info-card">
                          <div class="info-card-main">
                             <h3 class="info-title"><?=
htmlspecialchars($k['judul']); ?></h3>
                             <?php if (!empty($k['deskripsi'])):
?>
                                   <p class="info-desc">
                                      <?=
nl2br(htmlspecialchars($k['deskripsi'])); ?>
                                   </p>

                                   <?php endif; ?>
                              </div>
                              <div class="info-meta">
                                   <div class="info-meta-row">
                                        <i class="fa-regular
 fa-calendar"></i>
                                        <span><?= $tgl ?
 formatTanggalPendek($tgl) : "-"; ?></span>
                                   </div>
                                   <?php if (!empty($k['lokasi'])): ?>
                                        <div class="info-meta-row">
                                             <i class="fa-solid
 fa-location-dot"></i>
                                             <span><?=
 htmlspecialchars($k['lokasi']); ?></span>
                                        </div>
                                   <?php endif; ?>
                                   <span class="status-badge <?=
 $statusClass; ?>">
                                        <?= $statusText; ?>
                                   </span>
                              </div>
                         </article>
                    <?php endwhile; ?>
               <?php else: ?>
                    <p class="info-empty">
                         Belum ada data kegiatan yang cocok dengan
 filter.
                    </p>
               <?php endif; ?>
           </div>
     </section>
 </body>
 </html>
