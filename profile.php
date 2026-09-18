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
$successMsg = "";
$errorMsg = "";
$userData = [
"id_user" => $idUser,
"NIK" => $nik,
"email" => "",
"pass" => ""
];
$pendudukData = [
"nama" => $namaLengkap,
"alamat" => "",
"rt" => "",

"rw" => "",
"pekerjaan" => "",
"pendidikan" => "",
"minat" => "",
"foto" => ""
];
$adaPenduduk = false;
if ($idUser > 0) {
$qUser = mysqli_query($koneksi, "SELECT * FROM user WHERE
id_user = $idUser LIMIT 1");
if ($u = mysqli_fetch_assoc($qUser)) {
$userData["NIK"] = $u["NIK"];
$userData["email"] = $u["email"];
$userData["pass"] = $u["pass"];
}
}
$nikForQuery = mysqli_real_escape_string($koneksi,
$userData["NIK"]);
$qPenduduk = mysqli_query($koneksi, "SELECT * FROM penduduk
WHERE NIK = '$nikForQuery' LIMIT 1");
if ($qPenduduk && $p = mysqli_fetch_assoc($qPenduduk)) {
$adaPenduduk = true;
$pendudukData["nama"] = isset($p["nama"]) ? $p["nama"]
: $pendudukData["nama"];
$pendudukData["alamat"] = isset($p["alamat"]) ?
$p["alamat"] : "";
$pendudukData["rt"] = isset($p["RT"]) ? $p["RT"] :
"";
$pendudukData["rw"] = isset($p["RW"]) ? $p["RW"] :
"";
$pendudukData["pekerjaan"] = isset($p["pekerjaan"]) ?
$p["pekerjaan"] : "";
$pendudukData["pendidikan"] = isset($p["pendidikan"]) ?
$p["pendidikan"] : "";
$pendudukData["minat"] = isset($p["minat"]) ?

$p["minat"] : "";
$pendudukData["foto"] = isset($p["foto"]) ? $p["foto"]
: "";
if ($pendudukData["foto"] !== "") {
$_SESSION['foto'] = $pendudukData["foto"];
}
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
if (isset($_POST["aksi"]) && $_POST["aksi"] ===
"update_biodata") {
$nama = mysqli_real_escape_string($koneksi,
$_POST["nama"]);
$email = mysqli_real_escape_string($koneksi,
$_POST["email"]);
$alamat = mysqli_real_escape_string($koneksi,
$_POST["alamat"]);
$rt = mysqli_real_escape_string($koneksi,
$_POST["rt"]);
$rw = mysqli_real_escape_string($koneksi,
$_POST["rw"]);
$pekerjaan = mysqli_real_escape_string($koneksi,
$_POST["pekerjaan"]);
$pendidikan = mysqli_real_escape_string($koneksi,
$_POST["pendidikan"]);
$minat = mysqli_real_escape_string($koneksi,
$_POST["minat"]);
$nikEsc = mysqli_real_escape_string($koneksi,
$userData["NIK"]);
if ($adaPenduduk) {
$sqlPenduduk = "UPDATE penduduk SET nama='$nama',
alamat='$alamat', RT='$rt', RW='$rw', pekerjaan='$pekerjaan',
pendidikan='$pendidikan',
minat='$minat' WHERE NIK='$nikEsc' LIMIT 1";
} else {
$fotoExisting = mysqli_real_escape_string($koneksi,
$pendudukData["foto"]);
$sqlPenduduk = "INSERT INTO penduduk (NIK, nama,
alamat, RT, RW, pekerjaan, pendidikan, minat, foto)

VALUES('$nikEsc', '$nama',
'$alamat', '$rt', '$rw', '$pekerjaan', '$pendidikan', '$minat',
'$fotoExisting')";
}
$idUserEsc = (int) $userData["id_user"];
$sqlUser = "UPDATE user SET email='$email' WHERE
id_user = $idUserEsc LIMIT 1";
if (mysqli_query($koneksi, $sqlPenduduk) &&
mysqli_query($koneksi, $sqlUser)) {
$successMsg = "Biodata berhasil
diperbarui.";
$adaPenduduk = true;
$pendudukData["nama"] = $nama;
$pendudukData["alamat"] = $alamat;
$pendudukData["rt"] = $rt;
$pendudukData["rw"] = $rw;
$pendudukData["pekerjaan"] = $pekerjaan;
$pendudukData["pendidikan"] = $pendidikan;
$pendudukData["minat"] = $minat;
$userData["email"] = $email;
$_SESSION["nama"] = $nama;
$namaLengkap = $nama;
$parts = preg_split('/\s+/',
trim($namaLengkap));
if (count($parts) > 1) {
$inisial = strtoupper(substr($parts[0], 0, 1) .
substr($parts[1], 0, 1));
} elseif (count($parts) == 1 && $parts[0] !== "") {
$inisial = strtoupper(substr($parts[0], 0, 1));
} else {
$inisial = "PL";
}
} else {
$errorMsg = "Gagal memperbarui biodata.";
}
}
if (isset($_POST["aksi"]) && $_POST["aksi"] ===
"update_password") {

$passwordLama = "";
$passwordBaru = "";
$passwordKonf = "";
if (isset($_POST["password_lama"])) {
$passwordLama = $_POST["password_lama"];
}
if (isset($_POST["password_baru"])) {
$passwordBaru = $_POST["password_baru"];
}
if (isset($_POST["password_konfirmasi"])) {
$passwordKonf = $_POST["password_konfirmasi"];
}
if ($passwordLama === "" || $passwordBaru === "" ||
$passwordKonf === "") {
$errorMsg = "Semua field password wajib diisi.";
} elseif ($passwordLama !== $userData["pass"]) {
$errorMsg = "Password lama tidak sesuai.";
} elseif ($passwordBaru !== $passwordKonf) {
$errorMsg = "Password baru dan konfirmasi tidak
sama.";
} else {
$passEsc = mysqli_real_escape_string($koneksi,
$passwordBaru);
$idUserEsc = (int) $userData["id_user"];
$sqlPass = "UPDATE user SET pass='$passEsc' WHERE
id_user = $idUserEsc LIMIT 1";
if (mysqli_query($koneksi, $sqlPass)) {
$successMsg = "Password berhasil
diperbarui.";
$userData["pass"] = $passwordBaru;
} else {
$errorMsg = "Gagal memperbarui password.";
}
}
}
if (isset($_POST["aksi"]) && $_POST["aksi"] ===
"update_foto") {
if (isset($_FILES["foto"]) && $_FILES["foto"]["error"]

=== UPLOAD_ERR_OK) {
$tmpName = $_FILES["foto"]["tmp_name"];
$fileName = $_FILES["foto"]["name"];
$fileSize = $_FILES["foto"]["size"];
$ext = strtolower(pathinfo($fileName,
PATHINFO_EXTENSION));
$allowedExt = ["jpg", "jpeg", "png"];
if (!in_array($ext, $allowedExt)) {
$errorMsg = "Format foto harus JPG atau PNG.";
} elseif ($fileSize > 2 * 1024 * 1024) {
$errorMsg = "Ukuran foto maksimal 2MB.";
} else {
$newName = "profile_" . $userData["id_user"] .
"_" . time() . "." . $ext;
$uploadDir = "uploads/profile/";
if (!is_dir($uploadDir)) {
mkdir($uploadDir, 0777, true);
}
if (move_uploaded_file($tmpName, $uploadDir .
$newName)) {
if ($pendudukData["foto"] !== "" &&
file_exists($uploadDir . $pendudukData["foto"])) {
@unlink($uploadDir .
$pendudukData["foto"]);
}
$nikEsc =
mysqli_real_escape_string($koneksi, $userData["NIK"]);
$fotoEsc =
mysqli_real_escape_string($koneksi, $newName);
if ($adaPenduduk) {
$sqlFoto = "UPDATE penduduk SET
foto='$fotoEsc' WHERE NIK='$nikEsc' LIMIT 1";
} else {
$sqlFoto = "INSERT INTO penduduk(NIK,
nama, alamat, RT, RW, pekerjaan, pendidikan, minat, foto)
VALUES('$nikEsc',
'" .
mysqli_real_escape_string($koneksi, $pendudukData["nama"]) . "',

'" .
mysqli_real_escape_string($koneksi, $pendudukData["alamat"]) .
"',
'" .
mysqli_real_escape_string($koneksi, $pendudukData["rt"]) . "',
'" .
mysqli_real_escape_string($koneksi, $pendudukData["rw"]) . "',
'" .
mysqli_real_escape_string($koneksi, $pendudukData["pekerjaan"]) .
"',
'" .
mysqli_real_escape_string($koneksi, $pendudukData["pendidikan"])
. "',
'" .
mysqli_real_escape_string($koneksi, $pendudukData["minat"]) . "',
'$fotoEsc')";
}
if (mysqli_query($koneksi, $sqlFoto)) {
$pendudukData["foto"] = $newName;
$adaPenduduk = true;
$successMsg = "Foto profil
berhasil diperbarui.";
$_SESSION['foto'] = $newName;
} else {
$errorMsg = "Foto berhasil diunggah,
namun gagal menyimpan ke database.";
}
} else {
$errorMsg = "Gagal mengunggah foto.";
}
}
} else {
$errorMsg = "Pilih foto terlebih dahulu.";
}
}
if (isset($_POST["aksi"]) && $_POST["aksi"] === "hapus_foto")
{
$uploadDir = "uploads/profile/";
$currentFoto = '';

if (isset($pendudukData["foto"])){
$currentFoto = $pendudukData["foto"];
}
if (!empty($currentFoto)) {
$filePath = $uploadDir . $currentFoto;
if (file_exists($filePath)) {
@unlink($filePath);
}
$nikEsc = mysqli_real_escape_string($koneksi,
$userData["NIK"]);
if ($adaPenduduk) {
$sqlHapusFoto = "UPDATE penduduk SET foto=''
WHERE NIK='$nikEsc' LIMIT 1";
mysqli_query($koneksi, $sqlHapusFoto);
}
$pendudukData["foto"] = '';
unset($_SESSION['foto']);
$successMsg = "Foto profil berhasil dihapus.";
} else {
$errorMsg = "Tidak ada foto profil yang dapat
dihapus.";
}
}
}
$riwayatKegiatan = [];
if ($idUser > 0) {
$sqlHist = "SELECT k.judul, k.tanggal FROM peserta_kegiatan
pk JOIN kegiatan k ON k.id_kegiatan = pk.id_kegiatan WHERE
pk.id_user = $idUser ORDER BY k.tanggal DESC";
$resHist = mysqli_query($koneksi, $sqlHist);
while ($row = mysqli_fetch_assoc($resHist)) {
$tgl = $row["tanggal"];
$today = date('Y-m-d');
if ($tgl > $today) $status = "Mendatang";
elseif ($tgl < $today) $status = "Selesai";
else $status = "Berlangsung";
$riwayatKegiatan[] = [

"judul" => $row["judul"],
"tanggal" => $tgl,
"status" => $status
];
}
}
function formatTanggalIndo($tanggal) {
if (!$tanggal) return "";
$bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul',
'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$ts = strtotime($tanggal);
return date('d', $ts) . ' ' . $bulan[(int) date('n', $ts) -
1] . ' ' . date('Y', $ts);
}
$fotoHeader = '';
if (isset($_SESSION['foto']) && $_SESSION['foto'] !== '')
$fotoHeader = $_SESSION['foto'];
else if (isset($pendudukData['foto']) && $pendudukData['foto']
!== '') $fotoHeader = $pendudukData['foto'];
$imgPathHeader = 'uploads/profile/' . $fotoHeader;
if ($fotoHeader !== '' && file_exists($imgPathHeader)) $cbHeader
= filemtime($imgPathHeader);
else $cbHeader = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,
initial-scale=1.0">
<title>PopuLink | Profil Saya</title>
<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet"

