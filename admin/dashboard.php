<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "<script>
        alert('Akses ditolak! Silakan login sebagai admin.');
        window.location.href = '../index.php';
    </script>";
    exit;
}

require_once __DIR__ . '/../koneksi.php';

// Logic CREATE JADWAL
if (isset($_POST['kirim'])) {
    $id_kereta        = $_POST['id_kereta'];
    $id_stasiun_asal  = $_POST['id_stasiun_asal'];
    $id_stasiun_tujuan = $_POST['id_stasiun_tujuan'];
    $berangkat        = str_replace('T', ' ', $_POST['tanggal_waktu_berangkat']);
    $tiba             = str_replace('T', ' ', $_POST['tanggal_waktu_tiba']);
    $harga            = $_POST['harga'];

    $query = "INSERT INTO `jadwal` (`id_kereta`, `id_stasiun_asal`, `id_stasiun_tujuan`, `tanggal_waktu_berangkat`, `tanggal_waktu_tiba`, `harga`) 
              VALUES ('$id_kereta', '$id_stasiun_asal', '$id_stasiun_tujuan', '$berangkat', '$tiba', '$harga')";
    $result = mysqli_query($conn, $query);

    if ($result) {
        echo "<script>alert('Jadwal berhasil ditambah'); window.location.href='dashboard.php';</script>";
        exit;
    } else {
        echo "<script>alert('Jadwal gagal ditambah');</script>";
    }
}

// Logic DELETE JADWAL
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete = mysqli_query($conn, "DELETE FROM `jadwal` WHERE id = '$id'");

    if ($delete) {
        echo "<script>alert('Jadwal berhasil dihapus'); window.location.href='dashboard.php';</script>";
        exit;
    }
}

// Logic UPDATE JADWAL
if (isset($_POST['update'])) {
    $id                = $_POST['update'];
    $id_kereta         = $_POST['id_kereta'];
    $id_stasiun_asal   = $_POST['id_stasiun_asal'];
    $id_stasiun_tujuan = $_POST['id_stasiun_tujuan'];
    $berangkat         = str_replace('T', ' ', $_POST['tanggal_waktu_berangkat']);
    $tiba              = str_replace('T', ' ', $_POST['tanggal_waktu_tiba']);
    $harga             = $_POST['harga'];

    $query = "UPDATE `jadwal` SET 
                `id_kereta`='$id_kereta', 
                `id_stasiun_asal`='$id_stasiun_asal', 
                `id_stasiun_tujuan`='$id_stasiun_tujuan', 
                `tanggal_waktu_berangkat`='$berangkat', 
                `tanggal_waktu_tiba`='$tiba', 
                `harga`='$harga',
                `updated_at`=NOW()
              WHERE id = '$id'";
    $update = mysqli_query($conn, $query);

    if ($update) {
        echo "<script>alert('Jadwal berhasil diupdate'); window.location.href='dashboard.php';</script>";
        exit;
    } else {
        echo "<script>alert('Jadwal gagal diupdate');</script>";
    }
}

// AMBIL DATA UNTUK FORM EDIT
$id = $id_kereta = $id_stasiun_asal = $id_stasiun_tujuan = $berangkat = $tiba = $harga = '';
if (isset($_GET['update'])) {
    $id_edit = $_GET['update'];
    $res_edit = mysqli_query($conn, "SELECT * FROM `jadwal` WHERE id = '$id_edit'");
    $data = mysqli_fetch_assoc($res_edit);

    if ($data) {
        $id                = $data['id'];
        $id_kereta         = $data['id_kereta'];
        $id_stasiun_asal   = $data['id_stasiun_asal'];
        $id_stasiun_tujuan = $data['id_stasiun_tujuan'];
        $berangkat         = date('Y-m-d\TH:i', strtotime($data['tanggal_waktu_berangkat']));
        $tiba              = date('Y-m-d\TH:i', strtotime($data['tanggal_waktu_tiba']));
        $harga             = $data['harga'];
    }
}

// Ambil data dropdown Kereta & Stasiun
$kereta_list  = mysqli_query($conn, "SELECT * FROM kereta");
$stasiun_asal = mysqli_query($conn, "SELECT * FROM stasiun");
$stasiun_tujuan = mysqli_query($conn, "SELECT * FROM stasiun");

