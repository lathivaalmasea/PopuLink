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

if ($fotoHeader === "" && $nik !== "-" && $nik !== "") {
    $sqlFoto = "SELECT foto FROM penduduk WHERE NIK = '" .
mysqli_real_escape_string($koneksi, $nik) . "' LIMIT 1";

    $qFoto = mysqli_query($koneksi, $sqlFoto);
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

$imgPathHeader = 'uploads/profile/' . $fotoHeader;
if ($fotoHeader !== "" && file_exists($imgPathHeader)) {
    $cbHeader = filemtime($imgPathHeader);
} else {
    $cbHeader = time();
}

function formatTanggalIndo($tanggal) {
    if (!$tanggal) return '';
    $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul',
'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $ts = strtotime($tanggal);
    $idx = (int) date('n', $ts) - 1;
    return date('d', $ts) . ' ' . $bulan[$idx] . ' ' . date('Y',
$ts);
}

if ($idUser > 0) {
    if (isset($_POST['aksi'])) {
        $aksi = $_POST['aksi'];
        if ($aksi === 'join_kegiatan') {
            $id_kegiatan = (int) $_POST['id_kegiatan'];

            $q_cek = "SELECT id_peserta FROM peserta_kegiatan
                     WHERE id_user = '$idUser' AND id_kegiatan =
'$id_kegiatan'";
            $cek = mysqli_query($koneksi, $q_cek);

            if ($cek && mysqli_num_rows($cek) < 1) {
                  $sql_join = "INSERT INTO peserta_kegiatan
(id_user, id_kegiatan)
                               VALUES ('$idUser', '$id_kegiatan')";
                  mysqli_query($koneksi, $sql_join);
            }
        } elseif ($aksi === 'batal_kegiatan') {
            $id_kegiatan = (int) $_POST['id_kegiatan'];
            $sql_batal = "DELETE FROM peserta_kegiatan
                           WHERE id_user = '$idUser' AND
id_kegiatan = '$id_kegiatan'";
            mysqli_query($koneksi, $sql_batal);
        }
    }
}
if ($isAdmin) {
    if (isset($_POST['aksi'])) {
        $aksi = $_POST['aksi'];
        if ($aksi === 'tambah_kegiatan') {
            $judul       = mysqli_real_escape_string($koneksi,
$_POST['judul']);
            $tanggal     = mysqli_real_escape_string($koneksi,
$_POST['tanggal']);
            $lokasi      = mysqli_real_escape_string($koneksi,
$_POST['lokasi']);
            $deskripsi = mysqli_real_escape_string($koneksi,
$_POST['deskripsi']);
            $kategori    = mysqli_real_escape_string($koneksi,
$_POST['kategori']);

            $sql = "INSERT INTO kegiatan (judul, tanggal, lokasi,
deskripsi, kategori)
                      VALUES ('$judul', '$tanggal', '$lokasi',
'$deskripsi', '$kategori')";
            mysqli_query($koneksi, $sql);
        } elseif ($aksi === 'update_kegiatan') {
            $idKeg = 0;
            if (isset($_POST['id_kegiatan'])) {

               $idKeg = (int) $_POST['id_kegiatan'];
           }
           if ($idKeg > 0) {
               $judul       = mysqli_real_escape_string($koneksi,
$_POST['judul']);
               $tanggal     = mysqli_real_escape_string($koneksi,
$_POST['tanggal']);
               $lokasi      = mysqli_real_escape_string($koneksi,
$_POST['lokasi']);
               $deskripsi = mysqli_real_escape_string($koneksi,
$_POST['deskripsi']);
               $kategori    = mysqli_real_escape_string($koneksi,
$_POST['kategori']);

               $lat = '';
               $lng = '';
               if (isset($_POST['latitude'])) {
                      $lat = mysqli_real_escape_string($koneksi,
$_POST['latitude']);
               }
               if (isset($_POST['longitude'])) {
                      $lng = mysqli_real_escape_string($koneksi,
$_POST['longitude']);
               }
               $sqlUpdate = "UPDATE kegiatan SET judul='$judul',
tanggal='$tanggal', lokasi='$lokasi', deskripsi='$deskripsi',
kategori='$kategori'";
               if ($lat !== '' || $lng !== '') {
                      $sqlUpdate .= ", latitude='$lat',
longitude='$lng'";
               }
               $sqlUpdate .= " WHERE id_kegiatan = '$idKeg'";
               mysqli_query($koneksi, $sqlUpdate);
               header("Location: kegiatan.php");
               exit;
           }
       } elseif ($aksi === 'admin_tambah_peserta') {
           $idKegAdmin = 0;
           if (isset($_POST['id_kegiatan'])) {

                 $idKegAdmin = (int) $_POST['id_kegiatan'];
             }
             $keyword = '';
             if (isset($_POST['nik_peserta'])) {
                 $keyword = trim($_POST['nik_peserta']);
             }
             if ($idKegAdmin > 0 && $keyword !== '') {
                 $keywordEsc = mysqli_real_escape_string($koneksi,
$keyword);
                 $sql_cari = "SELECT u.id_user    FROM user u LEFT
JOIN penduduk p ON p.NIK = u.NIK WHERE u.NIK = '$keywordEsc' OR
p.nama LIKE '%$keywordEsc%' LIMIT 1";
                 $qUser = mysqli_query($koneksi, $sql_cari);
                 if ($qUser && mysqli_num_rows($qUser) > 0) {
                     $u = mysqli_fetch_assoc($qUser);
                     $idUserPeserta = (int) $u['id_user'];
                     $cek = mysqli_query($koneksi,"SELECT
id_peserta FROM peserta_kegiatan WHERE id_user = '$idUserPeserta'
AND id_kegiatan = '$idKegAdmin'");
                     if ($cek && mysqli_num_rows($cek) === 0) {
                         mysqli_query($koneksi,"INSERT INTO
peserta_kegiatan (id_user, id_kegiatan) VALUES ('$idUserPeserta',
'$idKegAdmin')");
                     }
                 }
             }
       } elseif ($aksi === 'admin_hapus_peserta') {
             $idPeserta = 0;
             if (isset($_POST['id_peserta'])) {
                 $idPeserta = (int) $_POST['id_peserta'];
             }
             if ($idPeserta > 0) {
                 mysqli_query($koneksi, "DELETE FROM
peserta_kegiatan WHERE id_peserta = '$idPeserta'");
             }
       }
   }
   if (isset($_GET['hapus_kegiatan'])) {
       $idKeg = (int) $_GET['hapus_kegiatan'];

          if ($idKeg > 0) {
              mysqli_query($koneksi, "DELETE FROM peserta_kegiatan
WHERE id_kegiatan = '$idKeg'");
              mysqli_query($koneksi, "DELETE FROM kegiatan WHERE
id_kegiatan = '$idKeg'");
          }
    }
}

$filterKegiatan = 'semua';
if (isset($_GET['filter_kegiatan'])) {
    $filterKegiatan = $_GET['filter_kegiatan'];
}

$searchQuery = '';
if (isset($_GET['q'])) {
    $searchQuery = trim($_GET['q']);
}

$whereFilter = "";
if ($filterKegiatan === 'saya' && $idUser > 0) {
    $whereFilter = "WHERE k.id_kegiatan IN (
          SELECT id_kegiatan FROM peserta_kegiatan WHERE id_user =
$idUser
    )";
} elseif ($isAdmin && $filterKegiatan === 'peserta') {
    $whereFilter = "";
}

$sqlKegiatanList = "SELECT k.*, (SELECT COUNT(*) FROM
peserta_kegiatan pk WHERE pk.id_kegiatan = k.id_kegiatan)
                     AS jumlah_peserta, EXISTS(SELECT 1 FROM
peserta_kegiatan pk2 WHERE pk2.id_kegiatan = k.id_kegiatan
                     AND pk2.id_user = $idUser) AS sudah_ikut FROM
kegiatan k $whereFilter ORDER BY k.tanggal ASC";
$kegiatanList = mysqli_query($koneksi, $sqlKegiatanList);

$statTotalKegiatan = 0;
$resStat = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM

kegiatan");
if ($resStat && $row = mysqli_fetch_assoc($resStat)) {
     $statTotalKegiatan = (int) $row['total'];
}

$jumlahKegiatanIkut = 0;
if ($idUser > 0) {
     $res = mysqli_query($koneksi,"SELECT COUNT(*) AS total FROM
peserta_kegiatan WHERE id_user = $idUser");
     if ($res && $row = mysqli_fetch_assoc($res)) {
         $jumlahKegiatanIkut = (int) $row['total'];
     }
}

$dataEdit = null;
if ($isAdmin && isset($_GET['edit_kegiatan'])) {
     $idEdit = (int) $_GET['edit_kegiatan'];
     if ($idEdit > 0) {
         $qEdit = mysqli_query($koneksi,"SELECT * FROM kegiatan
WHERE id_kegiatan = $idEdit LIMIT 1");
         if ($qEdit && mysqli_num_rows($qEdit) === 1) {
              $dataEdit = mysqli_fetch_assoc($qEdit);
         }
     }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width,
initial-scale=1.0">
     <title>Kegiatan | PopuLink</title>
     <link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
     <link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/c

ss/all.min.css" />
   <link rel="stylesheet" href="css/kegiatan.css">
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
                 <a href="kegiatan.php" class="side-menu-link
active">
                      <i class="fa-solid fa-wave-square"></i>
                      <span>Kegiatan</span>
                 </a>
                 <a href="features/diskusi.php"
class="side-menu-link">
                      <i class="fa-solid fa-comments"></i>
                      <span>Ruang Diskusi</span>
                 </a>
                 <a href="statistik.php" class="side-menu-link">

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
                         <div class="small text-muted"><?=
htmlspecialchars($sapaan); ?>,</div>
                         <div class="fw-semibold"><?=
htmlspecialchars($namaLengkap); ?></div>
                     </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                     <div class="avatar-circle avatar-header">
                         <?php if ($fotoHeader !== "" &&
file_exists($imgPathHeader)) : ?>
                              <img src="<?=
htmlspecialchars($imgPathHeader . '?v=' . $cbHeader); ?>"
alt="Foto Profil">
                         <?php else : ?>
                              <span><?= htmlspecialchars($inisial);
?></span>
                         <?php endif; ?>
                     </div>
                     <div class="d-none d-sm-flex flex-column">
                         <span class="small fw-semibold"><?=

htmlspecialchars($namaLengkap); ?></span>
                          <span class="badge-role"><?=
htmlspecialchars($statusLabel); ?></span>
                    </div>
                    <a href="logic/logout.php" class="btn btn-sm
btn-dark ms-1">Logout</a>
                 </div>
            </header>
            <main class="main-content">
                 <section class="page-header">
                    <h2>Kegiatan</h2>
                    <p>Ikuti berbagai kegiatan di lingkungan
Anda.</p>
                 </section>
                 <section class="mb-4">
                    <div class="row g-3">
                          <div class="col-md-4 col-lg-3">
                              <div class="stat-card">
                                    <div class="stat-title">Total
Kegiatan</div>
                                    <div class="stat-value"><?= (int)
$statTotalKegiatan; ?></div>
                                    <div class="stat-icon">
                                       <i class="fa-regular
fa-calendar-days"></i>
                                    </div>
                              </div>
                          </div>
                          <?php if (!$isAdmin): ?>
                              <div class="col-md-4 col-lg-3">
                                    <div class="stat-card">
                                       <div
class="stat-title">Kegiatan Diikuti</div>
                                       <div class="stat-value"><?=
(int) $jumlahKegiatanIkut; ?></div>
                                       <div class="stat-icon">
                                             <i class="fa-solid
fa-check-double"></i>
                                       </div>

                                   </div>
                             </div>
                       <?php endif; ?>
                   </div>
               </section>
               <section class="mb-5">
                   <div class="d-flex justify-content-between
align-items-center mb-3">
                       <div>
                             <h5
class="section-title">Kegiatan</h5>
                             <p class="section-subtitle">Ikuti
berbagai kegiatan sosial di lingkungan Anda.</p>
                       </div>
                       <?php if ($isAdmin): ?>
                             <button class="btn btn-sm
btn-primary-grad" data-bs-toggle="collapse"

data-bs-target="#formTambahKegiatan">
                                   <i class="fa-solid fa-plus
me-1"></i> Tambah Kegiatan
                             </button>
                       <?php endif; ?>
                   </div>
                   <?php if ($isAdmin): ?>
                       <div class="collapse mb-3"
id="formTambahKegiatan">
                             <div class="card card-form">
                                   <div class="card-body">
                                      <form method="post"
class="row g-2">
                                            <input type="hidden"
name="aksi" value="tambah_kegiatan">
                                            <div class="col-md-4">
                                               <label
class="form-label small">Judul</label>
                                               <input type="text"
name="judul" class="form-control form-control-sm" required>
                                            </div>

                                             <div class="col-md-3">
                                                 <label
class="form-label small">Tanggal</label>
                                                 <input type="date"
name="tanggal" class="form-control form-control-sm" required>
                                             </div>
                                             <div class="col-md-5">
                                                 <label
class="form-label small">Lokasi</label>
                                                 <input type="text"
name="lokasi" class="form-control form-control-sm">
                                             </div>
                                             <div class="col-md-4">
                                                 <label
class="form-label small">Kategori</label>
                                                 <input type="text"
name="kategori" class="form-control form-control-sm"
placeholder="Olahraga / Lingkungan / dll">
                                             </div>
                                             <div class="col-12">
                                                 <label
class="form-label small">Deskripsi</label>
                                                 <textarea
name="deskripsi" rows="2" class="form-control
form-control-sm"></textarea>
                                             </div>
                                             <div class="col-12 d-flex
justify-content-end">
                                                 <button type="submit"
class="btn btn-sm btn-primary-grad">
                                                      Simpan Kegiatan
                                                 </button>
                                             </div>
                                       </form>
                                    </div>
                           </div>
                        </div>
                   <?php endif; ?>
                   <div class="filter-bar mb-3">

                       <form method="get" class="d-flex
flex-wrap gap-2">
                             <div class="flex-grow-1">
                                  <div class="search-box">
                                      <i class="fa-solid
fa-magnifying-glass"></i>
                                      <input type="text"
class="form-control" placeholder="Cari kegiatan..." name="q"
value="<?= htmlspecialchars($searchQuery); ?>">
                                  </div>
                             </div>
                             <div class="filter-pills">
                                  <button type="submit"
name="filter_kegiatan" value="semua" class="pill <?=
($filterKegiatan === 'semua' ? 'active' : ''); ?>">
                                      Semua Kegiatan
                                  </button>
                                  <?php if ($isAdmin): ?>
                                      <button type="submit"
name="filter_kegiatan" value="peserta" class="pill <?=
($filterKegiatan === 'peserta' ? 'active' : ''); ?>">
                                           Peserta Kegiatan
                                      </button>
                                  <?php else: ?>
                                      <button type="submit"
name="filter_kegiatan" value="saya" class="pill <?=
($filterKegiatan === 'saya' ? 'active' : ''); ?>">
                                           Kegiatan Saya
                                      </button>
                                  <?php endif; ?>
                             </div>
                       </form>
                    </div>
                    <div class="row g-3">
                       <?php if ($kegiatanList &&
mysqli_num_rows($kegiatanList) > 0): ?>
                             <?php
                             $qSearch = strtolower($searchQuery);
                             ?>

                             <?php while ($k =
mysqli_fetch_assoc($kegiatanList)): ?>
                                  <?php
                                  if ($qSearch !== '') {
                                       $haystack = strtolower(
                                            (isset($k['judul']) ?
$k['judul'] : '') . ' ' .
                                            (isset($k['deskripsi']) ?
$k['deskripsi'] : '') . ' ' .
                                            (isset($k['lokasi']) ?
$k['lokasi'] : '')
                                       );
                                       if (strpos($haystack,
$qSearch) === false) {
                                            continue;
                                       }
                                  }
                                  $sudahIkut = ((int)
$k['sudah_ikut'] === 1);
                                  $colClass = 'col-12';
                                  ?>
                                  <div class="<?= $colClass; ?>"
id="keg-<?= (int) $k['id_kegiatan']; ?>">
                                       <div class="card
activity-card h-100">
                                            <div class="card-body">
                                               <div class="d-flex
justify-content-between align-items-start mb-2">
                                                   <div>
                                                        <div
class="activity-title">
                                                           <?=
htmlspecialchars($k['judul']); ?>
                                                        </div>
                                                        <div
class="activity-subtitle">
                                                           <?=
htmlspecialchars($k['deskripsi']); ?>
                                                        </div>

                                               </div>
                                               <?php if
(!empty($k['kategori'])): ?>
                                                    <span
class="badge badge-tag">
                                                          <?=
htmlspecialchars($k['kategori']); ?>
                                                    </span>
                                               <?php endif; ?>
                                           </div>
                                           <div
class="activity-meta">
                                               <div>
                                                    <i
class="fa-regular fa-calendar"></i>
                                                    <?=
formatTanggalIndo($k['tanggal']); ?>
                                               </div>
                                               <?php if
(!empty($k['lokasi'])): ?>
                                                    <div>
                                                          <i
class="fa-solid fa-location-dot"></i>
                                                          <?=
htmlspecialchars($k['lokasi']); ?>
                                                    </div>
                                               <?php endif; ?>
                                           </div>
                                           <div class="d-flex
justify-content-between align-items-center mt-3">
                                               <div class="small
text-muted">
                                                    <i
class="fa-regular fa-user"></i>
                                                    <?= (int)
$k['jumlah_peserta']; ?> peserta
                                               </div>
                                               <div
class="d-flex gap-2">

                                                    <?php if
($isAdmin && $filterKegiatan !== 'peserta'): ?>
                                                          <a
href="?edit_kegiatan=<?= (int) $k['id_kegiatan'];
?>#editKegiatan"

class="btn btn-sm btn-outline-secondary">Edit</a>
                                                          <a
href="?hapus_kegiatan=<?= (int) $k['id_kegiatan']; ?>"

class="btn btn-sm btn-outline-danger"

onclick="return confirm('Hapus kegiatan ini?')">Hapus</a>
                                                    <?php elseif
(!$isAdmin): ?>
                                                          <?php if
($sudahIkut): ?>
                                                               <form
method="post">

<input type="hidden" name="aksi" value="batal_kegiatan">

<input type="hidden" name="id_kegiatan"

value="<?= (int) $k['id_kegiatan']; ?>">

<button type="submit" class="btn btn-sm btn-secondary-grad">

Batal

</button>

</form>
                                                          <?php
else: ?>
                                                               <form
method="post">

<input type="hidden" name="aksi" value="join_kegiatan">

<input type="hidden" name="id_kegiatan"

value="<?= (int) $k['id_kegiatan']; ?>">

<button type="submit" class="btn btn-sm btn-primary-grad">

Ikuti Kegiatan

</button>

</form>
                                                        <?php
endif; ?>
                                                    <?php endif;
?>
                                               </div>
                                           </div>
                                           <?php if ($isAdmin &&
$filterKegiatan === 'peserta'): ?>
                                               <div class="mt-3
pt-2 border-top small">
                                                    <div
class="d-flex justify-content-between align-items-center mb-2">
                                                        <span
class="fw-semibold">Peserta</span>
                                                        <form
method="post" class="d-flex gap-1">

<input type="hidden" name="aksi" value="admin_tambah_peserta">

<input type="hidden" name="id_kegiatan"

value="<?= (int) $k['id_kegiatan']; ?>">

<input type="text" name="nik_peserta"

class="form-control form-control-sm"

placeholder="Cari user (NIK / Nama)">

<button type="submit" class="btn btn-sm btn-primary-grad">

Cari

</button>
                                                          </form>
                                                     </div>
                                                     <?php
                                                     $qPeserta =
mysqli_query($koneksi,"SELECT pk.id_peserta, u.NIK, p.nama FROM
peserta_kegiatan pk JOIN user u ON u.id_user = pk.id_user

LEFT JOIN penduduk p ON p.NIK = u.NIK   WHERE pk.id_kegiatan = " .
(int) $k['id_kegiatan'] . " ORDER BY p.nama ASC");
                                                     ?>
                                                     <?php if
($qPeserta && mysqli_num_rows($qPeserta) > 0): ?>
                                                          <?php
while ($ps = mysqli_fetch_assoc($qPeserta)): ?>
                                                              <div
class="d-flex justify-content-between align-items-center mb-1">

<div>

<div><?= htmlspecialchars(isset($ps['nama']) ? $ps['nama'] :
'Tanpa Nama'); ?></div>

<div class="text-muted">

NIK: <?= htmlspecialchars(isset($ps['NIK']) ? $ps['NIK'] : '-');
?>

</div>

</div>

<div class="d-flex align-items-start gap-1">

<form method="post">

<input type="hidden" name="aksi" value="admin_hapus_peserta">

<input type="hidden" name="id_peserta"

value="<?= (int) $ps['id_peserta']; ?>">

<button type="submit" class="btn btn-sm btn-outline-danger">

Hapus

</button>

</form>

</div>

</div>
                                                            <?php
endwhile; ?>
                                                      <?php else:
?>
                                                            <div
class="text-muted">Belum ada peserta.</div>
                                                      <?php endif;
?>
                                                   </div>
                                              <?php endif; ?>
                                          </div>
                                    </div>
                                 </div>
                            <?php endwhile; ?>
                       <?php endif; ?>
                   </div>
                   <?php if ($isAdmin && $dataEdit !== null): ?>
                       <div id="editKegiatan" class="mt-4">
                            <h6 class="mb-2">Edit Kegiatan</h6>

                           <div class="card card-form mb-3">
                                 <div class="card-body">
                                    <form method="post"
class="row g-2">
                                          <input type="hidden"
name="aksi" value="update_kegiatan">
                                          <input type="hidden"
name="id_kegiatan"
                                                   value="<?= (int)
$dataEdit['id_kegiatan']; ?>">
                                          <div class="col-md-4">
                                              <label
class="form-label small">Judul</label>
                                              <input type="text"
name="judul" class="form-control form-control-sm"
                                                       value="<?=
htmlspecialchars($dataEdit['judul']); ?>" required>
                                          </div>
                                          <div class="col-md-3">
                                              <label
class="form-label small">Tanggal</label>
                                              <input type="date"
name="tanggal" class="form-control form-control-sm"
                                                       value="<?=
htmlspecialchars($dataEdit['tanggal']); ?>" required>
                                          </div>
                                          <div class="col-md-5">
                                              <label
class="form-label small">Lokasi</label>
                                              <input type="text"
name="lokasi" class="form-control form-control-sm"
                                                       value="<?=
htmlspecialchars($dataEdit['lokasi']); ?>">
                                          </div>
                                          <div class="col-md-4">
                                              <label
class="form-label small">Kategori</label>
                                              <input type="text"
name="kategori" class="form-control form-control-sm"

                                                            value="<?=
htmlspecialchars($dataEdit['kategori']); ?>">
                                             </div>
                                             <div class="col-12">
                                                   <label
class="form-label small">Deskripsi</label>
                                                   <textarea
name="deskripsi" rows="2"

class="form-control form-control-sm"><?=
htmlspecialchars($dataEdit['deskripsi']); ?></textarea>
                                             </div>
                                             <div class="col-12 d-flex
justify-content-end gap-2">
                                                   <a
href="kegiatan.php" class="btn btn-sm btn-outline-secondary">
                                                        Batal
                                                   </a>
                                                   <button type="submit"
class="btn btn-sm btn-pink">
                                                        Update Kegiatan
                                                   </button>
                                             </div>
                                         </form>
                                    </div>
                              </div>
                          </div>
                       <?php endif; ?>
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
     </script>
</body>
</html>
