<?php
// Pembuat admin PERTAMA. Otomatis terkunci begitu sudah ada 1 admin di tabel users.
// Setelah dipakai, HAPUS file ini dari server.
include "koneksi.php";
include "auth.php";
mysqli_report(MYSQLI_REPORT_OFF);

$r   = rows(dbq($koneksi, "SELECT COUNT(*) AS c FROM users WHERE role = 'admin'"));
$ada = $r ? (int)$r[0]['c'] : 0;
if ($ada > 0) {
    http_response_code(403);
    die("Admin sudah ada, halaman ini dikunci. Hapus file setup_admin.php dari server.");
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u  = trim($_POST['username'] ?? '');
    $p  = $_POST['password'] ?? '';
    $p2 = $_POST['konfirmasi'] ?? '';

    if ($u === '' || $p === '') {
        $error = 'Username dan password wajib diisi.';
    } elseif ($p !== $p2) {
        $error = 'Konfirmasi password tidak sama.';
    } elseif (strlen($p) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif (dbq($koneksi, "INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')", 'ss', [$u, hash_pw($p)])) {
        flash_set('success', 'Admin berhasil dibuat. Sekarang login, lalu hapus setup_admin.php dari server.');
        header("Location: login.php"); exit;
    } else {
        $error = 'Gagal membuat admin: ' . mysqli_error($koneksi);
    }
}

include "navbar.php";
?>
<div class="auth">
  <section class="auth-hero">
    <div class="auth-brand"><span class="sb-logo"><i class="bi bi-mortarboard-fill"></i></span>SIPN</div>
    <div>
      <h1>Siapkan akun admin pertama.</h1>
      <ul class="list-unstyled mb-0">
        <li><i class="bi bi-check-circle-fill"></i><span>Halaman ini terkunci otomatis setelah admin ada</span></li>
        <li><i class="bi bi-check-circle-fill"></i><span>Hapus file setup_admin.php setelah dipakai</span></li>
      </ul>
    </div>
    <div class="auth-foot small">SMK Sangkuriang 1 Cimahi &middot; Pemrograman Web</div>
  </section>
  <section class="auth-panel">
    <div class="auth-card">
      <h2 class="mb-1">Buat Admin Pertama</h2>
      <p class="text-muted mb-4">Akun ini punya akses penuh.</p>
      <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
      <form method="POST">
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label" for="username">Username</label>
          <input type="text" class="form-control" id="username" name="username" maxlength="50" required autofocus></div>
        <div class="mb-3"><label class="form-label" for="password">Password</label>
          <input type="password" class="form-control" id="password" name="password" minlength="6" required></div>
        <div class="mb-4"><label class="form-label" for="konfirmasi">Konfirmasi Password</label>
          <input type="password" class="form-control" id="konfirmasi" name="konfirmasi" minlength="6" required></div>
        <button type="submit" class="btn btn-primary w-100 py-2">Buat Admin</button>
      </form>
    </div>
  </section>
</div>
<?php include "script.php"; ?>
