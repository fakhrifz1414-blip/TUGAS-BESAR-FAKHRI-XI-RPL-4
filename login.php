<?php
include "koneksi.php";
include "auth.php";
mysqli_report(MYSQLI_REPORT_OFF);

if (isset($_SESSION['user_id'])) { header("Location: dashboard.php"); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    $row = rows(dbq($koneksi, "SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1", 's', [$u]));
    $row = $row ? $row[0] : null;

    if ($row && cek_pw($p, $row['password'])) {
        // Password lama yang masih plaintext otomatis di-hash saat login berhasil
        if (!password_verify($p, $row['password']) || password_needs_rehash($row['password'], PASSWORD_DEFAULT)) {
            dbq($koneksi, "UPDATE users SET password = ? WHERE id = ?", 'si', [hash_pw($p), (int)$row['id']]);
        }
        session_regenerate_id(true);
        $_SESSION['user_id']  = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role']     = $row['role'];
        header("Location: dashboard.php");
        exit;
    }
    $error = 'Username atau password salah.';
}

include "navbar.php";
?>
<div class="auth">
  <section class="auth-hero">
    <div class="auth-brand"><span class="sb-logo"><i class="bi bi-mortarboard-fill"></i></span>SIPN</div>
    <div>
      <h1>Nilai rapor rapi, terpusat, dan transparan.</h1>
      <ul class="list-unstyled mb-0">
        <li><i class="bi bi-check-circle-fill"></i><span>Satu aplikasi untuk admin, guru, dan siswa</span></li>
        <li><i class="bi bi-check-circle-fill"></i><span>Status tuntas / belum tuntas dihitung otomatis</span></li>
        <li><i class="bi bi-check-circle-fill"></i><span>Siswa melihat nilai sendiri kapan saja</span></li>
      </ul>
    </div>
    <div class="auth-foot small">SMK Sangkuriang 1 Cimahi &middot; Pemrograman Web</div>
  </section>

  <section class="auth-panel">
    <div class="auth-card">
      <h2 class="mb-1">Masuk</h2>
      <p class="text-muted mb-4">Gunakan akun yang diberikan sekolah.</p>
      <?php flash_show(); ?>
      <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
      <form method="POST">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label" for="username">Username</label>
          <input type="text" class="form-control" id="username" name="username" maxlength="50" required autofocus>
        </div>
        <div class="mb-4">
          <label class="form-label" for="password">Password</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2">Masuk</button>
      </form>
      <p class="text-center text-muted mt-4 mb-0">Siswa belum punya akun? <a href="register.php" class="fw-semibold">Daftar di sini</a></p>
    </div>
  </section>
</div>
<?php include "script.php"; ?>
