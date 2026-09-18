<?php
 require "../config/koneksi.php";
 require "../logic/auth.php";

 $namaLengkap = "Warga";
 if (isset($_SESSION["nama"])) {
     $namaLengkap = $_SESSION["nama"];
 }

 $role = "warga";
 if (isset($_SESSION["role"])) {
     $role = $_SESSION["role"];
 }

 $nik = "-";
 if (isset($_SESSION["nik"])) {
     $nik = $_SESSION["nik"];
 }

 $idUser = 0;
 if (isset($_SESSION["id_user"])) {
     $idUser = (int) $_SESSION["id_user"];
 }

 $isAdmin = ($role === "admin");

 $parts = preg_split('/\s+/', trim($namaLengkap));
 $inisial = "PL";
 if (count($parts) > 1) {
     $inisial = strtoupper(substr($parts[0], 0, 1) .
 substr($parts[1], 0, 1));
 } elseif (count($parts) === 1 && $parts[0] !== "") {
     $inisial = strtoupper(substr($parts[0], 0, 1));
 }

date_default_timezone_set('Asia/Jakarta');

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

$imgRelPathHeader = '../uploads/profile/' . $fotoHeader;
if ($fotoHeader !== "" && file_exists($imgRelPathHeader)) {
    $cbHeader = filemtime($imgRelPathHeader);
} else {
    $cbHeader = time();
}

