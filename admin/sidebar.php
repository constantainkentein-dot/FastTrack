<?php $halaman = basename($_SERVER['PHP_SELF']); ?>

<div class="bg-dark text-white p-3 d-flex flex-column" style="width: 240px; min-height: 100vh;">
    <h4 class="fw-bold mb-4 px-2">FastTrack</h4>
    
    <ul class="nav nav-pills flex-column gap-1 mb-auto">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?= $halaman == 'dashboard.php' || $halaman == 'rute.php' ? 'active bg-secondary' : 'text-white' ?>">
                Kelola Jadwal
            </a>
        </li>
        <li class="nav-item">
            <a href="kereta.php" class="nav-link <?= $halaman == 'kereta.php' ? 'active bg-secondary' : 'text-white' ?>">
                Data Kereta
            </a>
        </li>
        <li class="nav-item">
            <a href="stasiun.php" class="nav-link <?= $halaman == 'stasiun.php' ? 'active bg-secondary' : 'text-white' ?>">
                Data Stasiun
            </a>
        </li>
        <li class="nav-item">
            <a href="pemesanan.php" class="nav-link <?= $halaman == 'pemesanan.php' ? 'active bg-secondary' : 'text-white' ?>">
                Data Pemesanan
            </a>
        </li>
    </ul>

    <hr class="border-secondary">
    <a href="../logout.php" class="btn btn-danger btn-sm w-100">Keluar</a>
</div>