href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link rel="stylesheet" href="css/beranda.css">
<link rel="stylesheet" href="css/profile.css">
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
<a href="profile.php" class="side-menu-link
active">
<i class="fa-solid fa-id-badge"></i> Profil
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
<span class="avatar-initial"><?=
htmlspecialchars($inisial); ?></span>
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
<section class="mb-3">
<h4 class="mb-1">Profil Saya</h4>
<p class="text-muted small mb-0">Kelola
informasi pribadi dan pengaturan akun Anda.</p>
</section>
<?php if ($successMsg): ?>
<div class="alert alert-success py-2"><?=
htmlspecialchars($successMsg); ?></div>
<?php endif; ?>
<?php if ($errorMsg): ?>
<div class="alert alert-danger py-2"><?=
htmlspecialchars($errorMsg); ?></div>
<?php endif; ?>
<section>
<div class="row g-3">
<div class="col-lg-4">
<div class="card profile-left-card">
<div class="card-body
text-center">
<form method="post"
enctype="multipart/form-data">
<input type="hidden"
name="aksi" value="update_foto">
<div class="d-flex
flex-column align-items-center mb-3">
<div
class="profile-avatar mb-2">
<?php
$fotoProfile = '';
if

(isset($pendudukData['foto']) && $pendudukData['foto'] !== '') {
$fotoProfile
= $pendudukData['foto'];
}
$imgPathProfile =
'uploads/profile/' . $fotoProfile;
if ($fotoProfile
!== '' && file_exists($imgPathProfile)) {
$cbProfile =
filemtime($imgPathProfile);
} else {
$cbProfile =
time();
}
if ($fotoProfile
!== '' && file_exists($imgPathProfile)) {
echo '<img
src="' . htmlspecialchars($imgPathProfile . '?v=' . $cbProfile) .
'" alt="Foto Profil">';
} else {
echo '<span>'
. htmlspecialchars($inisial) . '</span>';
}?>
<label
class="btn-upload">
<i
class="fa-solid fa-camera"></i>
<input
type="file" name="foto" accept="image/*" style="display:none"
onchange="this.form.submit()">
</label>
</div>
<div
class="fw-semibold"><?= htmlspecialchars($pendudukData["nama"]);
?>
</div>
<span
class="badge
bg-dark mt-1 text-white small"><?= ucfirst($role); ?></span>

