<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'penumpang') {
    echo "<script>alert('Akses ditolak!'); window.location.href = '../index.php';</script>";
    exit;
}

require_once __DIR__ . '/../koneksi.php';

$id_user = $_SESSION['id'];
$id_pemesanan = $_GET['id'];

$query = "SELECT 
            p.*, 
            u.nama AS nama_penumpang,
            k.nama AS nama_kereta, 
            j.tanggal_waktu_berangkat,
            j.tanggal_waktu_tiba,
            IFNULL(s1.nama, sa.nama) AS stasiun_naik,
            IFNULL(s2.nama, st.nama) AS stasiun_turun,
            IFNULL(s1.kode_stasiun, sa.kode_stasiun) AS kode_naik,
            IFNULL(s2.kode_stasiun, st.kode_stasiun) AS kode_turun
          FROM pemesanan p
          JOIN users u ON p.id_user = u.id
          JOIN jadwal j ON p.id_jadwal = j.id
          JOIN kereta k ON j.id_kereta = k.id
          JOIN stasiun sa ON j.id_stasiun_asal = sa.id
          JOIN stasiun st ON j.id_stasiun_tujuan = st.id
          LEFT JOIN rute_jadwal r1 ON r1.id_jadwal = p.id_jadwal AND r1.urutan = p.urutan_naik
          LEFT JOIN stasiun s1 ON s1.id = r1.id_stasiun
          LEFT JOIN rute_jadwal r2 ON r2.id_jadwal = p.id_jadwal AND r2.urutan = p.urutan_turun
          LEFT JOIN stasiun s2 ON s2.id = r2.id_stasiun
          WHERE p.id = '$id_pemesanan' AND p.id_user = '$id_user'";

$result = mysqli_query($conn, $query);
$tiket = mysqli_fetch_assoc($result);

if (!$tiket) {
    echo "<script>alert('Tiket tidak ditemukan!'); window.location.href = 'riwayat.php';</script>";
    exit;
}
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>e-Tiket FastTrack - FT-<?= str_pad($tiket['id'], 4, '0', STR_PAD_LEFT) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body { background: white !important; }
            .card { border: 1px solid #000 !important; box-shadow: none !important; }
        }
    </style>
</head>

<body class="bg-light py-5">
    <div class="container" style="max-width: 650px;">

        <!-- Tombol Cetak (otomatis hilang pas diprint) -->
        <div class="d-flex justify-content-between mb-3 d-print-none">
            <a href="riwayat.php" class="btn btn-outline-dark btn-sm rounded-1">&lt; Kembali</a>
            <button onclick="window.print()" class="btn btn-dark btn-sm rounded-1">Cetak / Simpan PDF</button>
        </div>

        <!-- Kartu e-Tiket -->
        <div class="card shadow-sm rounded-1">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                <div>
                    <h4 class="fw-bold mb-0">FastTrack</h4>
                    <small class="text-white-50">E-TICKET / BOARDING PASS</small>
                </div>
                <div class="text-end">
                    <small class="text-white-50 d-block">KODE BOOKING</small>
                    <h5 class="fw-bold mb-0">FT-<?= str_pad($tiket['id'], 4, '0', STR_PAD_LEFT) ?></h5>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row mb-4 pb-3 border-bottom">
                    <div class="col-5">
                        <small class="text-muted d-block">STASIUN ASAL</small>
                        <h3 class="fw-bold mb-0"><?= $tiket['kode_naik'] ?></h3>
                        <small><?= $tiket['stasiun_naik'] ?></small>
                    </div>
                    <div class="col-2 d-flex align-items-center justify-content-center">
                        <h4 class="mb-0">&rarr;</h4>
                    </div>
                    <div class="col-5 text-end">
                        <small class="text-muted d-block">STASIUN TUJUAN</small>
                        <h3 class="fw-bold mb-0"><?= $tiket['kode_turun'] ?></h3>
                        <small><?= $tiket['stasiun_turun'] ?></small>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <small class="text-muted d-block">NAMA PENUMPANG</small>
                        <span class="fw-semibold"><?= $tiket['nama_penumpang'] ?></span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">KERETA</small>
                        <span class="fw-semibold"><?= $tiket['nama_kereta'] ?></span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">WAKTU BERANGKAT</small>
                        <span class="fw-semibold"><?= date('d M Y, H:i', strtotime($tiket['tanggal_waktu_berangkat'])) ?> WIB</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">GERBONG / KURSI</small>
                        <span class="fw-bold"><?= $tiket['kursi'] ?> (<?= $tiket['jumlah_tiket'] ?> Tiket)</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <div>
                        <small class="text-muted d-block">STATUS PEMBAYARAN</small>
                        <span class="badge bg-success rounded-1"><?= strtoupper($tiket['status']) ?></span>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">TOTAL BAYAR</small>
                        <h5 class="fw-bold mb-0">Rp <?= number_format($tiket['total_bayar'], 0, ',', '.') ?></h5>
                    </div>
                </div>
            </div>
        </div>

    </div>
</body>

</html>