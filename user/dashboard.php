<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'penumpang') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href = '../index.php';</script>";
    exit;
}

require_once __DIR__ . '/../koneksi.php';

// Ambil data stasiun buat dropdown
$stasiun_asal   = mysqli_query($conn, "SELECT * FROM stasiun ORDER BY nama ASC");
$stasiun_tujuan = mysqli_query($conn, "SELECT * FROM stasiun ORDER BY nama ASC");

// Ambil parameter pencarian
$asal    = isset($_GET['asal']) ? $_GET['asal'] : '';
$tujuan  = isset($_GET['tujuan']) ? $_GET['tujuan'] : '';
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

// Logic searching dan pagination
$limit = 6; // 6 card per halaman (2 baris x 3 kolom)
$page  = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

if (isset($_GET['cari']) && $asal != '' && $tujuan != '' && $tanggal != '') {
    // MODE FILTER (Pencarian termasuk stasiun transit)
    $countQuery = "SELECT COUNT(*) AS total
                   FROM jadwal j
                   JOIN rute_jadwal r1 ON r1.id_jadwal = j.id
                   JOIN rute_jadwal r2 ON r2.id_jadwal = j.id
                   WHERE r1.id_stasiun = '$asal'
                     AND r2.id_stasiun = '$tujuan'
                     AND r1.urutan < r2.urutan
                     AND DATE(j.tanggal_waktu_berangkat) = '$tanggal'";

    $query = "SELECT 
                j.*, 
                k.nama AS nama_kereta,
                r1.urutan AS urutan_naik,
                r2.urutan AS urutan_turun,
                s1.nama AS nama_asal,
                s2.nama AS nama_tujuan
              FROM jadwal j
              JOIN kereta k ON j.id_kereta = k.id
              JOIN rute_jadwal r1 ON r1.id_jadwal = j.id
              JOIN rute_jadwal r2 ON r2.id_jadwal = j.id
              JOIN stasiun s1 ON r1.id_stasiun = s1.id
              JOIN stasiun s2 ON r2.id_stasiun = s2.id
              WHERE r1.id_stasiun = '$asal'
                AND r2.id_stasiun = '$tujuan'
                AND r1.urutan < r2.urutan
                AND DATE(j.tanggal_waktu_berangkat) = '$tanggal'
              ORDER BY j.tanggal_waktu_berangkat ASC
              LIMIT $limit OFFSET $offset";

    $searchParam = "&asal=$asal&tujuan=$tujuan&tanggal=$tanggal&cari=";
} else {
    // MODE DEFAULT (Tampilkan semua jadwal yang dibuat admin)
    $countQuery = "SELECT COUNT(*) AS total FROM jadwal";

    $query = "SELECT 
                j.*, 
                k.nama AS nama_kereta,
                IFNULL(r1.urutan, 1) AS urutan_naik,
                IFNULL(r2.urutan, 2) AS urutan_turun,
                s1.nama AS nama_asal,
                s2.nama AS nama_tujuan
              FROM jadwal j
              JOIN kereta k ON j.id_kereta = k.id
              JOIN stasiun s1 ON j.id_stasiun_asal = s1.id
              JOIN stasiun s2 ON j.id_stasiun_tujuan = s2.id
              LEFT JOIN rute_jadwal r1 ON r1.id_jadwal = j.id AND r1.id_stasiun = j.id_stasiun_asal
              LEFT JOIN rute_jadwal r2 ON r2.id_jadwal = j.id AND r2.id_stasiun = j.id_stasiun_tujuan
              ORDER BY j.tanggal_waktu_berangkat ASC
              LIMIT $limit OFFSET $offset";

    $searchParam = "";
}

// Hitung total halaman
$countResult = mysqli_query($conn, $countQuery);
$totalData   = mysqli_fetch_assoc($countResult)['total'];
$totalPages  = ceil($totalData / $limit);