// READ JADWAL
$query = "SELECT 
            j.*, 
            k.nama AS nama_kereta, 
            s_asal.kode_stasiun AS stasiun_asal, 
            s_akhir.kode_stasiun AS stasiun_akhir 
          FROM jadwal j 
          LEFT JOIN kereta k ON j.id_kereta = k.id 
          LEFT JOIN stasiun s_asal ON j.id_stasiun_asal = s_asal.id 
          LEFT JOIN stasiun s_akhir ON j.id_stasiun_tujuan = s_akhir.id
          ORDER BY j.id DESC";
$result = mysqli_query($conn, $query);
$no = 1;
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - FastTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand navbar-light bg-white w-100 px-3" style="height: 75px; border-bottom: 1px solid #dee2e6;">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php">FastTrack</a>
            <ul class="navbar-nav ms-auto gap-3">
                <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="dashboard.php">Jadwal</a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="kereta.php">Kereta</a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="stasiun.php">Stasiun</a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="pemesanan.php">Pemesanan</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">Keluar</a></li>
            </ul>
        </div>
    </nav>

    <div class="container py-4">

        <!-- Form Tambah / Edit Jadwal -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3"><?= $id != '' ? 'Edit Jadwal Perjalanan' : 'Tambah Jadwal Baru' ?></h5>
                <form action="" method="post" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Kereta</label>
                        <select name="id_kereta" class="form-select" required>
                            <option value="" disabled <?= $id_kereta == '' ? 'selected' : '' ?>>Pilih Kereta</option>
                            <?php while ($k = mysqli_fetch_assoc($kereta_list)) { ?>
                                <option value="<?= $k['id'] ?>" <?= $id_kereta == $k['id'] ? 'selected' : '' ?>>
                                    <?= $k['nama'] ?> (Kap: <?= $k['kapasitas'] ?>)
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Stasiun Asal</label>
                        <select name="id_stasiun_asal" class="form-select" required>
                            <option value="" disabled <?= $id_stasiun_asal == '' ? 'selected' : '' ?>>Pilih Stasiun Asal</option>
                            <?php while ($s = mysqli_fetch_assoc($stasiun_asal)) { ?>
                                <option value="<?= $s['id'] ?>" <?= $id_stasiun_asal == $s['id'] ? 'selected' : '' ?>>
                                    <?= $s['kode_stasiun'] ?> - <?= $s['nama'] ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Stasiun Tujuan</label>
                        <select name="id_stasiun_tujuan" class="form-select" required>
                            <option value="" disabled <?= $id_stasiun_tujuan == '' ? 'selected' : '' ?>>Pilih Stasiun Tujuan</option>
                            <?php while ($s = mysqli_fetch_assoc($stasiun_tujuan)) { ?>
                                <option value="<?= $s['id'] ?>" <?= $id_stasiun_tujuan == $s['id'] ? 'selected' : '' ?>>
                                    <?= $s['kode_stasiun'] ?> - <?= $s['nama'] ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Waktu Berangkat</label>
                        <input type="datetime-local" name="tanggal_waktu_berangkat" class="form-control" required value="<?= $berangkat ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Waktu Tiba</label>
                        <input type="datetime-local" name="tanggal_waktu_tiba" class="form-control" required value="<?= $tiba ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Harga (Rp)</label>
                        <input type="number" name="harga" class="form-control" placeholder="150000" required value="<?= $harga ?>">
                    </div>

                    <div class="col-12">
                        <?php if ($id != '') { ?>
                            <button type="submit" name="update" value="<?= $id ?>" class="btn btn-warning">Update Jadwal</button>
                            <a href="dashboard.php" class="btn btn-secondary">Batal</a>
                        <?php } else { ?>
                            <button type="submit" name="kirim" class="btn btn-dark">+ Tambah Jadwal Baru</button>
                        <?php } ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Data Jadwal -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="mb-3">Data Jadwal Perjalanan</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Kereta</th>
                                <th>Rute</th>
                                <th>Waktu Berangkat</th>
                                <th>Waktu Tiba</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= $row['nama_kereta']; ?></td>
                                    <td><?= $row['stasiun_asal'] . ' &rarr; ' . $row['stasiun_akhir'] ?></td>
                                    <td><?= date('d-m-y H:i', strtotime($row['tanggal_waktu_berangkat'])); ?></td>
                                    <td><?= date('d-m-y H:i', strtotime($row['tanggal_waktu_tiba'])); ?></td>
                                    <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                                    <td>
                                        <a href="rute.php?id_jadwal=<?= $row['id'] ?>" class="btn btn-info btn-sm">Rute</a>
                                        <a href="?update=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Hapus jadwal ini?')" class="btn btn-danger btn-sm">Hapus</a>
                                    </td>
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