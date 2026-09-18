<?php
 require "config/koneksi.php";
 require "logic/auth.php";

$namaLengkap = "Warga";
if (isset($_SESSION["nama"])) $namaLengkap = $_SESSION["nama"];

$role = "warga";
if (isset($_SESSION["role"])) $role = $_SESSION["role"];

$nik = "-";
if (isset($_SESSION["nik"])) $nik = $_SESSION["nik"];

$idUser = 0;
if (isset($_SESSION["id_user"])) $idUser = (int)
$_SESSION["id_user"];

$isAdmin = false;
if ($role === "admin") $isAdmin = true;

$statusLabel = "Warga";
if ($isAdmin) $statusLabel = "Admin";

$parts = preg_split('/\s+/', trim($namaLengkap));
$inisial = "PL";
if (count($parts) > 1) $inisial = strtoupper(substr($parts[0], 0,
1) . substr($parts[1], 0, 1));
elseif (count($parts) == 1 && $parts[0] != "") $inisial =
strtoupper(substr($parts[0], 0, 1));

date_default_timezone_set('Asia/Jakarta');

$fotoHeader = "";
if (isset($_SESSION['foto'])) $fotoHeader = $_SESSION['foto'];

if ($fotoHeader === "" && $nik !== "-" && $nik !== "") {
   $qFoto = mysqli_query($koneksi, "SELECT foto FROM penduduk
WHERE NIK = '" . mysqli_real_escape_string($koneksi, $nik) . "'
LIMIT 1");
   if ($qFoto) {
       $rowF = mysqli_fetch_assoc($qFoto);
       if ($rowF && isset($rowF['foto'])) {

              $fotoHeader = $rowF['foto'];
        }
        if ($fotoHeader !== "") {
              $_SESSION['foto'] = $fotoHeader;
        }
    }
}

$imgRelPathHeader = 'uploads/profile/' . $fotoHeader;
if ($fotoHeader !== "" && file_exists($imgRelPathHeader)) {
    $cbHeader = filemtime($imgRelPathHeader);
} else {
    $cbHeader = time();
}

$totalPenduduk = 0;
$res = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM
penduduk");
if ($res && $row = mysqli_fetch_assoc($res)) {
    $totalPenduduk = (int) $row['total'];
}

$totalKegiatan = 0;
$res = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM
kegiatan");
if ($res && $row = mysqli_fetch_assoc($res)) {
    $totalKegiatan = (int) $row['total'];
}

$totalWargaUser = 0;
$res = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM user
WHERE role='user'");
if ($res && $row = mysqli_fetch_assoc($res)) {
    $totalWargaUser = (int) $row['total'];
}

$wargaIkut = 0;
$res = mysqli_query($koneksi, "SELECT COUNT(DISTINCT id_user) AS
total FROM peserta_kegiatan");

if ($res && $row = mysqli_fetch_assoc($res)) {
    $wargaIkut = (int) $row['total'];
}

if ($totalWargaUser > 0) {
    $persenPartisipasi = round($wargaIkut / $totalWargaUser *
100);
} else {
    $persenPartisipasi = 0;
}

$currentYear = date('Y');
$bulanLabel = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul',
'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$kegiatanPerBulan = array_fill(1, 12, 0);

$sql = "SELECT MONTH(tanggal) AS bln, COUNT(*) AS total FROM
kegiatan WHERE YEAR(tanggal) = $currentYear GROUP BY
MONTH(tanggal) ORDER BY MONTH(tanggal)";
$res = mysqli_query($koneksi, $sql);
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $b = (int) $row['bln'];
        if ($b >= 1 && $b <= 12) {
              $kegiatanPerBulan[$b] = (int) $row['total'];
        }
    }
}

$chartKegiatanPerBulan = [];
for ($i = 1; $i <= 12; $i++) {
    $chartKegiatanPerBulan[] = $kegiatanPerBulan[$i];
}

$topKegiatanLabels = [];
$topKegiatanData = [];

$sqlTop = "SELECT k.judul, COUNT(pk.id_peserta) AS total_peserta
FROM kegiatan k LEFT JOIN peserta_kegiatan pk

           ON pk.id_kegiatan = k.id_kegiatan GROUP BY
k.id_kegiatan ORDER BY total_peserta DESC LIMIT 5";
$resTop = mysqli_query($koneksi, $sqlTop);
if ($resTop) {
     while ($row = mysqli_fetch_assoc($resTop)) {
         $topKegiatanLabels[] = $row['judul'];
         $topKegiatanData[]     = (int) $row['total_peserta'];
     }
}

$aktif = 0;
$sedang = 0;
$kurangAktif = 0;