</div>
<div class="text-start
small mb-1">
<i class="fa-regular
fa-envelope me-1"></i>
<span><?=
htmlspecialchars($userData["email"] ?: '-'); ?></span>
</div>
<div class="text-start
small mb-1">
<i class="fa-solid
fa-location-dot me-1"></i>
<span>RT <?=
htmlspecialchars($pendudukData["rt"] ?: '-'); ?> / RW
<?=
htmlspecialchars($pendudukData["rw"] ?: '-'); ?></span>
</div>
<div class="text-start
small mb-1">
<i class="fa-solid
fa-briefcase me-1"></i>
<span><?=
htmlspecialchars($pendudukData["pekerjaan"] ?: '-'); ?></span>
</div>
<div class="text-start
small mb-3">
<i class="fa-solid
fa-user-graduate me-1"></i>
<span><?=
htmlspecialchars($pendudukData["pendidikan"] ?: '-'); ?></span>
</div>
</form>
<?php if ($fotoProfile !== ''
&& file_exists($imgPathProfile)): ?>
<form method="post"
onsubmit="return confirm('Yakin ingin menghapus foto profil?');">
<input type="hidden"
name="aksi" value="hapus_foto">
<button type="submit"

class="btn btn-sm btn-outline-danger w-100 mb-3">
Hapus Foto Profil
</button>
</form>
<?php endif; ?>
<div class="text-start mt-3">
<div class="fw-semibold
small mb-1">Minat Saya</div>
<?php $minatString = "";
if
(isset($pendudukData["minat"])) {
$minatString =
$pendudukData["minat"];
}
$tags =
array_filter(array_map('trim', explode(',', $minatString)));
if (empty($tags)): ?>
<span class="text-muted
small">Belum diisi</span>
<?php else:
foreach ($tags as $tag):
?>
<span
class="tag-minat"><?= htmlspecialchars($tag); ?></span>
<?php endforeach;
endif; ?>
</div>
</div>
</div>
</div>
<div class="col-lg-8">
<div class="card">
<div class="card-body">
<ul class="nav nav-pills mb-3
tab-pill" role="tablist">
<li class="nav-item"
role="presentation">
<button
class="nav-link active" data-bs-toggle="tab"

