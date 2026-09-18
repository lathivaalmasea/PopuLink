<?php
 session_start();
 require "config/koneksi.php";

 $success = "";
 $error = "";

 if (isset($_POST["register"])) {
      $role = "warga";
      $nik = $_POST["nik"];
      $email = $_POST["email"];
      $nama = $_POST["nama"];
      $nama_panggilan = $_POST["nama_panggilan"];
      $password = $_POST["password"];
      $konfirmasi = $_POST["konfirmasi_password"];
      $alamat = $_POST["alamat"];
      $rt = $_POST["rt"];
      $rw = $_POST["rw"];
      $pekerjaan = $_POST["pekerjaan"];
      $pendidikan = $_POST["pendidikan"];

      if (isset($_POST["minat"]) && is_array($_POST["minat"])) {
           $minat = implode(", ", $_POST["minat"]);
      } else {
           $minat = "";
      }

      $dbRole = "user";

      if ($password !== $konfirmasi) {

         $error = "Konfirmasi password tidak sama.";
     } else {
         $cekNik = mysqli_query($koneksi, "SELECT * FROM `user`
WHERE NIK='$nik'");
         if (mysqli_num_rows($cekNik) > 0) {
             $error = "NIK sudah terdaftar!";
         } else {
             $queryPenduduk = "INSERT INTO penduduk(NIK, nama,
nama_panggilan, alamat, RT, RW, pekerjaan, pendidikan, minat,
pass)
                               VALUES('$nik', '$nama',
'$nama_panggilan', '$alamat', '$rt', '$rw', '$pekerjaan',
'$pendidikan', '$minat', '$password')";
             $queryUser = "INSERT INTO `user` (NIK, email, pass,
role)
                           VALUES ('$nik', '$email', '$password',
'$dbRole')";
             $okPenduduk = mysqli_query($koneksi, $queryPenduduk);
             $okUser = mysqli_query($koneksi, $queryUser);
             if ($okPenduduk && $okUser) {
                  $success = "Akun berhasil dibuat! Silakan
login.";
             } else {
                  $error = "Terjadi kesalahan: " .
mysqli_error($koneksi);
             }
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
     <title>Daftar | PopuLink</title>
     <link rel="stylesheet" href="css/register.css">

</head>
<body>
   <div class="auth-wrapper">
          <div class="brand">
             <img src="assets/img/populink_logo2.png"
alt="PopuLink Logo">
             <span>PopuLink</span>
          </div>
          <div class="auth-card">
             <div class="auth-panel auth-panel--accent">
                   <div class="accent-inner">
                      <div class="accent-title">Welcome!</div>
                      <div class="accent-text">
                            Sudah punya akun PopuLink? Masuk kembali
untuk mengelola kegiatan dan komunitas warga.
                      </div>
                      <a href="login.php"
class="accent-btn">Masuk</a>
                   </div>
             </div>
             <div class="auth-panel auth-panel--form">
                   <h2>Daftar sebagai Warga</h2>
                   <p class="subtitle">Lengkapi data diri Anda untuk
mendaftar di PopuLink.</p>
                   <div class="role-tabs">
                      <button id="wargaTab" class="active"
type="button">Warga</button>
                   </div>
                   <?php if ($success): ?>
                      <div class="success"><?= $success ?></div>
                   <?php endif; ?>
                   <?php if ($error): ?>
                      <div class="error"><?= $error ?></div>
                   <?php endif; ?>
                   <form method="post" class="form-register-warga">
                      <input type="hidden" name="role" id="role"
value="warga">
                      <div class="form-row two-col">
                            <div class="form-group">

                            <label>NIK *</label>
                            <input type="text" name="nik"
placeholder="16 digit NIK" required>
                       </div>
                       <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email"
placeholder="email@example.com" required>
                       </div>
                   </div>
                   <div class="form-row two-col">
                       <div class="form-group">
                            <label>Nama Lengkap *</label>
                            <input type="text" name="nama"
placeholder="Nama lengkap sesuai KTP" required>
                       </div>
                       <div class="form-group">
                            <label>Nama Panggilan</label>
                            <input type="text"
name="nama_panggilan" placeholder="Nama panggilan">
                       </div>
                   </div>
                   <div class="form-row two-col">
                       <div class="form-group">
                            <label>Password *</label>
                            <input type="password"
name="password" placeholder="Minimal 6 karakter" required>
                       </div>
                       <div class="form-group">
                            <label>Konfirmasi Password *</label>
                            <input type="password"
name="konfirmasi_password" placeholder="Ulangi password"
required>
                       </div>
                   </div>
                   <div class="form-row">
                       <div class="form-group">
                            <label>Alamat</label>
                            <input type="text" name="alamat"

placeholder="Alamat lengkap">
                        </div>
                     </div>
                     <div class="form-row two-col">
                        <div class="form-group">
                              <label>RT</label>
                              <input type="text" name="rt"
placeholder="001">
                        </div>
                        <div class="form-group">
                              <label>RW</label>
                              <input type="text" name="rw"
placeholder="005">
                        </div>
                     </div>
                     <div class="form-row two-col">
                        <div class="form-group">
                              <label>Pekerjaan</label>
                              <select name="pekerjaan">
                                   <option value="">Pilih
pekerjaan</option>
                                   <option
value="Pelajar/Mahasiswa">Pelajar / Mahasiswa</option>
                                   <option value="Pegawai
Negeri">Pegawai Negeri</option>
                                   <option value="Karyawan
Swasta">Karyawan Swasta</option>
                                   <option
value="Wiraswasta">Wiraswasta</option>
                                   <option value="Ibu Rumah
Tangga">Ibu Rumah Tangga</option>
                                   <option
value="Lainnya">Lainnya</option>
                              </select>
                        </div>
                        <div class="form-group">
                              <label>Pendidikan Terakhir</label>
                              <select name="pendidikan">
                                   <option value="">Pilih

pendidikan</option>
                                    <option value="SD">SD</option>
                                    <option value="SMP">SMP</option>
                                    <option value="SMA/SMK">SMA /
SMK</option>
                                    <option value="D3">D3</option>
                                    <option value="S1">S1</option>
                                    <option value="S2">S2</option>
                                    <option value="S3">S3</option>
                               </select>
                         </div>
                      </div>
                      <div class="form-row">
                         <label>Minat Sosial / Kegiatan</label>
                         <div class="minat-grid">
                               <label><input type="checkbox"
name="minat[]" value="Olahraga"> Olahraga</label>
                               <label><input type="checkbox"
name="minat[]" value="Pendidikan"> Pendidikan</label>
                               <label><input type="checkbox"
name="minat[]" value="Lingkungan"> Lingkungan</label>
                               <label><input type="checkbox"
name="minat[]" value="Seni & Budaya"> Seni & Budaya</label>
                               <label><input type="checkbox"
name="minat[]" value="Sosial"> Sosial</label>
                               <label><input type="checkbox"
name="minat[]" value="Kesehatan"> Kesehatan</label>
                               <label><input type="checkbox"
name="minat[]" value="Keagamaan"> Keagamaan</label>
                               <label><input type="checkbox"
name="minat[]" value="Teknologi"> Teknologi</label>
                         </div>
                      </div>
                      <button type="submit" name="register"
class="btn-primary btn-full">
                         Daftar Sekarang
                      </button>
               </form>
               <p class="helper">

                          Sudah punya akun? <a href="login.php">Login
 di sini</a>
                     </p>
                </div>
           </div>
      </div>
 </body>
 </html>
