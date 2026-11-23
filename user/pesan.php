<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'penumpang') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href = '../index.php';</script>";
    exit;
}

require_once __DIR__ . '/../koneksi.php';

$id_user   = $_SESSION['id'];
$id_jadwal = $_GET['id_jadwal'];
$naik      = $_GET['naik'];
$turun     = $_GET['turun'];
$gerbong   = isset($_GET['gerbong']) ? $_GET['gerbong'] : 'EKS-1';

// Validasi urutan naik & turun
if ($naik >= $turun) {
    echo "<script>alert('Stasiun turun harus setelah stasiun naik!'); history.back();</script>";
    exit;
}

// Ambil semua stasiun yang dilewati jadwal ini
$q_rute = "SELECT r.urutan, s.kode_stasiun, s.nama 
           FROM rute_jadwal r 
           JOIN stasiun s ON r.id_stasiun = s.id 
           WHERE r.id_jadwal = '$id_jadwal' 
           ORDER BY r.urutan ASC";
$res_rute = mysqli_query($conn, $q_rute);
$daftar_rute = [];
while ($r = mysqli_fetch_assoc($res_rute)) {
    $daftar_rute[] = $r;
}

// 1. Ambil detail jadwal, kereta, dan nama stasiun naik & turun
$q_jadwal = "SELECT 
                j.*, 
                k.nama AS nama_kereta, 
                k.kapasitas,
                IFNULL(s1.nama, sa.nama) AS stasiun_naik,
                IFNULL(s2.nama, st.nama) AS stasiun_turun
             FROM jadwal j
             JOIN kereta k ON j.id_kereta = k.id
             JOIN stasiun sa ON j.id_stasiun_asal = sa.id
             JOIN stasiun st ON j.id_stasiun_tujuan = st.id
             LEFT JOIN rute_jadwal r1 ON r1.id_jadwal = j.id AND r1.urutan = '$naik'
             LEFT JOIN stasiun s1 ON s1.id = r1.id_stasiun
             LEFT JOIN rute_jadwal r2 ON r2.id_jadwal = j.id AND r2.urutan = '$turun'
             LEFT JOIN stasiun s2 ON s2.id = r2.id_stasiun
             WHERE j.id = '$id_jadwal'";
$jadwal = mysqli_fetch_assoc(mysqli_query($conn, $q_jadwal));
$harga_final = hitungHarga($jadwal['harga'], $jadwal['tanggal_waktu_berangkat']);

// 2. Logic SIMPAN PEMESANAN (Pas klik Konfirmasi Bayar di Modal)
if (isset($_POST['bayar'])) {
    $kursi        = $_POST['kursi'];  
    $jumlah_tiket = $_POST['jumlah_tiket'];
    $total_bayar  = $jumlah_tiket * $jadwal['harga'];

    $query = "INSERT INTO `pemesanan` (`id_user`, `id_jadwal`, `tanggal_pesan`, `jumlah_tiket`, `kursi`, `urutan_naik`, `urutan_turun`, `total_bayar`, `status`) 
              VALUES ('$id_user', '$id_jadwal', NOW(), '$jumlah_tiket', '$kursi', '$naik', '$turun', '$total_bayar', 'lunas')";
    $result = mysqli_query($conn, $query);

    if ($result) {
        echo "<script>
            alert('Pembayaran berhasil! Tiket sudah diterbitkan.');
            window.location.href = 'riwayat.php';
        </script>";
        exit;
    } else {
        echo "<script>alert('Gagal memproses pesanan');</script>";
    }
}

// 3. Ambil kursi yang TERISI khusus di potongan rute yang bentrok
$kursi_terisi = [];
$q_terisi = "SELECT kursi FROM pemesanan 
             WHERE id_jadwal = '$id_jadwal' 
               AND urutan_naik < '$turun' 
               AND urutan_turun > '$naik'";
$res_terisi = mysqli_query($conn, $q_terisi);

while ($row = mysqli_fetch_assoc($res_terisi)) {
    $pecah = explode(',', $row['kursi']);
    foreach ($pecah as $k) {
        $kursi_terisi[] = trim($k);
    }
}