function formatTanggalDiskusi($tanggal)
{
    if (!$tanggal) return '';
    $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul',
'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $ts = strtotime($tanggal);
    $idx = (int) date('n', $ts) - 1;
    return date('d', $ts) . ' ' . $bulan[$idx] . ' ' . date('Y',
$ts) . ' • ' . date('H:i', $ts);

}
if ($idUser > 0) {
    if (isset($_POST['aksi'])) {
          $aksi = $_POST['aksi'];
          if ($aksi === 'tambah_diskusi') {
             $isi = "";
             if (isset($_POST['isi_diskusi'])) {
                 $isi = trim($_POST['isi_diskusi']);
             }
             if ($isi !== '') {
                 $isiEsc = mysqli_real_escape_string($koneksi,
$isi);
                 mysqli_query($koneksi,"INSERT INTO diskusi
(id_user, isi_post, tanggal_post) VALUES ($idUser, '$isiEsc',
NOW())");
             }
          } elseif ($aksi === 'tambah_komentar') {
             $idPost = 0;
             if (isset($_POST['id_post'])) {
                 $idPost = (int) $_POST['id_post'];
             }
             $isiK = "";
             if (isset($_POST['isi_komentar'])) {
                 $isiK = trim($_POST['isi_komentar']);
             }
             if ($idPost > 0 && $isiK !== '') {
                 $isiKEsc = mysqli_real_escape_string($koneksi,
$isiK);
                 mysqli_query($koneksi,"INSERT INTO komentar
(id_post, id_user, isi_komentar) VALUES ($idPost, $idUser,
'$isiKEsc')");
             }
          } elseif ($aksi === 'hapus_komentar') {
             $idKomentar = 0;
             if (isset($_POST['id_komentar'])) {
                 $idKomentar = (int) $_POST['id_komentar'];
             }
             if ($idKomentar > 0) {
                 if($isAdmin) {

                      $sqlDel = "DELETE from komentar where
id_komentar = $idKomentar LIMIT 1";
                  } else {
                      $sqlDel = "DELETE from komentar where
id_komentar = $idKomentar and id_user = $idUser LIMIT 1";
                  }
                  mysqli_query($koneksi, $sqlDel);
              }
         }
     }
}

$totalDiskusi = 0;
$diskusiHariIni = 0;
$totalKomentar = 0;

$res = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM
diskusi");
if ($res && $row = mysqli_fetch_assoc($res)) {
     $totalDiskusi = (int) $row['jml'];
}

$res = mysqli_query(
     $koneksi,
     "SELECT COUNT(*) AS jml FROM diskusi WHERE DATE(tanggal_post)
= CURDATE()"
);
if ($res && $row = mysqli_fetch_assoc($res)) {
     $diskusiHariIni = (int) $row['jml'];
}

$res = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM
komentar");
if ($res && $row = mysqli_fetch_assoc($res)) {
     $totalKomentar = (int) $row['jml'];
}

$daftarDiskusi = [];

$sqlDiskusi = "SELECT d.id_post, d.id_user, d.isi_post,
d.tanggal_post, u.NIK, u.role AS user_role, p.nama,
                (SELECT COUNT(*) FROM komentar k WHERE k.id_post =
d.id_post) AS jml_komentar FROM diskusi d
                JOIN user u ON u.id_user = d.id_user LEFT JOIN
penduduk p ON p.NIK = u.NIK ORDER BY d.tanggal_post DESC";
$qDiskusi = mysqli_query($koneksi, $sqlDiskusi);
if ($qDiskusi) {
     while ($row = mysqli_fetch_assoc($qDiskusi)) {
          $daftarDiskusi[] = $row;
     }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width,
initial-scale=1.0">
     <title>Ruang Diskusi | PopuLink</title>
     <link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
     <link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
     <link rel="stylesheet" href="../css/beranda.css">
</head>
<body>
     <div class="app-layout">
          <nav class="side-menu" id="sideMenu">
             <div class="side-menu-header">
                  <div class="d-flex align-items-center gap-2">
                      <div class="brand-logo rounded-circle d-flex
align-items-center justify-content-center">
                         <span class="fw-bold">P</span>
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
                   <a href="../dashboard.php"
class="side-menu-link">
                      <i class="fa-solid fa-house"></i> Beranda
                   </a>
                   <a href="../kegiatan.php" class="side-menu-link">
                      <i class="fa-solid fa-wave-square"></i>
Kegiatan
                   </a>
                   <a href="diskusi.php" class="side-menu-link
active">
                      <i class="fa-solid fa-comments"></i> Ruang
Diskusi
                   </a>
                   <a href="../statistik.php"
class="side-menu-link">
                      <i class="fa-solid fa-chart-line"></i>
Statistik
                   </a>
                   <a href="../profile.php" class="side-menu-link">
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
                     <a href="../logic/logout.php" class="btn
btn-sm btn-dark">Logout</a>
                </div>
            </header>
            <main class="main-content">
                <section class="mb-4">
                     <div class="card card-discussion">
                         <div class="card-body">
                              <div class="discussion-header">
                                   <div>

                                     <h2>Ruang Diskusi</h2>
                                     <p>Forum komunikasi dan
berbagi informasi antar warga.</p>
                                  </div>
                                  <button
class="btn-diskusi-primary" type="button"
data-bs-toggle="collapse" data-bs-target="#formDiskusiBaru">
                                     <i class="fa-solid fa-plus
me-1"></i> Buat Diskusi
                                  </button>
                           </div>
                           <div class="discussion-stats">
                                  <div
class="discussion-stat-card">
                                     <div class="stat-text">
                                            <div class="label">Total
Diskusi</div>
                                            <div class="value"><?=
(int) $totalDiskusi; ?></div>
                                     </div>
                                     <div class="icon">
                                            <i class="fa-regular
fa-message"></i>
                                     </div>
                                  </div>
                                  <div
class="discussion-stat-card">
                                     <div class="stat-text">
                                            <div class="label">Hari
Ini</div>
                                            <div class="value"><?=
(int) $diskusiHariIni; ?></div>
                                     </div>
                                     <div class="icon">
                                            <i class="fa-regular
fa-clock"></i>
                                     </div>
                                  </div>
                                  <div

class="discussion-stat-card">
                                       <div class="stat-text">
                                             <div class="label">Total
Komentar</div>
                                             <div class="value"><?=
(int) $totalKomentar; ?></div>
                                       </div>
                                       <div class="icon">
                                             <i class="fa-regular
fa-comments"></i>
                                       </div>
                                    </div>
                              </div>

                          </div>
                    </div>
                 </section>
                 <section class="mb-3">
                    <div class="collapse show"
id="formDiskusiBaru">
                          <div class="card">
                              <div class="card-body">
                                    <form method="post"
class="discussion-form">
                                       <input type="hidden"
name="aksi" value="tambah_diskusi">
                                       <label class="form-label
small mb-1">Tulis diskusi baru</label>
                                       <textarea
                                             name="isi_diskusi"
                                             class="form-control mb-2"
                                             placeholder="Apa yang
ingin Anda diskusikan?"
                                       ></textarea>
                                       <div class="d-flex
justify-content-end">
                                             <button type="submit"
class="btn-diskusi-primary">
                                                Kirim Diskusi

                                             </button>
                                       </div>
                                   </form>
                              </div>
                        </div>
                     </div>
               </section>
               <section>
                     <?php if (empty($daftarDiskusi)): ?>
                        <div class="card">
                              <div class="card-body text-center
text-muted small">
                                   Belum ada diskusi. Jadilah yang
pertama memulai percakapan!
                              </div>
                        </div>
                     <?php else: ?>
                        <?php foreach ($daftarDiskusi as $d): ?>
                              <?php
                              if ($d['user_role'] === 'admin') {
                                   $namaPenulis = 'Admin';
                              } else {
                                   $namaPenulis = 'Pengguna';
                                   if (!empty($d['nama'])) {
                                       $namaPenulis = $d['nama'];
                                   }
                              }
                              $iniPenulis =
strtoupper(substr($namaPenulis, 0, 2));
                              ?>
                              <div class="discussion-item">
                                   <div
class="discussion-item-header">
                                       <div class="avatar-circle
small-avatar d-flex align-items-center justify-content-center">
                                             <span><?=
htmlspecialchars($iniPenulis); ?></span>
                                       </div>
                                       <div class="flex-grow-1">

                                           <div
class="discussion-author">
                                                <?=
htmlspecialchars($namaPenulis); ?>
                                           </div>
                                           <div
class="discussion-meta">
                                                <?=
formatTanggalDiskusi($d['tanggal_post']); ?> ·
                                                <?= (int)
$d['jml_komentar']; ?> komentar
                                           </div>
                                           <div
class="discussion-body">
                                                <?=
nl2br(htmlspecialchars($d['isi_post'])); ?>
                                           </div>
                                       </div>
                                  </div>
                                  <?php
                                  $komentar = [];
                                  $idPostKomentar = (int)
$d['id_post'];
                                  $sqlK = "SELECT     k.id_komentar,
k.isi_komentar,   k.id_user, u.NIK, u.role AS user_role, p.nama
                                           FROM komentar k JOIN
user u ON u.id_user = k.id_user LEFT JOIN penduduk p ON p.NIK =
u.NIK
                                           WHERE k.id_post =
$idPostKomentar ORDER BY k.id_komentar ASC";
                                  $qK = mysqli_query($koneksi,
$sqlK);
                                  if ($qK) {
                                       while ($rowK =
mysqli_fetch_assoc($qK)) {
                                           $komentar[] = $rowK;
                                       }
                                  }
                                  ?>

                                 <?php if (!empty($komentar)): ?>
                                      <div class="comment-list">
                                         <?php foreach ($komentar
as $kmt): ?>
                                             <?php
                                             if ($kmt['user_role']
=== 'admin') {
                                                  $namaKmt =
'Admin';
                                             } else {
                                                  $namaKmt =
'Pengguna';
                                                  if
(!empty($kmt['nama'])) {
                                                       $namaKmt =
$kmt['nama'];
                                                  }
                                             }
                                             $iniKmt =
strtoupper(substr($namaKmt, 0, 2));
                                             ?>
                                             <div
class="comment-item">
                                                  <div
class="avatar-circle tiny-avatar d-flex align-items-center
justify-content-center">
                                                       <span><?=
htmlspecialchars($iniKmt); ?></span>
                                                  </div>
                                                  <div
class="comment-main">
                                                       <div
class="comment-author">
                                                           <?=
htmlspecialchars($namaKmt); ?>
                                                       </div>
                                                       <div
class="comment-text">
                                                           <?=

nl2br(htmlspecialchars($kmt['isi_komentar'])); ?>
                                                       </div>
                                                       <div
class="comment-actions">
                                                             <?php if
($isAdmin || (int)$kmt['id_user'] === $idUser): ?>
                                                                <form
method="post" class="d-inline">

<input type="hidden" name="aksi" value="hapus_komentar">

<input type="hidden" name="id_komentar" value="<?= (int)
$kmt['id_komentar']; ?>">

<button type="submit" class="btn btn-link p-0 m-0 align-baseline
text-danger small">

Hapus

</button>

</form>
                                                             <?php
endif; ?>
                                                       </div>
                                                    </div>
                                              </div>
                                         <?php endforeach; ?>
                                     </div>
                                  <?php endif; ?>
                                  <div class="mt-2">
                                     <form method="post"
class="comment-form">
                                         <input type="hidden"
name="aksi" value="tambah_komentar">
                                         <input type="hidden"
name="id_post" value="<?= (int) $d['id_post']; ?>">
                                         <textarea
                                              name="isi_komentar"

                                                   class="form-control
form-control-sm mb-1"
                                                   placeholder="Tulis
komentar..."
                                             ></textarea>
                                             <div class="d-flex
justify-content-end">
                                                   <button type="submit"
class="btn btn-sm btn-outline-secondary">
                                                      Kirim
                                                   </button>
                                             </div>
                                         </form>
                                    </div>
                              </div>
                          <?php endforeach; ?>
                       <?php endif; ?>
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
       const menuToggle = document.getElementById("menuToggle");
       const menuClose =
document.getElementById("sideMenuClose");

       function openMenu() {
             menu.classList.add("open");
             overlay.classList.add("show");
       }

       function closeMenu() {
             menu.classList.remove("open");

                 overlay.classList.remove("show");
           }

           menuToggle.addEventListener("click", function (e) {
                 e.stopPropagation();
                 if (menu.classList.contains("open")) {
                     closeMenu();
                 } else {
                     openMenu();
                 }
           });

           menuClose.addEventListener("click", function (e) {
                 e.stopPropagation();
                 closeMenu();
           });

           overlay.addEventListener("click", function () {
                 closeMenu();
           });
     </script>
 </body>
 </html>
