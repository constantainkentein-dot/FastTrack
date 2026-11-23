<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "<script>alert('Akses ditolak!'); window.location.href = '../index.php';</script>";
    exit;
}

require_once __DIR__ . '/../koneksi.php';

// Logic CREATE KERETA
if (isset($_POST['kirim'])) {
    $nama      = $_POST['nama'];
    $kapasitas = $_POST['kapasitas'];

    $query = "INSERT INTO `kereta` (`nama`, `kapasitas`) VALUES ('$nama', '$kapasitas')";
    $result = mysqli_query($conn, $query);

    if ($result) {
        echo "<script>alert('Kereta berhasil ditambah'); window.location.href='kereta.php';</script>";
        exit;
    } else {
        echo "<script>alert('Kereta gagal ditambah');</script>";
    }
}

// Logic DELETE KERETA
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete = mysqli_query($conn, "DELETE FROM `kereta` WHERE id = '$id'");

    if ($delete) {
        echo "<script>alert('Kereta berhasil dihapus'); window.location.href='kereta.php';</script>";
        exit;
    }
}

// Logic UPDATE KERETA
if (isset($_POST['update'])) {
    $id        = $_POST['update'];
    $nama      = $_POST['nama'];
    $kapasitas = $_POST['kapasitas'];

    $query = "UPDATE `kereta` SET `nama`='$nama', `kapasitas`='$kapasitas', `updated_at`=NOW() WHERE id = '$id'";
    $update = mysqli_query($conn, $query);

    if ($update) {
        echo "<script>alert('Kereta berhasil diupdate'); window.location.href='kereta.php';</script>";
        exit;
    } else {
        echo "<script>alert('Kereta gagal diupdate');</script>";
    }
}

// AMBIL DATA UNTUK FORM EDIT
$id = $nama = $kapasitas = '';
if (isset($_GET['update'])) {
    $id_edit = $_GET['update'];
    $res_edit = mysqli_query($conn, "SELECT * FROM `kereta` WHERE id = '$id_edit'");
    $data = mysqli_fetch_assoc($res_edit);

    if ($data) {
        $id        = $data['id'];
        $nama      = $data['nama'];
        $kapasitas = $data['kapasitas'];
    }
}

// READ KERETA
$result = mysqli_query($conn, "SELECT * FROM `kereta` ORDER BY id DESC");
$no = 1;
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Kereta - FastTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand navbar-light bg-white w-100 px-3" style="height: 75px; border-bottom: 1px solid #dee2e6;">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php">FastTrack</a>
            <ul class="navbar-nav ms-auto gap-3">
                <li class="nav-item"><a class="nav-link text-dark" href="dashboard.php">Jadwal</a></li>
                <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="kereta.php">Kereta</a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="stasiun.php">Stasiun</a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="pemesanan.php">Pemesanan</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">Keluar</a></li>
            </ul>
        </div>
    </nav>

    <div class="container py-4">

        <!-- Form Tambah / Edit Kereta -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3"><?= $id != '' ? 'Edit Data Kereta' : 'Tambah Kereta Baru' ?></h5>
                <form action="" method="post" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Kereta</label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: Argo Bromo" required value="<?= $nama ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kapasitas (Total Kursi)</label>
                        <input type="number" name="kapasitas" class="form-control" placeholder="Contoh: 60" required value="<?= $kapasitas ?>">
                    </div>
                    <div class="col-12">
                        <?php if ($id != '') { ?>
                            <button type="submit" name="update" value="<?= $id ?>" class="btn btn-warning">Update Kereta</button>
                            <a href="kereta.php" class="btn btn-secondary">Batal</a>
                        <?php } else { ?>
                            <button type="submit" name="kirim" class="btn btn-dark">+ Tambah Kereta</button>
                        <?php } ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Data Kereta -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="mb-3">Data Master Kereta</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Kereta</th>
                                <th>Kapasitas</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= $row['nama']; ?></td>
                                    <td><?= $row['kapasitas']; ?> Kursi</td>
                                    <td>
                                        <a href="?update=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Hapus kereta ini?')" class="btn btn-danger btn-sm">Hapus</a>
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