// Bagi kapasitas kereta ke 3 gerbong (misal kapasitas 60 -> 20 kursi per gerbong)
$daftar_gerbong = ['EKS-1', 'EKS-2', 'EKS-3'];
$kapasitas      = ceil($jadwal['kapasitas'] / 3);
$jumlah_baris   = ceil($kapasitas / 4);
$kolom          = ['A', 'B', 'C', 'D'];
$kode_booking   = 'FT-' . date('Ymd') . '-' . rand(100, 999);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Kursi - FastTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
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
    </div>

    <div class="container my-5 p-3">
        <h2>Pilih Gerbong & Bangku</h2>
        <div class="info d-flex gap-4 text-muted">
            <p class="fw-semibold text-dark"><?= $jadwal['nama_kereta'] ?></p>
            <p>(<?= $jadwal['stasiun_naik'] ?> &rarr; <?= $jadwal['stasiun_turun'] ?>)</p>
            <p><?= date('d-m-Y, H:i', strtotime($jadwal['tanggal_waktu_berangkat'])) ?> WIB</p>
        </div>

        <hr>

        <?php if (count($daftar_rute) > 0) : ?>
            <div class="mb-4">
                <h5>Pilih Stasiun Naik & Turun:</h5>
                <form method="get" action="" class="row g-2 align-items-end mt-1">
                    <input type="hidden" name="id_jadwal" value="<?= $id_jadwal ?>">
                    <input type="hidden" name="gerbong" value="<?= $gerbong ?>">

                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">Naik di Stasiun</label>
                        <select name="naik" class="form-select rounded-1">
                            <?php foreach ($daftar_rute as $r) : ?>
                                <option value="<?= $r['urutan'] ?>" <?= $naik == $r['urutan'] ? 'selected' : '' ?>>
                                    <?= $r['urutan'] ?>. <?= $r['kode_stasiun'] ?> - <?= $r['nama'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">Turun di Stasiun</label>
                        <select name="turun" class="form-select rounded-1">
                            <?php foreach ($daftar_rute as $r) : ?>
                                <option value="<?= $r['urutan'] ?>" <?= $turun == $r['urutan'] ? 'selected' : '' ?>>
                                    <?= $r['urutan'] ?>. <?= $r['kode_stasiun'] ?> - <?= $r['nama'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button type="submit" class="btn btn-dark rounded-1 w-100">Cek Kursi</button>
                    </div>
                </form>
            </div>
            <hr>
        <?php endif; ?>

        <!-- 1. Pilih Gerbong -->
        <div class="gerbong">
            <h5>1. Pilih Gerbong:</h5>
            <div class="gerbong-list d-flex gap-3 mt-2">
                <?php foreach ($daftar_gerbong as $g) : ?>
                    <a href="?id_jadwal=<?= $id_jadwal ?>&naik=<?= $naik ?>&turun=<?= $turun ?>&gerbong=<?= $g ?>"
                        class="btn <?= $gerbong == $g ? 'btn-dark' : 'btn-outline-dark' ?> rounded-1"
                        style="width: 150px;">
                        <?= $g ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 2. Pilih Kursi -->
        <div class="kursi mt-4">
            <h5>2. Pilih Kursi (<?= $gerbong ?>):</h5>
            <div class="border p-4 text-center mx-auto mt-3" style="max-width: 350px;">
                <div class="fw-bold border-bottom pb-2 mb-3">DEPAN</div>

                <div class="d-flex justify-content-between align-items-center mb-2 fw-bold">
                    <span style="width: 20px;"></span>
                    <span style="width: 40px;">A</span>
                    <span style="width: 40px;">B</span>
                    <span style="width: 40px; font-size: 11px;">Lorong</span>
                    <span style="width: 40px;">C</span>
                    <span style="width: 40px;">D</span>
                </div>

                <?php
                $nomor_urut = 0;
                for ($baris = 1; $baris <= $jumlah_baris; $baris++) :
                ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold" style="width: 20px;"><?= $baris ?></span>

                        <?php foreach ($kolom as $huruf) :
                            $nomor_urut++;
                            if ($nomor_urut > $kapasitas) {
                                echo '<span style="width: 40px;"></span>';
                                if ($huruf == 'B') echo '<span style="width: 40px;"></span>';
                                continue;
                            }

                            $kode_kursi = $baris . $huruf;
                            $full_kode  = $gerbong . '/' . $kode_kursi; // Contoh: EKS-1/2D
                            $is_terisi  = in_array($full_kode, $kursi_terisi);
                        ?>
                            <button type="button"
                                class="btn btn-sm rounded-1 <?= $is_terisi ? 'btn-secondary disabled' : 'btn-outline-dark btn-kursi' ?>"
                                style="width: 40px; height: 40px;"
                                data-kursi="<?= $kode_kursi ?>"
                                data-full="<?= $full_kode ?>"
                                <?= $is_terisi ? 'disabled' : '' ?>>
                                <?= $is_terisi ? 'X' : '' ?>
                            </button>

                            <?php if ($huruf == 'B') : ?>
                                <span class="text-muted" style="width: 40px;">—</span>
                            <?php endif; ?>

                        <?php endforeach; ?>
                    </div>
                <?php endfor; ?>

                <div class="fw-bold border-top pt-2 mt-3">BELAKANG</div>
            </div>
        </div>

        <div class="keterangan mt-4">
            <h5>Keterangan:</h5>
            <p class="mb-1">[] = Tersedia (Bisa diklik)</p>
            <p class="mb-1">[X] = Terisi / Sudah Dipesan</p>
            <p class="mb-1">[O] = Pilihan Anda</p>
        </div>

        <hr>

        <div class="ringkasan mb-4">
            <p class="mb-1">Jumlah Penumpang: <strong id="text-jumlah">0 Orang</strong></p>
            <p class="mb-1">Gerbong: <strong><?= $gerbong ?></strong></p>
            <p class="mb-1">Nomor Kursi: <strong id="text-kursi">-</strong></p>
        </div>

        <div class="button d-flex justify-content-between">
            <a href="dashboard.php" class="btn btn-outline-dark rounded-1">&lt; KEMBALI</a>
            <button type="button" id="btn-lanjut" class="btn btn-dark rounded-1">LANJUT PEMBAYARAN</button>
        </div>
    </div>

    <!-- Modal Konfirmasi Pembayaran -->
    <div class="modal fade" id="modalBayar" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="post" action="" class="modal-content rounded-1">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Rincian Pemesanan</h5>
                    <span class="badge bg-dark rounded-1 ms-auto"><?= $kode_booking ?></span>
                </div>

                <div class="modal-body">
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Kereta</span>
                            <span class="fw-semibold"><?= $jadwal['nama_kereta'] ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Rute</span>
                            <span class="fw-semibold"><?= $jadwal['stasiun_naik'] ?> &rarr; <?= $jadwal['stasiun_turun'] ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Gerbong/Kursi</span>
                            <span class="fw-semibold" id="modal-kursi">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Waktu</span>
                            <span class="fw-semibold"><?= date('d-m-Y, H:i', strtotime($jadwal['tanggal_waktu_berangkat'])) ?> WIB</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Harga Satuan</span>
                            <span class="fw-semibold">Rp <?= number_format($jadwal['harga'], 0, ',', '.') ?></span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Jumlah Pesanan:</span>
                        <input type="number" name="jumlah_tiket" id="modal-jumlah" class="form-control rounded-1 text-end" value="0" readonly style="width: 100px;">
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <span class="fw-bold">Total Bayar:</span>
                        <h4 class="fw-bold mb-0" id="modal-total">Rp 0</h4>
                    </div>

                    <!-- Hidden input buat dikirim ke PHP -->
                    <input type="hidden" name="kursi" id="input-kursi">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark rounded-1" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="bayar" class="btn btn-dark rounded-1">Konfirmasi Bayar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const hargaSatuan = <?= (int)$jadwal['harga'] ?>;
        let kursiDipilih = []; // Simpan kode pendek: ['2D', '2C']
        let kursiFullDipilih = []; // Simpan kode lengkap: ['EKS-1/2D', 'EKS-1/2C']

        // Klik kursi
        document.querySelectorAll('.btn-kursi').forEach(btn => {
            btn.addEventListener('click', function() {
                const kode = this.dataset.kursi;
                const full = this.dataset.full;

                if (kursiFullDipilih.includes(full)) {
                    // Batal pilih
                    kursiDipilih = kursiDipilih.filter(k => k !== kode);
                    kursiFullDipilih = kursiFullDipilih.filter(k => k !== full);
                    this.classList.remove('btn-dark');
                    this.classList.add('btn-outline-dark');
                    this.innerText = '';
                } else {
                    // Pilih kursi
                    kursiDipilih.push(kode);
                    kursiFullDipilih.push(full);
                    this.classList.remove('btn-outline-dark');
                    this.classList.add('btn-dark');
                    this.innerText = 'O';
                }

                // Update ringkasan di bawah
                document.getElementById('text-jumlah').innerText = kursiDipilih.length + ' Orang';
                document.getElementById('text-kursi').innerText = kursiDipilih.length > 0 ? kursiDipilih.join(', ') : '-';
            });
        });

        // Klik Lanjut Pembayaran -> Buka Modal
        document.getElementById('btn-lanjut').addEventListener('click', function() {
            if (kursiFullDipilih.length === 0) {
                alert('Pilih minimal 1 kursi dulu!');
                return;
            }

            const jumlah = kursiFullDipilih.length;
            const total = jumlah * hargaSatuan;

            document.getElementById('modal-kursi').innerText = kursiFullDipilih.join(', ');
            document.getElementById('modal-jumlah').value = jumlah;
            document.getElementById('modal-total').innerText = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('input-kursi').value = kursiFullDipilih.join(',');

            const modal = new bootstrap.Modal(document.getElementById('modalBayar'));
            modal.show();
        });
    </script>
</body>

</html>