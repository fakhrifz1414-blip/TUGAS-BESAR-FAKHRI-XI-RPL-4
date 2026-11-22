<?php
include "koneksi.php";
include "auth.php";
require_login();
mysqli_report(MYSQLI_REPORT_OFF);

$role = $_SESSION['role'];
$tiles = [];

// ---------- data per role ----------
if ($role === 'admin') {
    $tiles[] = tile('User',     hitung($koneksi, "SELECT COUNT(*) FROM users"),       'bi-people',        'primary',   'user.php',     'col-6 col-lg-2');
    $tiles[] = tile('Guru',     hitung($koneksi, "SELECT COUNT(*) FROM guru"),        'bi-person-badge',  'success',   'guru.php',     'col-6 col-lg-2');
    $tiles[] = tile('Siswa',    hitung($koneksi, "SELECT COUNT(*) FROM siswa"),       'bi-mortarboard',   'info',      'siswa.php',    'col-6 col-lg-2');
    $tiles[] = tile('Mapel',    hitung($koneksi, "SELECT COUNT(*) FROM mapel"),       'bi-book',          'warning',   'mapel.php',    'col-6 col-lg-2');
    $tiles[] = tile('Mengajar', hitung($koneksi, "SELECT COUNT(*) FROM mengajar"),    'bi-diagram-3',     'secondary', 'mengajar.php', 'col-6 col-lg-2');
    $tiles[] = tile('Nilai',    hitung($koneksi, "SELECT COUNT(*) FROM nilai_rapor"), 'bi-clipboard2-data', 'danger',   null,           'col-6 col-lg-2');
    $per_kelas = rows(dbq($koneksi, "SELECT kelas, COUNT(*) AS jml FROM siswa GROUP BY kelas ORDER BY kelas"));
    $cc = kolom_dibuat($koneksi);
    $user_baru = rows(dbq($koneksi, "SELECT u.username, u.role, $cc AS created_at FROM users u ORDER BY u.id DESC LIMIT 5"));
}