// Eksekusi query data jadwal
$hasil = mysqli_query($conn, $query);
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FastTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        .hero {
            min-height: 340px;
            padding: 65px 0;
            background-image:
                linear-gradient(90deg,
                    rgba(8, 35, 82, 0.95),
                    rgba(8, 35, 82, 0.65),
                    rgba(8, 35, 82, 0.05)),
                url("../image/hero.jpeg");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: white;
        }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="header w-100">
        <nav class="navbar navbar-expand navbar-light bg-white w-100 px-3" style="height: 75px; border-bottom: 1px solid #dee2e6;">
            <div class="container-fluid">
                <a class="navbar-brand fw-bold" href="dashboard.php">FastTrack</a>
                <ul class="navbar-nav ms-auto gap-3">
                    <li class="nav-item">
                        <span class="nav-link text-dark fw-medium">Halo, <?= $_SESSION['nama'] ?? 'Penumpang' ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="riwayat.php">Riwayat</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../logout.php">Keluar</a>
                    </li>
                </ul>
            </div>
        </nav>

        <section class="hero">
            <div class="container">
                <h1>Perjalanan berikutnya<br>dimulai di sini.</h1>
                <p>Temukan perjalanan terbaik untuk tujuanmu.</p>
            </div>
        </section>
    </div>

    <div class="mx-auto" style="width: 90%; margin-top: 55px;">
        <h1 class="mb-3">Cari Tiket Kereta</h1>
        <form action="" method="get">
            <div class="input-bar mb-3">
                <label class="form-label">Stasiun Asal:</label>
                <select name="asal" class="form-select" required>
                    <option value="" disabled <?= $asal == '' ? 'selected' : '' ?>>Pilih Stasiun Asal</option>
                    <?php while ($s = mysqli_fetch_assoc($stasiun_asal)) { ?>
                        <option value="<?= $s['id'] ?>" <?= $asal == $s['id'] ? 'selected' : '' ?>>
                            <?= $s['kode_stasiun'] ?> - <?= $s['nama'] ?> (<?= $s['kota'] ?>)
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="input-bar mb-3">
                <label class="form-label">Stasiun Tujuan:</label>
                <select name="tujuan" class="form-select" required>
                    <option value="" disabled <?= $tujuan == '' ? 'selected' : '' ?>>Pilih Stasiun Tujuan</option>
                    <?php while ($s = mysqli_fetch_assoc($stasiun_tujuan)) { ?>
                        <option value="<?= $s['id'] ?>" <?= $tujuan == $s['id'] ? 'selected' : '' ?>>
                            <?= $s['kode_stasiun'] ?> - <?= $s['nama'] ?> (<?= $s['kota'] ?>)
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="input-bar mb-3">
                <label class="form-label">Tanggal Keberangkatan:</label>
                <input type="date" class="form-control" name="tanggal" value="<?= $tanggal ?>" required>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="cari" class="btn btn-dark rounded-1">CARI TIKET</button>
                <?php if (isset($_GET['cari'])) { ?>
                    <a href="dashboard.php" class="btn btn-secondary rounded-1">Reset</a>
                <?php } ?>
            </div>
        </form>
    </div>

    <div class="container p-4" style="margin-top: 40px;">
        <h3 class="mb-3">Penelusuran jadwal:</h3>
        <div class="row g-3">
            <?php if (mysqli_num_rows($hasil) > 0) { ?>
                <?php while ($row = mysqli_fetch_assoc($hasil)) { ?>
                    <div class="col-md-4">
                        <div class="card rounded-1 h-100">
                            <div class="card-body d-flex flex-column">
                                <div class="top d-flex justify-content-between">
                                    <h6 class="fw-bold"><?= $row['nama_kereta'] ?></h6>
                                    <?php
                                    $harga_tampil = hitungHarga($row['harga'], $row['tanggal_waktu_berangkat']);
                                    $is_weekend   = date('N', strtotime($row['tanggal_waktu_berangkat'])) >= 5;
                                    ?>
                                    <h6 class="text-primary fw-bold">
                                        Rp <?= number_format($harga_tampil, 0, ',', '.') ?>
                                        <?php if ($is_weekend) : ?>
                                            <span class="badge bg-warning text-dark ms-1" style="font-size: 10px;">Weekend</span>
                                        <?php endif; ?>
                                    </h6>
                                </div>
                                <small class="text-muted mb-2"><?= $row['nama_asal'] ?> &rarr; <?= $row['nama_tujuan'] ?></small>
                                <div style="margin-top: 10px;">
                                    <p class="mb-1">Tanggal: <strong><?= date('d-m-Y', strtotime($row['tanggal_waktu_berangkat'])) ?></strong></p>
                                    <p class="mb-1">Keberangkatan: <strong><?= date('H:i', strtotime($row['tanggal_waktu_berangkat'])) ?></strong></p>
                                    <p class="mb-3">Tiba: <strong><?= date('H:i', strtotime($row['tanggal_waktu_tiba'])) ?></strong></p>
                                </div>
                                <div class="d-flex justify-content-end mt-auto">
                                    <a href="pesan.php?id_jadwal=<?= $row['id'] ?>&naik=<?= $row['urutan_naik'] ?>&turun=<?= $row['urutan_turun'] ?>"
                                        class="btn btn-dark rounded-1">
                                        PESAN TIKET
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="col-12">
                    <div class="alert alert-warning rounded-1">
                        Jadwal kereta tidak ditemukan.
                    </div>
                </div>
            <?php } ?>
        </div>

        <!-- Navigasi Pagination -->
        <?php if ($totalPages > 1) { ?>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <small class="text-muted">
                    Menampilkan <strong><?= $offset + 1 ?></strong> -
                    <strong><?= min($offset + $limit, $totalData) ?></strong> dari
                    <strong><?= $totalData ?></strong> jadwal
                </small>

                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link text-dark" href="?page=<?= $page - 1 ?><?= $searchParam ?>">&laquo; Prev</a>
                        </li>

                        <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link <?= $i == $page ? 'bg-dark border-dark text-white' : 'text-dark' ?>"
                                    href="?page=<?= $i ?><?= $searchParam ?>"><?= $i ?></a>
                            </li>
                        <?php } ?>

                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link text-dark" href="?page=<?= $page + 1 ?><?= $searchParam ?>">Next &raquo;</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php } ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>