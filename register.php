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

$inisial = "";
if (count($parts) > 1) $inisial = strtoupper(substr($parts[0], 0,
1) . substr($parts[1], 0, 1));
else if (count($parts) == 1 && $parts[0] != "") $inisial =
strtoupper(substr($parts[0], 0, 1));
else $inisial = "PL";

date_default_timezone_set('Asia/Jakarta');
$jam = (int) date('H');
if ($jam >= 4 && $jam < 11) $sapaan = "Selamat Pagi";
elseif ($jam >= 11 && $jam < 15) $sapaan = "Selamat Siang";
elseif ($jam >= 15 && $jam < 18) $sapaan = "Selamat Sore";
else $sapaan = "Selamat Malam";

$fotoHeader = "";
if (isset($_SESSION['foto'])) {
    $fotoHeader = $_SESSION['foto'];
}

if ($fotoHeader == "" && $nik != "-" && $nik != "") {
    $qFoto = mysqli_query($koneksi,"SELECT foto FROM penduduk
WHERE NIK = '" . mysqli_real_escape_string($koneksi, $nik) . "'
LIMIT 1");
    if ($qFoto) {
        $r = mysqli_fetch_assoc($qFoto);
        if ($r && isset($r['foto'])) {
             $fotoHeader = $r['foto'];
             if ($fotoHeader != "") {
                 $_SESSION['foto'] = $fotoHeader;
             }
        }
    }
}

$profilNik = "-";
$profilRtRw = "-";

$profilPekerjaan = "-";
$qPenduduk = mysqli_query($koneksi, "SELECT NIK, RT, RW,
pekerjaan FROM penduduk WHERE NIK = '" .
mysqli_real_escape_string($koneksi, $nik) . "' LIMIT 1");

$p = mysqli_fetch_assoc($qPenduduk);

if ($p) $profilNik = isset($p["NIK"]) ? $p["NIK"] : "";
if ($p) $rt = isset($p["RT"]) ? $p["RT"] : "";
if ($p) $rw = isset($p["RW"]) ? $p["RW"] : "";

if ($p) {
    if ($rt != "") $rtVal = $rt;
    else $rtVal = "-";

    if ($rw != "") $rwVal = $rw;
    else $rwVal = "-";

    $profilRtRw = $rtVal . " / " . $rwVal;
}

if ($p) $profilPekerjaan = (isset($p["pekerjaan"]) &&
$p["pekerjaan"] != "") ? $p["pekerjaan"] : "-";

$jumlahKegiatanIkut = 0;
$jumlahDiskusi = 0;
$poinAktivitas = 0;

if ($idUser > 0) {
    $jumlahKegiatanIkut = 0;
    $res = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM
peserta_kegiatan WHERE id_user = $idUser");
    if ($res) {
        $row = mysqli_fetch_assoc($res);
        if ($row && isset($row["total"])) {
            $jumlahKegiatanIkut = $row["total"];
        }
    }
    $jumlahDiskusi = 0;

     $res = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM
diskusi WHERE id_user = $idUser");
     if ($res) {
         $row = mysqli_fetch_assoc($res);
         if ($row && isset($row["total"])) {
             $jumlahDiskusi = $row["total"];
         }
     }
     $poinAktivitas = ($jumlahKegiatanIkut * 10) + ($jumlahDiskusi
* 2);
}

$kegiatanMendatang = [];

$sqlKegiatan = "SELECT id_kegiatan, judul, tanggal, lokasi,
deskripsi, kategori FROM kegiatan ORDER BY tanggal DESC LIMIT
50";
$resKegiatan = mysqli_query($koneksi, $sqlKegiatan);
if ($resKegiatan) {
     while ($row = mysqli_fetch_assoc($resKegiatan)) {
         $kegiatanMendatang[] = $row;
     }
}

function formatTanggalIndo($t) {
     if (!$t) return "";
     $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul',
'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
     $ts = strtotime($t);
     $idxBln = (int) date('n', $ts) - 1;
     return date('d', $ts) . " " . $bulan[$idxBln] . " " .
date('Y', $ts);
}

$imgPathHeader = 'uploads/profile/' . $fotoHeader;
$cbHeader = (!empty($fotoHeader) && file_exists($imgPathHeader))
? filemtime($imgPathHeader) : time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width,
initial-scale=1.0">
   <title>PopuLink | Dashboard</title>
   <link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
   <link rel="stylesheet" href="css/beranda.css">
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
                  <a href="dashboard.php" class="side-menu-link
active">
                      <i class="fa-solid fa-house"></i> Beranda

                   </a>
                   <a href="kegiatan.php" class="side-menu-link">
                      <i class="fa-solid fa-wave-square"></i>
Kegiatan
                   </a>
                   <a href="features/diskusi.php"
class="side-menu-link">
                      <i class="fa-solid fa-comments"></i> Ruang
Diskusi
                   </a>
                   <a href="statistik.php" class="side-menu-link">
                      <i class="fa-solid fa-chart-line"></i>
Statistik
                   </a>
                   <a href="profile.php" class="side-menu-link">
                      <i class="fa-solid fa-id-badge"></i> Profile
Saya
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
                            <div class="small text-muted"><?=
htmlspecialchars($sapaan); ?>,</div>
                            <div class="fw-semibold"><?=
htmlspecialchars($namaLengkap); ?></div>
                      </div>
                   </div>
                   <div class="d-flex align-items-center gap-2">
                      <div class="avatar-circle avatar-header">
                            <?php if (!empty($fotoHeader)): ?>

                              <img src="<?=
htmlspecialchars($imgPathHeader . '?v=' . $cbHeader); ?>"
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
ucfirst($role); ?></span>
                     </div>
                     <a href="logic/logout.php" class="btn btn-sm
btn-dark">Logout</a>
                </div>
             </header>
             <main class="main-content">
                <section class="mb-4">
                     <div class="card card-hero text-white
border-0">
                         <div class="card-body">
                              <h5><?= $sapaan; ?>, <strong><?=
htmlspecialchars($namaLengkap); ?></strong></h5>
                              <p class="small mb-0">Selamat datang
kembali di PopuLink.</p>
                         </div>
                     </div>
                </section>
                <section>
                     <div class="row g-3">
                         <div class="col-lg-8">
                              <div class="card mb-3">
                                   <div class="card-header d-flex
justify-content-between align-items-center">
                                      <span
class="fw-semibold">Kegiatan Terbaru</span>
                                   </div>

                                  <div class="card-body">
                                      <?php if
(!empty($kegiatanMendatang)): ?>
                                           <?php foreach
($kegiatanMendatang as $k): ?>
                                              <div
class="event-item">
                                                    <div
class="event-icon"><i class="fa-regular fa-calendar"></i></div>
                                                    <div
class="flex-grow-1">
                                                        <div
class="fw-semibold small">
                                                             <?=
htmlspecialchars($k['judul']); ?>
                                                        </div>
                                                        <div
class="small text-muted">
                                                             <?=
formatTanggalIndo($k['tanggal']); ?> ·
                                                             <?=
htmlspecialchars($k['lokasi']); ?>
                                                        </div>
                                                    </div>
                                              </div>
                                           <?php endforeach; ?>
                                      <?php else: ?>
                                           <div class="text-muted
small">
                                              Belum ada kegiatan
yang tercatat.
                                           </div>
                                      <?php endif; ?>
                                  </div>
                             </div>
                       </div>
                       <div class="col-lg-4">
                             <div class="card mb-3">
                                  <div class="card-header">

                                      <span
class="fw-semibold">Profil Anda</span>
                                 </div>
                                 <div class="card-body small">
                                      <div class="d-flex
justify-content-between mb-1">
                                          <span
class="text-muted">Nama</span>
                                          <span><?=
htmlspecialchars($namaLengkap); ?></span>
                                      </div>
                                      <div class="d-flex
justify-content-between mb-1">
                                          <span
class="text-muted">Status</span>
                                          <span class="badge
bg-primary-subtle text-primary border">
                                               <?= ucfirst($role);
?>
                                          </span>
                                      </div>
                                      <div class="d-flex
justify-content-between mb-1">
                                          <span
class="text-muted">NIK</span>
                                          <span><?=
htmlspecialchars($profilNik); ?></span>
                                      </div>
                                      <div class="d-flex
justify-content-between mb-1">
                                          <span
class="text-muted">RT / RW</span>
                                          <span><?=
htmlspecialchars($profilRtRw); ?></span>
                                      </div>
                                      <div class="d-flex
justify-content-between mb-3">
                                          <span
class="text-muted">Pekerjaan</span>

                                              <span><?=
htmlspecialchars($profilPekerjaan); ?></span>
                                         </div>
                                         <hr class="my-2">
                                         <div class="small text-muted
mb-1">Ringkasan Aktivitas</div>
                                         <div class="d-flex
flex-column gap-1">
                                              <div class="d-flex
justify-content-between">
                                                  <span>Kegiatan
diikuti</span>
                                                  <span
class="fw-semibold"><?= (int) $jumlahKegiatanIkut; ?></span>
                                              </div>
                                              <div class="d-flex
justify-content-between">
                                                  <span>Diskusi
dibuat</span>
                                                  <span
class="fw-semibold"><?= (int) $jumlahDiskusi; ?></span>
                                              </div>
                                              <div class="d-flex
justify-content-between">
                                                  <span>Poin
aktivitas</span>
                                                  <span
class="fw-semibold"><?= (int) $poinAktivitas; ?></span>
                                              </div>
                                         </div>
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
           const menu = document.getElementById("sideMenu");
           const overlay =
 document.getElementById("sideMenuOverlay");

           document.getElementById("menuToggle").onclick = () => {
                menu.classList.add("open");
                overlay.classList.add("show");
           };
           document.getElementById("sideMenuClose").onclick =
 closeMenu;
           overlay.onclick = closeMenu;

           function closeMenu() {
                menu.classList.remove("open");
                overlay.classList.remove("show");
           }
     </script>
 </body>
 </html>