if ($role === 'guru') {
    $guru = guru_saya($koneksi);
    $mapel = [];
    if ($guru) {
        $gid = (int)$guru['id'];
        $mapel = rows(dbq($koneksi, "SELECT m.id, m.kode_mapel, m.nama_mapel,
                (SELECT COUNT(*) FROM mengajar j WHERE j.mapel_id = m.id) AS jml_siswa,
                (SELECT COUNT(*) FROM mengajar j2 WHERE j2.mapel_id = m.id
                    AND EXISTS (SELECT 1 FROM nilai_rapor n WHERE n.mapel_id = j2.mapel_id AND n.siswa_id = j2.siswa_id)) AS jml_nilai
              FROM mapel m WHERE m.guru_id = ? ORDER BY m.nama_mapel", 'i', [$gid]));
        $siswa_unik = hitung($koneksi, "SELECT COUNT(DISTINCT j.siswa_id) FROM mengajar j JOIN mapel m ON m.id = j.mapel_id WHERE m.guru_id = ?", 'i', [$gid]);
        $sudah = array_sum(array_column($mapel, 'jml_nilai'));
        $total = array_sum(array_column($mapel, 'jml_siswa'));
        $tiles[] = tile('Mapel Diampu',  count($mapel), 'bi-book',            'primary', 'nilai.php');
        $tiles[] = tile('Siswa Diajar',  $siswa_unik,   'bi-mortarboard',     'info');
        $tiles[] = tile('Sudah Dinilai', $sudah,        'bi-check2-circle',   'success', 'nilai.php');
        $tiles[] = tile('Belum Dinilai', $total - $sudah, 'bi-hourglass-split', 'warning', 'nilai.php?aksi=tambah');
    }
}

if ($role === 'siswa') {
    $siswa = siswa_saya($koneksi);
    $nilai = [];
    if ($siswa) {
        $nilai = rows(dbq($koneksi, "SELECT m.nama_mapel, n.nilai_kkm, n.nilai_pengetahuan, n.nilai_keterampilan, n.Keterangan
                FROM mengajar j
                JOIN mapel m ON m.id = j.mapel_id
                LEFT JOIN nilai_rapor n ON n.mapel_id = j.mapel_id AND n.siswa_id = j.siswa_id
                WHERE j.siswa_id = ? ORDER BY m.nama_mapel", 'i', [(int)$siswa['id']]));
        list($ada, $rata, $tuntas) = ringkas_nilai($nilai);
        $tiles[] = tile('Mapel Diikuti', count($nilai), 'bi-book',            'primary', 'nilai_saya.php');
        $tiles[] = tile('Nilai Masuk',   count($ada),   'bi-clipboard2-check', 'info',    'nilai_saya.php');
        $tiles[] = tile('Rata-rata',     $rata !== null ? $rata : '-', 'bi-graph-up', 'success');
        $tiles[] = tile('Mapel Tuntas',  $tuntas . ' / ' . count($ada), 'bi-patch-check', 'warning');
    }
}

include "navbar.php";
$nama_tampil = $_SESSION['username'];
if ($role === 'guru' && !empty($guru))   $nama_tampil = $guru['nama'];
if ($role === 'siswa' && !empty($siswa)) $nama_tampil = $siswa['nama'];
?>

<div class="container">
  <?php flash_show(); ?>

  <div class="hero mb-4">
    <h2>Halo, <?= e($nama_tampil) ?> 👋</h2>
    <div class="opacity-75 mb-2">Dashboard <?= ucfirst($role) ?></div>
    <span class="chip"><i class="bi bi-calendar3 me-1"></i><?= date('d M Y') ?></span>
    <?php if ($role === 'siswa' && !empty($siswa)): ?>
      <span class="chip">NIS <?= e($siswa['nis']) ?></span><span class="chip">Kelas <?= e($siswa['kelas']) ?></span>
    <?php endif; ?>
    <?php if ($role === 'guru' && !empty($guru)): ?>
      <span class="chip">NIP <?= e($guru['nip']) ?></span>
    <?php endif; ?>
  </div>

  <?php if (($role === 'guru' && !$guru) || ($role === 'siswa' && !$siswa)): ?>
    <div class="alert alert-warning">Akun ini belum terhubung dengan data <?= $role ?>. Hubungi admin.</div>
  <?php endif; ?>

  <div class="row g-3 mb-4"><?= implode('', $tiles) ?></div>

  <?php if ($role === 'admin'): ?>
  <div class="row g-3">
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-header">Siswa per Kelas</div>
        <div class="card-body p-0">
          <table class="table">
            <thead><tr><th>Kelas</th><th class="text-end">Jumlah Siswa</th></tr></thead>
            <tbody>
              <?php foreach ($per_kelas as $r): ?>
                <tr><td><?= e($r['kelas']) ?></td><td class="text-end"><?= (int)$r['jml'] ?></td></tr>
              <?php endforeach; ?>
              <?php if (!$per_kelas): ?><tr><td colspan="2" class="text-muted text-center py-3">Belum ada data.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-header">User Terbaru</div>
        <div class="card-body p-0">
          <table class="table">
            <thead><tr><th>Username</th><th>Role</th><th>Dibuat</th></tr></thead>
            <tbody>
              <?php foreach ($user_baru as $r): ?>
                <tr><td><?= e($r['username']) ?></td><td><span class="badge text-bg-secondary"><?= e($r['role']) ?></span></td><td class="text-muted small"><?= tgl($r['created_at']) ?></td></tr>
              <?php endforeach; ?>
              <?php if (!$user_baru): ?><tr><td colspan="3" class="text-muted text-center py-3">Belum ada data.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($role === 'guru' && $guru): ?>
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>Mapel yang Saya Ampu</span>
      <a href="nilai.php?aksi=tambah" class="btn btn-primary btn-sm">+ Input Nilai</a>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead><tr><th>Kode</th><th>Mata Pelajaran</th><th class="text-center">Siswa</th><th class="text-center">Sudah Dinilai</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($mapel as $m): ?>
          <tr>
            <td><?= e($m['kode_mapel']) ?></td>
            <td><?= e($m['nama_mapel']) ?></td>
            <td class="text-center"><?= (int)$m['jml_siswa'] ?></td>
            <td class="text-center">
              <span class="badge <?= $m['jml_nilai'] >= $m['jml_siswa'] && $m['jml_siswa'] > 0 ? 'text-bg-success' : 'text-bg-warning' ?>"><?= (int)$m['jml_nilai'] ?> / <?= (int)$m['jml_siswa'] ?></span>
            </td>
            <td class="text-end"><a class="btn btn-outline-primary btn-sm" href="nilai.php?mapel_id=<?= (int)$m['id'] ?>">Lihat Nilai</a></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$mapel): ?><tr><td colspan="5" class="text-center text-muted py-4">Anda belum ditugaskan mengampu mapel.</td></tr><?php endif; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($role === 'siswa' && $siswa): ?>
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>Ringkasan Nilai</span>
      <a href="nilai_saya.php" class="btn btn-outline-primary btn-sm">Lihat Detail</a>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead><tr><th>Mata Pelajaran</th><th class="text-center">Rata-rata</th><th class="text-center">Keterangan</th></tr></thead>
        <tbody>
          <?php foreach ($nilai as $r): ?>
          <tr>
            <td><?= e($r['nama_mapel']) ?></td>
            <?php if ($r['nilai_pengetahuan'] === null): ?>
              <td class="text-center text-muted">-</td><td class="text-center"><span class="badge text-bg-light border">Belum ada nilai</span></td>
            <?php else: ?>
              <td class="text-center"><?= rata_nilai($r['nilai_pengetahuan'], $r['nilai_keterampilan']) ?></td>
              <td class="text-center"><span class="badge <?= $r['Keterangan'] === 'Tuntas' ? 'text-bg-success' : 'text-bg-danger' ?>"><?= e($r['Keterangan']) ?></span></td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
          <?php if (!$nilai): ?><tr><td colspan="3" class="text-center text-muted py-4">Kamu belum terdaftar di mapel manapun.</td></tr><?php endif; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php include "script.php"; ?>