data-bs-target="#tab-biodata" type="button" role="tab">
Biodata
</button>
</li>
<li class="nav-item"
role="presentation">
<button
class="nav-link" data-bs-toggle="tab"
data-bs-target="#tab-riwayat"
type="button"
role="tab">
Riwayat Kegiatan
</button>
</li>
</ul>
<div class="tab-content">
<div class="tab-pane fade
show active" id="tab-biodata" role="tabpanel">
<h6 class="mb-3">Data
Pribadi</h6>
<form method="post">
<input
type="hidden" name="aksi" value="update_biodata">
<div class="row
g-2 small">
<div
class="col-md-6">
<label
class="form-label small">NIK</label>
<input
type="text" class="form-control form-control-sm"
value="<?= htmlspecialchars($userData["NIK"]); ?>" disabled>
</div>
<div
class="col-md-6">
<label
class="form-label small">Nama Lengkap</label>

<input
type="text" name="nama"
class="form-control form-control-sm"
value="<?= htmlspecialchars($pendudukData["nama"]); ?>"
required>
</div>
<div
class="col-md-6">
<label
class="form-label small">Email</label>
<input
type="email" name="email"
class="form-control form-control-sm"
value="<?= htmlspecialchars($userData["email"]); ?>">
</div>
<div
class="col-md-6">
<label
class="form-label small">Pekerjaan</label>
<input
type="text" name="pekerjaan"
class="form-control form-control-sm"
value="<?= htmlspecialchars($pendudukData["pekerjaan"]); ?>">
</div>
<div
class="col-md-6">
<label
class="form-label small">RT</label>
<input
type="text" name="rt"
class="form-control form-control-sm"

