<?php
 session_start();
 require "config/koneksi.php";

 $sessionTimeout = 30 * 60;
 if (isset($_SESSION['LAST_ACTIVITY']) && (time() -
 $_SESSION['LAST_ACTIVITY']) > $sessionTimeout) {
      session_unset();
      session_destroy();
 }

 $error = "";

 $savedRole = "warga";
 if (isset($_COOKIE["remember_role"])){
      $savedRole = $_COOKIE["remember_role"];
 }

 if ($savedRole === "admin") {
      $savedNik   = "";
      $savedPass = "";
      if (isset($_COOKIE["remember_admin_email"])) {
           $savedNik = $_COOKIE["remember_admin_email"];
      }
      if (isset($_COOKIE["remember_admin_pass"])) {
           $savedPass = $_COOKIE["remember_admin_pass"];
      }
      $rememberChecked = ($savedNik !== "");
 } else {
      $savedNik   = "";
      $savedPass = "";
      if (isset($_COOKIE["remember_warga_nik"])) {
           $savedNik = $_COOKIE["remember_warga_nik"];
      }
      if (isset($_COOKIE["remember_warga_pass"])) {
           $savedPass = $_COOKIE["remember_warga_pass"];

    }
    $rememberChecked = ($savedNik !== "");
}

if (isset($_POST["login"])) {
    $roleInput = $_POST["role"];
    $nik        = $_POST["nik"];
    $password   = $_POST["password"];
    $remember   = isset($_POST["remember"]);

    if ($roleInput === "admin") {
        $query = "SELECT * FROM user WHERE email='$nik' AND
pass='$password' AND role='admin' LIMIT 1";
    } else {
        $query = "SELECT * FROM user WHERE NIK='$nik' AND
pass='$password' AND role='user' LIMIT 1";
    }

    $result = mysqli_query($koneksi, $query);

    if ($dataUser = mysqli_fetch_assoc($result)) {

        $_SESSION["id_user"] = $dataUser["id_user"];
        $_SESSION["role"]      = $roleInput;
        $_SESSION["LAST_ACTIVITY"] = time();

        if ($roleInput === "admin") {
            $_SESSION["nik"]    = "";
            $_SESSION["nama"] = "Administrator";
        } else {
            $_SESSION["nik"] = $dataUser["NIK"];
            $qPenduduk = mysqli_query($koneksi, "SELECT nama FROM
penduduk WHERE NIK='" . $dataUser["NIK"] . "' LIMIT 1");
            $dataPenduduk = mysqli_fetch_assoc($qPenduduk);
            $_SESSION["nama"] = "Pengguna";
            if (isset($dataPenduduk["nama"])) {
                $_SESSION["nama"] = $dataPenduduk["nama"];
            }
        }

        $cookieTime = time() + (30 * 24 * 60 * 60);

        if ($remember) {

              setcookie("remember_role", $roleInput, $cookieTime,
"/");

              if ($roleInput === "admin") {
                  setcookie("remember_admin_email", $nik,
$cookieTime, "/");
                  setcookie("remember_admin_pass",   $password,
$cookieTime, "/");
              } else {
                  setcookie("remember_warga_nik",    $nik,
$cookieTime, "/");
                  setcookie("remember_warga_pass", $password,
$cookieTime, "/");
              }

        } else {
              if ($roleInput === "admin") {
                  setcookie("remember_admin_email", "", time() -
3600, "/");
                  setcookie("remember_admin_pass",   "", time() -
3600, "/");
              } else {
                  setcookie("remember_warga_nik",    "", time() -
3600, "/");
                  setcookie("remember_warga_pass", "", time() -
3600, "/");
              }
              setcookie("remember_role", "", time() - 3600, "/");
        }
        header("Location: dashboard.php");
        exit;
   } else {
        if ($roleInput === "admin") {
              $error = "Email atau password salah!";

          } else {
              $error = "NIK atau password salah!";
          }
          $savedNik    = $nik;
          $savedRole   = $roleInput;
          $savedPass   = $password;
          $rememberChecked = $remember;
     }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width,
initial-scale=1.0">
     <title>Login | PopuLink</title>
     <link rel="stylesheet" href="css/login.css">
</head>
<body>
<div class="auth-wrapper">
     <div class="brand">
          <img src="assets/img/populink_logo2.png" alt="PopuLink
Logo">
          <span>PopuLink</span>
     </div>
     <div class="auth-card">
          <div class="auth-panel auth-panel--form">
              <h2>Masuk ke PopuLink</h2>
              <div class="role-tabs">
                  <button id="wargaTab" type="button"
onclick="switchRole('warga')">Warga</button>
                  <button id="adminTab" type="button"
onclick="switchRole('admin')">Admin</button>
              </div>
              <?php if($error): ?>
                  <div class="error"><?= htmlspecialchars($error)
?></div>

               <?php endif; ?>
               <form method="post">
                  <input type="hidden" name="role" id="role"
value="<?= htmlspecialchars($savedRole) ?>">
                  <label id="nikLabel">NIK</label>
                  <input type="text" name="nik" id="nikInput"
placeholder="Masukkan NIK Anda" value="<?=
htmlspecialchars($savedNik) ?>" required>
                  <label>Password</label>
                  <input type="password" name="password"
id="passwordInput" placeholder="Masukkan password" value="<?=
htmlspecialchars($savedPass) ?>" required>
                  <div class="form-meta">
                         <label class="remember-me">
                            <input type="checkbox" name="remember"
id="rememberCheckbox"
                                 <?= $rememberChecked ? "checked" : ""
?>>
                            <span>Remember Me</span>
                         </label>
                  </div>
                  <button type="submit" name="login" id="submitBtn"
class="btn-primary">
                         Login sebagai Warga
                  </button>
               </form>
         </div>
         <div class="auth-panel auth-panel--accent">
               <div class="accent-inner">
                  <div class="accent-title">Welcome back!</div>
                  <div class="accent-text">Belum tergabung? Daftar
sekarang.</div>
                  <a href="register.php" class="accent-btn">Buat
Akun Baru</a>
               </div>
         </div>
      </div>
</div>
<script>

function applyRoleUI(role) {
    const label      = document.getElementById('nikLabel');
    const input      = document.getElementById('nikInput');
    const submitBtn = document.getElementById('submitBtn');
    if (role === 'admin') {
          label.innerText     = 'Email';
          input.placeholder   = 'Masukkan email';
          submitBtn.innerText = 'Login sebagai Admin';
    } else {
          label.innerText     = 'NIK';
          input.placeholder   = 'Masukkan NIK Anda';
          submitBtn.innerText = 'Login sebagai Warga';
    }
}

function switchRole(role) {
    document.getElementById('role').value = role;

document.getElementById('wargaTab').classList.remove('active');

document.getElementById('adminTab').classList.remove('active');
    document.getElementById(role +
'Tab').classList.add('active');
    document.getElementById('nikInput').value = "";
    document.getElementById('passwordInput').value = "";
    document.getElementById('rememberCheckbox').checked = false;
    applyRoleUI(role);
}

switchRole("<?= $savedRole ?>");
document.getElementById('nikInput').value = "<?=
htmlspecialchars($savedNik) ?>";
document.getElementById('passwordInput').value = "<?=
htmlspecialchars($savedPass) ?>";
document.getElementById('rememberCheckbox').checked = <?=
$rememberChecked ? "true" : "false" ?>;
</script>
</body>
</html>
