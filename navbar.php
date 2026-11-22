<?php
include_once "auth.php";
$cur    = basename($_SERVER['PHP_SELF']);
$role   = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$logged = isset($_SESSION['user_id']);
$aksi_q = isset($_GET['aksi']) ? $_GET['aksi'] : '';

$judul_map = array(
    'dashboard.php' => 'Dashboard', 'user.php' => 'Kelola User', 'guru.php' => 'Kelola Guru',
    'siswa.php' => 'Kelola Siswa', 'mapel.php' => 'Kelola Mata Pelajaran', 'mengajar.php' => 'Kelola Mengajar',
    'nilai.php' => 'Nilai Siswa', 'nilai_saya.php' => 'Nilai Saya', 'profil.php' => 'Profil', 'cek_db.php' => 'Cek Database',
);
$judul = isset($judul_map[$cur]) ? $judul_map[$cur] : 'SIPN';

// Grup menu admin: tiap section = 1 file halaman, isinya "Daftar X" dan "+ Tambah X"
$grup_admin = array(
    array('User',     'user.php',     'bi-people'),
    array('Guru',     'guru.php',     'bi-person-badge'),
    array('Siswa',    'siswa.php',    'bi-mortarboard'),
    array('Mapel',    'mapel.php',    'bi-book'),
    array('Mengajar', 'mengajar.php', 'bi-diagram-3'),
);
$nama_user = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$inisial   = strtoupper(substr($nama_user !== '' ? $nama_user : '?', 0, 1));

