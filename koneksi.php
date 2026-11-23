<?php

$hostname = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'fasttrack';

$conn = mysqli_connect($hostname, $username, $password, $dbname);
if(!$conn) {
    die('Koneksi gagal' . mysqli_connect_error());
}

function hitungHarga($harga_dasar, $tanggal_berangkat) {
    $hari = date('N', strtotime($tanggal_berangkat));
    if ($hari >= 5) {
        return $harga_dasar * 1.2;
    }
    return $harga_dasar;
}