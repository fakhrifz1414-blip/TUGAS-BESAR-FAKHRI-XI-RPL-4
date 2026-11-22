<?php
include "koneksi.php";
include "auth.php";
require_role('siswa');
mysqli_report(MYSQLI_REPORT_OFF);

$siswa = siswa_saya($koneksi);
$nilai = $siswa ? rows(dbq($koneksi, "SELECT m.kode_mapel, m.nama_mapel, g.nama AS guru,
              n.nilai_kkm, n.nilai_pengetahuan, n.nilai_keterampilan, n.Keterangan
            FROM mengajar j
            JOIN mapel m ON m.id = j.mapel_id
            JOIN guru g  ON g.id = m.guru_id
            LEFT JOIN nilai_rapor n ON n.mapel_id = j.mapel_id AND n.siswa_id = j.siswa_id
            WHERE j.siswa_id = ? ORDER BY m.nama_mapel", 'i', [(int)$siswa['id']])) : [];

list($ada, $rata, $tuntas) = ringkas_nilai($nilai);

include "navbar.php";
?>

<div class="container">
  <?php flash_show(); ?>

  <div class="hero mb-4">
    <h2>Nilai Saya</h2>
    <?php if ($siswa): ?>
      <span class="chip"><?= e($siswa['nama']) ?></span><span class="chip">NIS <?= e($siswa['nis']) ?></span><span class="chip">Kelas <?= e($siswa['kelas']) ?></span>
    <?php endif; ?>
  </div>
  <?php if (!$siswa): ?>
    <div class="alert alert-warning">Akun ini belum terhubung dengan data siswa. Hubungi admin.</div>
  <?php endif; ?>

  <div class="row g-3 mb-4">
    <?= tile('Mapel Diikuti', count($nilai), 'bi-book', 'primary') ?>
    <?= tile('Nilai Masuk', count($ada), 'bi-clipboard2-check', 'info') ?>
    <?= tile('Rata-rata Keseluruhan', $rata !== null ? $rata : '-', 'bi-graph-up', 'success') ?>
    <?= tile('Mapel Tuntas', $tuntas . ' / ' . count($ada), 'bi-patch-check', 'warning') ?>
  </div>

  <div class="card">
    <div class="card-header">Rincian Nilai per Mata Pelajaran</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th class="text-center">No</th><th>Mata Pelajaran</th><th>Guru</th>
              <th class="text-center">KKM</th><th class="text-center">Pengetahuan</th><th class="text-center">Keterampilan</th>
              <th class="text-center">Rata-rata</th><th class="text-center">Keterangan</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($nilai as $i => $r): ?>
            <tr>
              <td class="text-center"><?= $i + 1 ?></td>
              <td><?= e($r['nama_mapel']) ?> <span class="badge text-bg-light border"><?= e($r['kode_mapel']) ?></span></td>
              <td><?= e($r['guru']) ?></td>
              <?php if ($r['nilai_pengetahuan'] === null): ?>
                <td colspan="5" class="text-center text-muted"><span class="badge text-bg-light border">Belum ada nilai</span></td>
              <?php else:
                $rt = rata_nilai($r['nilai_pengetahuan'], $r['nilai_keterampilan']); ?>
                <td class="text-center"><?= (int)$r['nilai_kkm'] ?></td>
                <td class="text-center"><?= (int)$r['nilai_pengetahuan'] ?></td>
                <td class="text-center"><?= (int)$r['nilai_keterampilan'] ?></td>
                <td class="text-center fw-semibold"><?= $rt ?></td>
                <td class="text-center"><span class="badge <?= $r['Keterangan'] === 'Tuntas' ? 'text-bg-success' : 'text-bg-danger' ?>"><?= e($r['Keterangan']) ?></span></td>
              <?php endif; ?>
            </tr>
            <?php endforeach; ?>
            <?php if (!$nilai): ?><tr><td colspan="8" class="text-center text-muted py-4">Kamu belum terdaftar di mapel manapun.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include "script.php"; ?>