$sqlAktif = "SELECT u.id_user, COALESCE(pk.jml_keg, 0) AS
jml_keg, COALESCE(dk.jml_diskusi, 0) AS jml_diskusi FROM user u
               LEFT JOIN (SELECT id_user, COUNT(*) AS jml_keg FROM
peserta_kegiatan GROUP BY id_user) pk ON pk.id_user = u.id_user
               LEFT JOIN (SELECT id_user, COUNT(*) AS jml_diskusi
FROM diskusi GROUP BY id_user) dk ON dk.id_user = u.id_user WHERE
u.role = 'user'";
$resAktif = mysqli_query($koneksi, $sqlAktif);
if ($resAktif) {
     while ($row = mysqli_fetch_assoc($resAktif)) {
         $poin = ((int) $row['jml_keg'] * 10) + ((int)
$row['jml_diskusi'] * 2);
         if ($poin >= 30) $aktif++;
         elseif ($poin >= 10) $sedang++;
         else $kurangAktif++;
     }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width,
initial-scale=1.0">

   <title>Statistik | PopuLink</title>
   <link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
   <link rel="stylesheet" href="css/kegiatan.css">
   <link rel="stylesheet" href="css/statistik.css">
   <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
   <div class="app-layout">
          <nav class="side-menu" id="sideMenu">
             <div class="side-menu-header">
                 <div class="d-flex align-items-center gap-2">
                      <div class="brand-logo rounded-circle d-flex
align-items-center justify-content-center">
                          <img src="assets/img/populink_logo2.png"
alt="populink logo" style="width: 30px; height: 40px;">
                      </div>
                      <div class="side-menu-title">
                          <div class="fw-semibold">PopuLink</div>
                          <small>Menu Navigasi</small>
                      </div>
                 </div>
                 <button class="btn-close-menu"
id="sideMenuClose">
                      <i class="fa-solid fa-xmark"></i>
                 </button>
             </div>
             <div class="side-menu-body">
                 <a href="dashboard.php" class="side-menu-link">
                      <i class="fa-solid fa-house"></i>
                      <span>Beranda</span>
                 </a>
                 <a href="kegiatan.php" class="side-menu-link">
                      <i class="fa-solid fa-wave-square"></i>
                      <span>Kegiatan</span>

                </a>
                <a href="features/diskusi.php"
class="side-menu-link">
                    <i class="fa-solid fa-comments"></i>
                    <span>Ruang Diskusi</span>
                </a>
                <a href="statistik.php" class="side-menu-link
active">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Statistik</span>
                </a>
                <a href="profile.php" class="side-menu-link">
                    <i class="fa-solid fa-id-badge"></i>
                    <span>Profil Saya</span>
                </a>
           </div>
       </nav>
       <div class="side-menu-overlay"
id="sideMenuOverlay"></div>
       <div class="main-area">
           <header class="topbar d-flex align-items-center
justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn-icon" id="menuToggle">
                          <i class="fa-solid fa-bars"></i>
                    </button>
                    <div>
                          <div class="small text-muted">Selamat
datang,</div>
                          <div class="fw-semibold"><?=
htmlspecialchars($namaLengkap); ?></div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-circle avatar-header">
                          <?php if ($fotoHeader !== "" &&
file_exists($imgRelPathHeader)): ?>
                              <img src="<?=
htmlspecialchars($imgRelPathHeader . '?v=' . $cbHeader); ?>"

alt="Foto Profil">
                          <?php else: ?>
                              <span><?= htmlspecialchars($inisial);
?></span>
                          <?php endif; ?>
                     </div>
                     <div class="d-none d-sm-flex flex-column">
                          <span class="small fw-semibold"><?=
htmlspecialchars($namaLengkap); ?></span>
                          <span class="badge-role"><?=
htmlspecialchars(ucfirst($role)); ?></span>
                     </div>
                     <a href="logic/logout.php" class="btn btn-sm
btn-dark ms-1">Logout</a>
                 </div>
            </header>
            <main class="main-content">
                 <section class="page-header">
                     <h2>Statistik</h2>
                     <p>Data dan visualisasi aktivitas warga.</p>
                 </section>
                 <section class="mb-4">
                     <div class="row g-3">
                          <div class="col-md-4">
                              <div class="stat-card
stat-card--primary">
                                   <div class="stat-title">Total
Penduduk</div>
                                   <div class="stat-value"><?=
$totalPenduduk; ?></div>
                                   <div class="stat-desc">Warga
terdata dalam sistem</div>
                                   <div class="stat-icon">
                                       <i class="fa-solid
fa-users"></i>
                                   </div>
                              </div>
                          </div>
                          <div class="col-md-4">

                               <div class="stat-card
stat-card--green">
                                    <div class="stat-title">Total
Kegiatan</div>
                                    <div class="stat-value"><?=
$totalKegiatan; ?></div>
                                    <div class="stat-desc">Kegiatan
sosial yang tercatat</div>
                                    <div class="stat-icon">
                                        <i class="fa-regular
fa-calendar-days"></i>
                                    </div>
                               </div>
                         </div>
                         <div class="col-md-4">
                               <div class="stat-card
stat-card--orange">
                                    <div class="stat-title">Tingkat
Partisipasi</div>
                                    <div class="stat-value"><?=
$persenPartisipasi; ?>%</div>
                                    <div class="stat-desc">Warga yang
pernah ikut kegiatan</div>
                                    <div class="stat-icon">
                                        <i class="fa-solid
fa-chart-line"></i>
                                    </div>
                               </div>
                         </div>
                      </div>
                 </section>
                 <section>
                      <div class="row g-3">
                         <div class="col-lg-7">
                               <div class="chart-card">
                                    <div class="chart-header d-flex
justify-content-between align-items-center">
                                        <div>
                                             <h6 class="mb-0">Kegiatan

per Bulan (<?= $currentYear; ?>)</h6>
                                          <small
class="text-muted">Jumlah kegiatan yang tercatat setiap
bulan</small>
                                      </div>
                                 </div>
                                 <div class="chart-body">
                                      <canvas
id="chartKegiatanPerBulan"></canvas>
                                 </div>
                            </div>
                       </div>
                       <div class="col-lg-5">
                            <div class="chart-card">
                                 <div class="chart-header d-flex
justify-content-between align-items-center">
                                      <div>
                                          <h6 class="mb-0">Tingkat
Keaktifan Warga</h6>
                                          <small
class="text-muted">Kategori berdasarkan partisipasi dan
diskusi</small>
                                      </div>
                                 </div>
                                 <div class="chart-body">
                                      <canvas
id="chartKeaktifan"></canvas>
                                 </div>
                            </div>
                       </div>
                   </div>
                   <div class="row g-3 mt-1">
                       <div class="col-lg-12">
                            <div class="chart-card">
                                 <div class="chart-header d-flex
justify-content-between align-items-center">
                                      <div>
                                          <h6 class="mb-0">Top 5
Kegiatan Paling Sering Diikuti</h6>

                                              <small
class="text-muted">Berdasarkan jumlah peserta kegiatan</small>
                                         </div>
                                     </div>
                                     <div class="chart-body">
                                         <canvas
id="chartTopKegiatan"></canvas>
                                     </div>
                                </div>
                          </div>
                       </div>
                </section>
             </main>
       </div>
   </div>
   <script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstr
ap.bundle.min.js"></script>
   <script>
       const sideMenu = document.getElementById('sideMenu');
       const sideMenuOverlay =
document.getElementById('sideMenuOverlay');
       const menuToggle = document.getElementById('menuToggle');
       const sideMenuClose =
document.getElementById('sideMenuClose');

       function openMenu() {
             sideMenu.classList.add('open');
             sideMenuOverlay.classList.add('show');
       }

       function closeMenu() {
             sideMenu.classList.remove('open');
             sideMenuOverlay.classList.remove('show');
       }

       menuToggle.addEventListener('click', function (e) {
             e.stopPropagation();
             if (sideMenu.classList.contains('open')) {

                 closeMenu();
             } else {
                 openMenu();
             }
       });

       sideMenuClose.addEventListener('click', function (e) {
             e.stopPropagation();
             closeMenu();
       });

       sideMenuOverlay.addEventListener('click', function () {
             closeMenu();
       });

       const bulanLabels             = <?= json_encode($bulanLabel);
?>;
       const kegiatanPerBulan        = <?=
json_encode($chartKegiatanPerBulan); ?>;
       const topKegiatanLabels = <?=
json_encode($topKegiatanLabels); ?>;
       const topKegiatanData         = <?=
json_encode($topKegiatanData); ?>;
       const keaktifanLabels         = ["Aktif", "Sedang", "Kurang
Aktif"];
       const keaktifanData           = [<?= (int) $aktif; ?>, <?=
(int) $sedang; ?>, <?= (int) $kurangAktif; ?>];

       const ctxKegiatan =
document.getElementById('chartKegiatanPerBulan').getContext('2d')
;
       new Chart(ctxKegiatan, {
             type: 'line',
             data: {
                 labels: bulanLabels,
                 datasets: [{
                       label: 'Jumlah Kegiatan',
                       data: kegiatanPerBulan,
                       tension: 0.35,

                       borderWidth: 2,
                       pointRadius: 4
                  }]
             },
             options: {
                  responsive: true,
                  maintainAspectRatio: false,
                  scales: {
                       y: {
                           beginAtZero: true,
                           ticks: { precision: 0 }
                       }
                  }
             }
       });

       const ctxTop =
document.getElementById('chartTopKegiatan').getContext('2d');
       new Chart(ctxTop, {
             type: 'bar',
             data: {
                  labels: topKegiatanLabels,
                  datasets: [{
                       label: 'Jumlah Peserta',
                       data: topKegiatanData,
                       borderWidth: 1
                  }]
             },
             options: {
                  indexAxis: 'y',
                  responsive: true,
                  maintainAspectRatio: false,
                  scales: {
                       x: {
                           beginAtZero: true,
                           ticks: { precision: 0 }
                       }
                  }
             }

           });

           const ctxPie =
 document.getElementById('chartKeaktifan').getContext('2d');
           new Chart(ctxPie, {
                 type: 'pie',
                 data: {
                      labels: keaktifanLabels,
                      datasets: [{
                            data: keaktifanData
                      }]
                 },
                 options: {
                      responsive: true,
                      maintainAspectRatio: false
                 }
           });
     </script>
 </body>
 </html>