// Render satu grup dropdown sidebar
function sb_grup($id, $label, $ikon, $file, $daftar, $tambah, $cur, $aksi_q) {
    $aktif = ($cur === $file);
    $h  = '<div class="sb-group">';
    $h .= '<a class="sb-link' . ($aktif ? ' active' : '') . '" data-bs-toggle="collapse" href="#g-' . $id . '" role="button" aria-expanded="' . ($aktif ? 'true' : 'false') . '">'
        . '<i class="bi ' . $ikon . '"></i><span>' . e($label) . '</span><i class="bi bi-chevron-down ms-auto sb-caret"></i></a>';
    $h .= '<div class="collapse' . ($aktif ? ' show' : '') . '" id="g-' . $id . '">';
    $h .= '<a class="sb-sub' . ($aktif && $aksi_q !== 'tambah' ? ' active' : '') . '" href="' . $file . '">' . e($daftar) . '</a>';
    $h .= '<a class="sb-sub sb-add' . ($aktif && $aksi_q === 'tambah' ? ' active' : '') . '" href="' . $file . '?aksi=tambah">' . e($tambah) . '</a>';
    $h .= '</div></div>';
    return $h;
}
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIPN &middot; <?= e($judul) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
      /* ===== Tema "Rimba": hijau teal gelap + aksen amber + latar krem ===== */
      :root{
        --bg:#f6f3ec; --ink:#1c2b2a; --mute:#6b7a78; --line:#e7e1d4; --card:#fff;
        --brand:#0f766e; --brand-d:#0b5a54; --acc:#f59e0b;
        --bs-primary:#0f766e; --bs-primary-rgb:15,118,110;
        --bs-link-color:#0f766e; --bs-link-color-rgb:15,118,110; --bs-link-hover-color:#0b5a54;
        --bs-body-bg:#f6f3ec; --bs-body-color:#1c2b2a;
        --bs-body-font-family:'Plus Jakarta Sans',system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;
        --bs-border-color:#e7e1d4; --bs-primary-bg-subtle:#d8efec; --bs-primary-text-emphasis:#0b5a54;
        --bs-info-bg-subtle:#dbeefb; --bs-success-bg-subtle:#dcf5e4; --bs-warning-bg-subtle:#fdeccd;
      }
      body{background:var(--bg);color:var(--ink);font-size:.95rem}
      h1,h2,h3,h4,h5{letter-spacing:-.01em}

      /* --- sidebar --- */
      .sidebar{position:fixed;top:0;bottom:0;left:0;width:272px;background:linear-gradient(180deg,#0d2b2a,#0a1f1e);color:#bfd3cf;z-index:1040;display:flex;flex-direction:column;transition:transform .25s ease}
      .sb-brand{padding:1.2rem 1.25rem;display:flex;align-items:center;gap:.75rem;color:#fff;text-decoration:none}
      .sb-logo{width:40px;height:40px;border-radius:13px;background:var(--acc);display:grid;place-items:center;color:#0d2b2a;font-size:1.3rem}
      .sb-title{font-weight:800;letter-spacing:.05em;line-height:1;font-size:1.1rem}
      .sb-title small{display:block;font-weight:500;letter-spacing:0;font-size:.7rem;color:#8fb0aa;margin-top:.25rem}
      .sb-scroll{flex:1;overflow-y:auto;padding:.25rem .8rem 1rem}
      .sb-label{font-size:.68rem;letter-spacing:.12em;text-transform:uppercase;color:#6f9089;margin:1rem .6rem .4rem}
      .sb-link{display:flex;align-items:center;gap:.75rem;padding:.62rem .75rem;border-radius:12px;color:#bfd3cf;text-decoration:none;font-weight:500;font-size:.92rem;margin-bottom:2px}
      .sb-link:hover{background:rgba(255,255,255,.06);color:#fff}
      .sb-link.active{background:rgba(245,158,11,.14);color:#fff;box-shadow:inset 3px 0 0 var(--acc)}
      .sb-link>i:first-child{font-size:1.05rem;width:1.2rem;text-align:center}
      .sb-caret{font-size:.7rem;transition:transform .2s}
      .sb-link[aria-expanded="true"] .sb-caret{transform:rotate(180deg)}
      .sb-sub{display:block;padding:.42rem .75rem .42rem 2.9rem;color:#9bb6b1;text-decoration:none;font-size:.86rem;border-radius:10px}
      .sb-sub:hover{color:#fff;background:rgba(255,255,255,.05)}
      .sb-sub.active{color:#fbbf24;font-weight:700}
      .sb-sub.sb-add{color:#5eead4}
      .sb-user{padding:.9rem 1rem;border-top:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:.7rem}
      .sb-avatar{width:40px;height:40px;border-radius:13px;background:#134e4a;color:#fbbf24;display:grid;place-items:center;font-weight:800;flex-shrink:0}
      .sb-user-name{color:#fff;font-weight:600;font-size:.9rem;line-height:1.2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
      .sb-user-role{font-size:.72rem;color:#fbbf24;text-transform:uppercase;letter-spacing:.08em}
      .sb-backdrop{display:none;position:fixed;inset:0;background:rgba(5,20,19,.55);z-index:1035}

      /* --- area utama --- */
      .app-main{margin-left:272px;min-height:100vh;display:flex;flex-direction:column}
      .topbar{position:sticky;top:0;z-index:1020;background:rgba(246,243,236,.92);backdrop-filter:blur(8px);border-bottom:1px solid var(--line);padding:.75rem 1.5rem;display:flex;align-items:center;gap:.75rem}
      .topbar .t-title{font-weight:700;font-size:1.05rem}
      .t-burger{display:none;border:1px solid var(--line);background:#fff;border-radius:12px;width:42px;height:42px;font-size:1.3rem;line-height:1;color:var(--ink)}
      .app-content{padding:1.5rem;flex:1}
      .app-content>.container{max-width:1240px;padding:0;margin:0 auto}
      .app-foot{padding:1rem 1.5rem;color:#9aa5a3;font-size:.8rem;text-align:center}
      @media (max-width:991.98px){
        .sidebar{transform:translateX(-100%)}
        body.sb-open .sidebar{transform:none}
        body.sb-open .sb-backdrop{display:block}
        .app-main{margin-left:0}
        .t-burger{display:grid;place-items:center}
        .topbar{padding:.65rem 1rem}
        .app-content{padding:1rem}
      }

      /* --- komponen --- */
      .card{background:var(--card);border:1px solid var(--line);border-radius:18px;box-shadow:none}
      .card-header{background:transparent;border-bottom:1px dashed var(--line);font-weight:700;padding:1rem 1.2rem;border-radius:18px 18px 0 0 !important}
      .card-body{padding:1.2rem}
      .card-body.p-0{padding:0 !important}
      .table{--bs-table-bg:transparent;--bs-table-hover-bg:#f1f7f5;margin-bottom:0}
      .table>:not(caption)>*>*{padding:.8rem 1rem;border-bottom-color:#f0ebdf}
      .table thead th{background:#faf7f0;color:var(--mute);font-size:.7rem;letter-spacing:.07em;text-transform:uppercase;font-weight:700;border-bottom:1px solid var(--line)}
      .badge{border-radius:999px;font-weight:600;padding:.4em .75em}
      .alert{border-radius:14px;border:0}
      .form-control,.form-select{border-radius:12px;border-color:#ddd5c3;background-color:#fffdf8;padding:.6rem .85rem}
      .form-control:focus,.form-select:focus{border-color:var(--brand);box-shadow:0 0 0 .22rem rgba(15,118,110,.15);background-color:#fff}
      .form-label{font-weight:600;font-size:.85rem}
      .btn{border-radius:12px;font-weight:600}
      .btn-sm{border-radius:10px}
      .btn-primary{--bs-btn-bg:#0f766e;--bs-btn-border-color:#0f766e;--bs-btn-hover-bg:#0b5a54;--bs-btn-hover-border-color:#0b5a54;--bs-btn-active-bg:#0b5a54;--bs-btn-active-border-color:#0b5a54;--bs-btn-disabled-bg:#0f766e;--bs-btn-disabled-border-color:#0f766e}
      .btn-outline-primary{--bs-btn-color:#0f766e;--bs-btn-border-color:#0f766e;--bs-btn-hover-bg:#0f766e;--bs-btn-hover-border-color:#0f766e;--bs-btn-active-bg:#0b5a54;--bs-btn-active-border-color:#0b5a54}
      .btn-secondary{--bs-btn-bg:#e9e4d6;--bs-btn-color:#3b4a48;--bs-btn-border-color:#e9e4d6;--bs-btn-hover-bg:#ddd6c4;--bs-btn-hover-color:#243230;--bs-btn-hover-border-color:#ddd6c4;--bs-btn-active-bg:#d3cbb6;--bs-btn-active-color:#243230}
      .btn-outline-secondary{--bs-btn-color:#5b6b69;--bs-btn-border-color:#d6cfbd;--bs-btn-hover-bg:#e9e4d6;--bs-btn-hover-color:#243230;--bs-btn-hover-border-color:#d6cfbd}
      .btn-warning{--bs-btn-bg:#fdf0d5;--bs-btn-color:#8a5a00;--bs-btn-border-color:#fbe2ad;--bs-btn-hover-bg:#f9dc9a;--bs-btn-hover-color:#6b4500;--bs-btn-hover-border-color:#f5cf7a;--bs-btn-active-bg:#f5cf7a;--bs-btn-active-color:#6b4500}
      .btn-danger{--bs-btn-bg:#fde7e7;--bs-btn-color:#a12a2a;--bs-btn-border-color:#f8cfcf;--bs-btn-hover-bg:#f9cdcd;--bs-btn-hover-color:#7d1d1d;--bs-btn-hover-border-color:#f3b9b9;--bs-btn-active-bg:#f3b9b9;--bs-btn-active-color:#7d1d1d}
      .btn-light{--bs-btn-bg:#fff;--bs-btn-border-color:var(--line)}
      .pagination{--bs-pagination-color:#0f766e;--bs-pagination-active-bg:#0f766e;--bs-pagination-active-border-color:#0f766e;--bs-pagination-hover-color:#0b5a54;--bs-pagination-border-color:var(--line);--bs-pagination-border-radius:.6rem}
      .pagination .page-item{margin:0 .15rem}
      .pagination .page-link{border-radius:.6rem !important}
      .page-title{font-weight:800;letter-spacing:-.02em}
      dt{color:var(--mute);font-weight:600;font-size:.85rem}

      /* --- hero & tile (dashboard) --- */
      .hero{background:linear-gradient(135deg,#0f766e 0%,#115e59 55%,#134e4a 100%);color:#fff;border-radius:22px;padding:1.6rem 1.75rem;position:relative;overflow:hidden}
      .hero:before{content:"";position:absolute;right:-60px;top:-60px;width:230px;height:230px;border-radius:50%;background:rgba(245,158,11,.28)}
      .hero:after{content:"";position:absolute;right:80px;bottom:-90px;width:190px;height:190px;border-radius:50%;background:rgba(255,255,255,.08)}
      .hero>*{position:relative;z-index:1}
      .hero h2{font-weight:800;letter-spacing:-.02em;margin-bottom:.35rem}
      .hero .chip{display:inline-block;background:rgba(255,255,255,.15);border-radius:999px;padding:.25rem .8rem;font-size:.78rem;margin:.15rem .25rem 0 0}
      .tile{display:block;background:#fff;border:1px solid var(--line);border-radius:18px;padding:1rem 1.1rem 1.1rem;text-decoration:none;color:inherit;position:relative;overflow:hidden;height:100%;transition:transform .15s,box-shadow .15s}
      a.tile:hover{transform:translateY(-2px);box-shadow:0 10px 22px rgba(13,43,42,.09);color:inherit}
      .tile:after{content:"";position:absolute;left:0;right:0;bottom:0;height:4px;background:var(--t)}
      .tile-top{display:flex;justify-content:space-between;align-items:center;gap:.5rem}
      .tile-label{font-size:.7rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--mute)}
      .tile-ic{width:38px;height:38px;border-radius:12px;display:grid;place-items:center;background:var(--tb);color:var(--t);font-size:1.15rem;flex-shrink:0}
      .tile-num{font-size:2rem;font-weight:800;letter-spacing:-.03em;margin-top:.45rem;line-height:1.1}
      .tile-foot{font-size:.78rem;color:var(--t);font-weight:700;margin-top:.3rem}
      .tile-primary{--t:#0f766e;--tb:#d8efec}.tile-success{--t:#15803d;--tb:#dcf5e4}.tile-info{--t:#0369a1;--tb:#dbeefb}
      .tile-warning{--t:#b45309;--tb:#fdeccd}.tile-secondary{--t:#475569;--tb:#e7ebf0}.tile-danger{--t:#be123c;--tb:#fde1e8}

      /* --- halaman login / register (tanpa sidebar) --- */
      .auth{min-height:100vh;display:grid;grid-template-columns:1.05fr 1fr}
      .auth-hero{background:linear-gradient(160deg,#0d2b2a 0%,#115e59 70%,#0f766e 100%);color:#e6f4f1;padding:3rem 3.25rem;display:flex;flex-direction:column;justify-content:space-between;position:relative;overflow:hidden}
      .auth-hero:before{content:"";position:absolute;right:-90px;bottom:-90px;width:320px;height:320px;border-radius:50%;background:rgba(245,158,11,.22)}
      .auth-hero:after{content:"";position:absolute;left:-60px;top:35%;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.06)}
      .auth-hero>*{position:relative;z-index:1}
      .auth-brand{display:flex;align-items:center;gap:.8rem;font-weight:800;letter-spacing:.05em;font-size:1.15rem;color:#fff}
      .auth-hero h1{font-weight:800;font-size:2.2rem;line-height:1.15;letter-spacing:-.025em;color:#fff;margin:2rem 0 1rem}
      .auth-hero li{margin-bottom:.6rem;display:flex;gap:.6rem;align-items:flex-start}
      .auth-hero li i{color:#fbbf24;margin-top:.15rem}
      .auth-panel{display:flex;align-items:center;justify-content:center;padding:2rem 1.25rem;background:var(--bg)}
      .auth-card{width:100%;max-width:440px}
      .auth-card.wide{max-width:560px}
      .auth-card h2{font-weight:800;letter-spacing:-.02em}
      @media (max-width:991.98px){
        .auth{grid-template-columns:1fr}
        .auth-hero{padding:1.5rem 1.5rem 1.75rem}
        .auth-hero h1{font-size:1.5rem;margin:1rem 0 .5rem}
        .auth-hero ul,.auth-hero .auth-foot{display:none}
      }
    </style>
  </head>

  <body class="<?= $logged ? 'app' : 'guest' ?>">
<?php if ($logged): ?>
  <div class="sb-backdrop" data-sb-toggle></div>

  <!-- start navbar (sidebar) -->
  <aside class="sidebar" id="sidebar">
    <a class="sb-brand" href="dashboard.php">
      <span class="sb-logo"><i class="bi bi-mortarboard-fill"></i></span>
      <span class="sb-title">SIPN<small>Pengelolaan Nilai</small></span>
    </a>

    <div class="sb-scroll">
      <div class="sb-label">Menu</div>
      <a class="sb-link<?= $cur === 'dashboard.php' ? ' active' : '' ?>" href="dashboard.php"><i class="bi bi-grid-1x2"></i><span>Dashboard</span></a>

      <?php if ($role === 'admin'): ?>
        <div class="sb-label">Data Master</div>
        <?php foreach ($grup_admin as $g):
            echo sb_grup(strtolower($g[0]), $g[0], $g[2], $g[1], 'Daftar ' . $g[0], '+ Tambah ' . $g[0], $cur, $aksi_q);
        endforeach; ?>
      <?php endif; ?>

      <?php if ($role === 'guru'): ?>
        <div class="sb-label">Penilaian</div>
        <?= sb_grup('nilai', 'Nilai Siswa', 'bi-clipboard2-data', 'nilai.php', 'Daftar Nilai', '+ Input Nilai', $cur, $aksi_q) ?>
      <?php endif; ?>

      <?php if ($role === 'siswa'): ?>
        <div class="sb-label">Akademik</div>
        <a class="sb-link<?= $cur === 'nilai_saya.php' ? ' active' : '' ?>" href="nilai_saya.php"><i class="bi bi-clipboard2-data"></i><span>Nilai Saya</span></a>
      <?php endif; ?>

      <div class="sb-label">Akun</div>
      <a class="sb-link<?= ($cur === 'profil.php' && $aksi_q !== 'edit') ? ' active' : '' ?>" href="profil.php"><i class="bi bi-person-circle"></i><span>Profil</span></a>
      <a class="sb-link<?= ($cur === 'profil.php' && $aksi_q === 'edit') ? ' active' : '' ?>" href="profil.php?aksi=edit"><i class="bi bi-pencil-square"></i><span>Edit Profil</span></a>
      <a class="sb-link" href="logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
    </div>

    <div class="sb-user">
      <div class="sb-avatar"><?= e($inisial) ?></div>
      <div class="overflow-hidden">
        <div class="sb-user-name"><?= e($nama_user) ?></div>
        <div class="sb-user-role"><?= e($role) ?></div>
      </div>
    </div>
  </aside>
  <!-- end navbar -->

  <div class="app-main">
    <header class="topbar">
      <button class="t-burger" type="button" data-sb-toggle aria-label="Buka menu"><i class="bi bi-list"></i></button>
      <div class="t-title"><?= e($judul) ?></div>
      <div class="ms-auto text-muted small d-none d-md-block"><i class="bi bi-calendar3 me-1"></i><?= date('d M Y') ?></div>
    </header>
    <main class="app-content">
<?php endif; ?>