value="<?= htmlspecialchars($pendudukData["rt"]); ?>">
</div>
<div
class="col-md-6">
<label
class="form-label small">RW</label>
<input
type="text" name="rw"
class="form-control form-control-sm"
value="<?= htmlspecialchars($pendudukData["rw"]); ?>">
</div>
<div
class="col-md-6">
<label
class="form-label small">Pendidikan</label>
<input
type="text" name="pendidikan"
class="form-control form-control-sm"
value="<?= htmlspecialchars($pendudukData["pendidikan"]); ?>">
</div>
<div
class="col-md-6">
<label
class="form-label small">Alamat</label>
<input
type="text" name="alamat"
class="form-control form-control-sm"
value="<?= htmlspecialchars($pendudukData["alamat"]); ?>">
</div>
<div
class="col-12">
<label

class="form-label small">Minat (pisahkan dengan
koma)</label>
<input
type="text" name="minat"
class="form-control form-control-sm"
value="<?= htmlspecialchars($pendudukData["minat"]); ?>">
</div>
<div
class="col-12 d-flex justify-content-end mt-2">
<button
type="submit" class="btn btn-sm btn-dark">
Simpan Perubahan
</button>
</div>
</div>
</form>
<hr class="my-4">
<h6
class="mb-2">Keamanan</h6>
<form method="post"
class="small">
<input
type="hidden" name="aksi" value="update_password">
<div class="row
g-2">
<div
class="col-md-4">
<label
class="form-label small">Password Lama</label>
<input
type="password" name="password_lama"
class="form-control form-control-sm" required>
</div>
<div

class="col-md-4">
<label
class="form-label small">Password Baru</label>
<input
type="password" name="password_baru"
class="form-control form-control-sm" required>
</div>
<div
class="col-md-4">
<label
class="form-label small">Konfirmasi Password</label>
<input
type="password" name="password_konfirmasi"
class="form-control form-control-sm" required>
</div>
<div
class="col-12 d-flex justify-content-end mt-2">
<button
type="submit" class="btn btn-sm btn-outline-secondary">
Ubah
Password
</button>
</div>
</div>
</form>
</div>
<div class="tab-pane
fade" id="tab-riwayat" role="tabpanel">
<h6
class="mb-3">Riwayat Kegiatan</h6>
<?php if
(empty($riwayatKegiatan)): ?>
<p class="small
text-muted mb-0">Anda belum mengikuti kegiatan apa pun.</p>
<?php else: ?>
<?php foreach
($riwayatKegiatan as $rk): ?>

<div
class="timeline-item">
<div
class="timeline-icon">
<i
class="fa-regular fa-calendar-days"></i>
</div>
<div
class="flex-grow-1">
<div
class="small fw-semibold">
<?= htmlspecialchars($rk["judul"]); ?>
</div>
<div
class="small text-muted">
<?= formatTanggalIndo($rk["tanggal"]); ?>
</div>
</div>
<div>
<?php
$cls
= "bg-secondary text-white";
if
($rk["status"] === "Mendatang") {
$cls = "bg-dark text-white";
}
elseif ($rk["status"] === "Berlangsung") {
$cls = "bg-success text-white";
}
?>
<span
class="badge badge-status <?= $cls; ?>">

<?= htmlspecialchars($rk["status"]); ?>
</span>
</div>
</div>
<?php endforeach;
?>
<?php endif; ?>
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
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
