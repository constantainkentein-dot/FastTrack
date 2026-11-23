<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "<script>alert('Akses ditolak!'); window.location.href = '../index.php';</script>";
    exit;
}

require_once __DIR__ . '/../koneksi.php';

// Logic HAPUS PEMESANAN
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM `pemesanan` WHERE id = '$id'");
    echo "<script>alert('Data pemesanan dihapus'); window.location.href='pemesanan.php';</script>";
    exit;
}

// READ SEMUA PEMESANAN (Join ke user, jadwal, kereta, dan stasiun naik/turun)
$query = "SELECT 
            p.*, 
            u.nama AS nama_penumpang, 
            k.nama AS nama_kereta, 
            j.tanggal_waktu_berangkat,
            s1.kode_stasiun AS stasiun_naik,
            s2.kode_stasiun AS stasiun_turun
          FROM pemesanan p
          JOIN users u ON p.id_user = u.id
          JOIN jadwal j ON p.id_jadwal = j.id
          JOIN kereta k ON j.id_kereta = k.id
          LEFT JOIN rute_jadwal r1 ON r1.id_jadwal = p.id_jadwal AND r1.urutan = p.urutan_naik
          LEFT JOIN stasiun s1 ON s1.id = r1.id_stasiun
          LEFT JOIN rute_jadwal r2 ON r2.id_jadwal = p.id_jadwal AND r2.urutan = p.urutan_turun
          LEFT JOIN stasiun s2 ON s2.id = r2.id_stasiun
          ORDER BY p.id DESC";
$result = mysqli_query($conn, $query);
$no = 1;
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Pemesanan - FastTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand navbar-light bg-white w-100 px-3" style="height: 75px; border-bottom: 1px solid #dee2e6;">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php">FastTrack</a>
            <ul class="navbar-nav ms-auto gap-3">
                <li class="nav-item"><a class="nav-link text-dark" href="dashboard.php">Jadwal</a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="kereta.php">Kereta</a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="stasiun.php">Stasiun</a></li>
                <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="pemesanan.php">Pemesanan</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">Keluar</a></li>
            </ul>
        </div>
    </nav>

    <!-- Konten Utama -->
    <div class="flex-grow-1 p-4">
        <h3 class="fw-bold mb-4">Data Pemesanan Tiket</h3>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Penumpang</th>
                                <th>Kereta & Rute</th>
                                <th>Kursi</th>
                                <th>Total Bayar</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0) { ?>
                                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td>
                                            <div class="fw-semibold"><?= $row['nama_penumpang'] ?></div>
                                            <small class="text-muted"><?= date('d-m-Y H:i', strtotime($row['tanggal_pesan'])) ?></small>
                                        </td>
                                        <td>
                                            <div><?= $row['nama_kereta'] ?></div>
                                            <small class="text-muted"><?= $row['stasiun_naik'] ?> &rarr; <?= $row['stasiun_turun'] ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-dark"><?= $row['kursi'] ?></span>
                                            <div class="small text-muted">(<?= $row['jumlah_tiket'] ?> tiket)</div>
                                        </td>
                                        <td>Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?></td>
                                        <td>
                                            <?php if ($row['status'] == 'lunas') { ?>
                                                <span class="badge bg-success">Lunas</span>
                                            <?php } else { ?>
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="7" class="text-muted py-4">Belum ada data pemesanan.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>