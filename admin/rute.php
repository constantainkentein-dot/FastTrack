<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "<script>alert('Akses ditolak!'); window.location.href = '../index.php';</script>";
    exit;
}

require_once __DIR__ . '/../koneksi.php';

$id_jadwal = $_GET['id_jadwal'];

// Ambil info jadwal buat judul halaman
$q_jadwal = "SELECT j.*, k.nama AS nama_kereta, s1.nama AS asal, s2.nama AS tujuan
             FROM jadwal j
             JOIN kereta k ON j.id_kereta = k.id
             JOIN stasiun s1 ON j.id_stasiun_asal = s1.id
             JOIN stasiun s2 ON j.id_stasiun_tujuan = s2.id
             WHERE j.id = '$id_jadwal'";
$jadwal = mysqli_fetch_assoc(mysqli_query($conn, $q_jadwal));

// Logic TAMBAH STASIUN KE RUTE
if (isset($_POST['tambah'])) {
    $id_stasiun = $_POST['id_stasiun'];
    $urutan     = $_POST['urutan'];

    $query = "INSERT INTO `rute_jadwal` (`id_jadwal`, `id_stasiun`, `urutan`) 
              VALUES ('$id_jadwal', '$id_stasiun', '$urutan')";
    $result = mysqli_query($conn, $query);

    if ($result) {
        echo "<script>alert('Stasiun rute berhasil ditambah'); window.location.href='rute.php?id_jadwal=$id_jadwal';</script>";
        exit;
    }
}

// Logic HAPUS STASIUN DARI RUTE
if (isset($_GET['delete'])) {
    $id_rute = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM `rute_jadwal` WHERE id = '$id_rute'");
    echo "<script>alert('Stasiun dihapus dari rute'); window.location.href='rute.php?id_jadwal=$id_jadwal';</script>";
    exit;
}

// Ambil daftar semua stasiun buat dropdown
$stasiun_list = mysqli_query($conn, "SELECT * FROM stasiun");

// Ambil daftar rute jadwal ini (diurutkan dari urutan 1, 2, 3...)
$q_rute = "SELECT r.*, s.kode_stasiun, s.nama AS nama_stasiun, s.kota 
           FROM rute_jadwal r 
           JOIN stasiun s ON r.id_stasiun = s.id 
           WHERE r.id_jadwal = '$id_jadwal' 
           ORDER BY r.urutan ASC";
$rute_list = mysqli_query($conn, $q_rute);

// Otomatis hitung saran nomor urutan berikutnya
$q_max = mysqli_query($conn, "SELECT MAX(urutan) AS max_urutan FROM rute_jadwal WHERE id_jadwal = '$id_jadwal'");
$next_urutan = mysqli_fetch_assoc($q_max)['max_urutan'] + 1;
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Atur Rute - FastTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light py-4">
    <div class="container" style="max-width: 750px;">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">Atur Rute Transit</h4>
                <small class="text-muted">
                    <?= $jadwal['nama_kereta'] ?> (<?= $jadwal['asal'] ?> &rarr; <?= $jadwal['tujuan'] ?>)
                </small>
            </div>
            <a href="dashboard.php" class="btn btn-secondary btn-sm">&lt; Kembali ke Jadwal</a>
        </div>

        <!-- Form Tambah Stasiun Transit -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="" method="post" class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Stasiun yang Dilewati</label>
                        <select name="id_stasiun" class="form-select" required>
                            <option value="" disabled selected>Pilih Stasiun</option>
                            <?php while ($s = mysqli_fetch_assoc($stasiun_list)) { ?>
                                <option value="<?= $s['id'] ?>"><?= $s['kode_stasiun'] ?> - <?= $s['nama'] ?> (<?= $s['kota'] ?>)</option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Urutan Ke-</label>
                        <input type="number" name="urutan" class="form-control" value="<?= $next_urutan ?>" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" name="tambah" class="btn btn-dark w-100">+ Tambah</button>
                    </div>
                </form>
                <small class="text-muted d-block mt-2">
                    *Pastikan masukkan stasiun awal (urutan 1), stasiun transit (2, 3, ...), sampai stasiun tujuan akhir.
                </small>
            </div>
        </div>

        <!-- Tabel Rute -->
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-bordered text-center align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Urutan</th>
                            <th>Kode</th>
                            <th>Nama Stasiun</th>
                            <th>Kota</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($rute_list) > 0) { ?>
                            <?php while ($r = mysqli_fetch_assoc($rute_list)) { ?>
                                <tr>
                                    <td class="fw-bold"><?= $r['urutan'] ?></td>
                                    <td><?= $r['kode_stasiun'] ?></td>
                                    <td><?= $r['nama_stasiun'] ?></td>
                                    <td><?= $r['kota'] ?></td>
                                    <td>
                                        <a href="?id_jadwal=<?= $id_jadwal ?>&delete=<?= $r['id'] ?>"
                                            onclick="return confirm('Hapus stasiun ini dari rute?')"
                                            class="btn btn-danger btn-sm">Hapus</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="5" class="text-muted py-3">Belum ada rute stasiun yang diatur.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>

</html>