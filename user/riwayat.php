<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'penumpang') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href = '../index.php';</script>";
    exit;
}

require_once __DIR__ . '/../koneksi.php';

$id_user = $_SESSION['id'];

$query = "SELECT 
            p.*, 
            k.nama AS nama_kereta, 
            j.tanggal_waktu_berangkat,
            j.tanggal_waktu_tiba,
            IFNULL(s1.nama, sa.nama) AS stasiun_naik,
            IFNULL(s2.nama, st.nama) AS stasiun_turun,
            IFNULL(s1.kode_stasiun, sa.kode_stasiun) AS kode_naik,
            IFNULL(s2.kode_stasiun, st.kode_stasiun) AS kode_turun
          FROM pemesanan p
          JOIN jadwal j ON p.id_jadwal = j.id
          JOIN kereta k ON j.id_kereta = k.id
          JOIN stasiun sa ON j.id_stasiun_asal = sa.id
          JOIN stasiun st ON j.id_stasiun_tujuan = st.id
          LEFT JOIN rute_jadwal r1 ON r1.id_jadwal = p.id_jadwal AND r1.urutan = p.urutan_naik
          LEFT JOIN stasiun s1 ON s1.id = r1.id_stasiun
          LEFT JOIN rute_jadwal r2 ON r2.id_jadwal = p.id_jadwal AND r2.urutan = p.urutan_turun
          LEFT JOIN stasiun s2 ON s2.id = r2.id_stasiun
          WHERE p.id_user = '$id_user'
          ORDER BY p.id DESC";
$result = mysqli_query($conn, $query);
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Pemesanan - FastTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand navbar-light bg-white w-100 px-3" style="height: 75px; border-bottom: 1px solid #dee2e6;">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php">FastTrack</a>
            <ul class="navbar-nav ms-auto gap-3">
                <li class="nav-item">
                    <span class="nav-link text-dark fw-medium">Halo, <?= $_SESSION['nama'] ?? 'Penumpang' ?></span>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark fw-semibold" href="riwayat.php">Riwayat</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="../logout.php">Keluar</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container py-5" style="max-width: 900px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">Riwayat Pemesanan Tiket</h3>
            <a href="dashboard.php" class="btn btn-outline-dark btn-sm rounded-1">&lt; Cari Tiket Lagi</a>
        </div>

        <?php if (mysqli_num_rows($result) > 0) { ?>
            <div class="d-flex flex-column gap-3">
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <div class="card shadow-sm rounded-1">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1"><?= $row['nama_kereta'] ?></h5>
                                    <small class="text-muted">
                                        Kode Booking: <strong>FT-<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></strong>
                                        &bull; Dipesan: <?= date('d-m-Y H:i', strtotime($row['tanggal_pesan'])) ?>
                                    </small>
                                </div>
                                <span class="badge <?= $row['status'] == 'lunas' ? 'bg-success' : 'bg-warning text-dark' ?> rounded-1 px-3 py-2">
                                    <?= strtoupper($row['status']) ?>
                                </span>
                            </div>

                            <div class="row g-3 align-items-center">
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Rute Perjalanan</small>
                                    <span class="fw-semibold">
                                        <?= $row['kode_naik'] ?> (<?= $row['stasiun_naik'] ?>) &rarr; <?= $row['kode_turun'] ?> (<?= $row['stasiun_turun'] ?>)
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Keberangkatan</small>
                                    <span class="fw-semibold"><?= date('d-m-Y, H:i', strtotime($row['tanggal_waktu_berangkat'])) ?> WIB</span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Kursi (<?= $row['jumlah_tiket'] ?> Tiket)</small>
                                    <span class="badge bg-dark rounded-1"><?= $row['kursi'] ?></span>
                                </div>
                                <div class="col-md-2 text-md-end">
                                    <small class="text-muted d-block">Total Bayar</small>
                                    <span class="fw-bold">Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?></span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-3 pt-3 border-top">
                                <a href="tiket.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-dark btn-sm rounded-1">
                                    Cetak e-Tiket
                                </a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <div class="alert alert-light border text-center py-5 rounded-1">
                <p class="text-muted mb-3">Kamu belum pernah memesan tiket.</p>
                <a href="dashboard.php" class="btn btn-dark rounded-1">Cari Tiket Sekarang</a>
            </div>
        <?php } ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>