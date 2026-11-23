<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "<script>alert('Akses ditolak!'); window.location.href = '../index.php';</script>";
    exit;
}

require_once __DIR__ . '/../koneksi.php';

// Logic CREATE STASIUN
if (isset($_POST['kirim'])) {
    $kode_stasiun = strtoupper($_POST['kode_stasiun']);
    $nama         = $_POST['nama'];
    $kota         = $_POST['kota'];

    $query = "INSERT INTO `stasiun` (`kode_stasiun`, `nama`, `kota`) VALUES ('$kode_stasiun', '$nama', '$kota')";
    $result = mysqli_query($conn, $query);

    if ($result) {
        echo "<script>alert('Stasiun berhasil ditambah'); window.location.href='stasiun.php';</script>";
        exit;
    } else {
        echo "<script>alert('Stasiun gagal ditambah');</script>";
    }
}

// Logic DELETE STASIUN
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete = mysqli_query($conn, "DELETE FROM `stasiun` WHERE id = '$id'");

    if ($delete) {
        echo "<script>alert('Stasiun berhasil dihapus'); window.location.href='stasiun.php';</script>";
        exit;
    }
}

// Logic UPDATE STASIUN
if (isset($_POST['update'])) {
    $id           = $_POST['update'];
    $kode_stasiun = strtoupper($_POST['kode_stasiun']);
    $nama         = $_POST['nama'];
    $kota         = $_POST['kota'];

    $query = "UPDATE `stasiun` SET `kode_stasiun`='$kode_stasiun', `nama`='$nama', `kota`='$kota', `updated_at`=NOW() WHERE id = '$id'";
    $update = mysqli_query($conn, $query);

    if ($update) {
        echo "<script>alert('Stasiun berhasil diupdate'); window.location.href='stasiun.php';</script>";
        exit;
    } else {
        echo "<script>alert('Stasiun gagal diupdate');</script>";
    }
}

// AMBIL DATA UNTUK FORM EDIT
$id = $kode_stasiun = $nama = $kota = '';
if (isset($_GET['update'])) {
    $id_edit = $_GET['update'];
    $res_edit = mysqli_query($conn, "SELECT * FROM `stasiun` WHERE id = '$id_edit'");
    $data = mysqli_fetch_assoc($res_edit);

    if ($data) {
        $id           = $data['id'];
        $kode_stasiun = $data['kode_stasiun'];
        $nama         = $data['nama'];
        $kota         = $data['kota'];
    }
}

// READ STASIUN
$result = mysqli_query($conn, "SELECT * FROM `stasiun` ORDER BY id DESC");
$no = 1;
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Stasiun - FastTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand navbar-light bg-white w-100 px-3" style="height: 75px; border-bottom: 1px solid #dee2e6;">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php">FastTrack</a>
            <ul class="navbar-nav ms-auto gap-3">
                <li class="nav-item"><a class="nav-link text-dark" href="dashboard.php">Jadwal</a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="kereta.php">Kereta</a></li>
                <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="stasiun.php">Stasiun</a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="pemesanan.php">Pemesanan</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">Keluar</a></li>
            </ul>
        </div>
    </nav>

    <div class="container py-4">

        <!-- Form Tambah / Edit Stasiun -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3"><?= $id != '' ? 'Edit Data Stasiun' : 'Tambah Stasiun Baru' ?></h5>
                <form action="" method="post" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Kode Stasiun</label>
                        <input type="text" name="kode_stasiun" class="form-control" placeholder="Contoh: GMR / BDG" required value="<?= $kode_stasiun ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nama Stasiun</label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: Stasiun Gambir" required value="<?= $nama ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kota</label>
                        <input type="text" name="kota" class="form-control" placeholder="Contoh: Jakarta" required value="<?= $kota ?>">
                    </div>
                    <div class="col-12">
                        <?php if ($id != '') { ?>
                            <button type="submit" name="update" value="<?= $id ?>" class="btn btn-warning">Update Stasiun</button>
                            <a href="stasiun.php" class="btn btn-secondary">Batal</a>
                        <?php } else { ?>
                            <button type="submit" name="kirim" class="btn btn-dark">+ Tambah Stasiun</button>
                        <?php } ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Data Stasiun -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="mb-3">Data Master Stasiun</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Stasiun</th>
                                <th>Kota</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td class="fw-bold"><?= $row['kode_stasiun']; ?></td>
                                    <td><?= $row['nama']; ?></td>
                                    <td><?= $row['kota']; ?></td>
                                    <td>
                                        <a href="?update=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Hapus stasiun ini?')" class="btn btn-danger btn-sm">Hapus</a>
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