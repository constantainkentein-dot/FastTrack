<?php

require_once __DIR__ . '/koneksi.php';

if (isset($_POST['register']) == true) {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $hashPassword = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO `users`(`id`, `nama`, `username`, `password`, `role`, `created_at`, `updated_at`) VALUES (NULL, '$name', '$username', '$hashPassword', 'penumpang', NOW(), NOW())";
    $result = mysqli_query($conn, $query);

    if($result) {
        echo "<script>
            alert('Registrasi berhasil');
            window.location.href = 'index.php';
        </script>";
    } else {
        echo "<script>alert('Registrasi gagal')</script>";
    }
}

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FastTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid p-0">
        <div class="row g-0 min-vh-100">
            <!-- Setengah Kiri: Form Login -->
            <div class="col-md-6 d-flex justify-content-center align-items-center bg-white p-4">
                <div style="width: 100%; max-width: 400px;">
                    <form method="post" action="">
                        <h1 class="text-center fw-bold" style="margin-bottom: 30px; font-size: 36px;">Registrasi Sistem Tiket</h1>
                        <div class="mb-3">
                            <label for="exampleInputName1" class="form-label">Name:</label>
                            <input type="text" class="form-control rounded-2" id="exampleInputName1" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="exampleInputUsername1" class="form-label">Username:</label>
                            <input type="text" class="form-control rounded-2" id="exampleInputUsername1" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="exampleInputPassword1" class="form-label">Password:</label>
                            <input type="password" class="form-control rounded-2" id="exampleInputPassword1" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="exampleInputConPassword1" class="form-label">Konfirmasi Password:</label>
                            <input type="password" class="form-control rounded-2" id="exampleInputConPassword1" name="con_password" required>
                        </div>
                        <div class="text-center">
                            <button name="register" type="submit" class="btn btn-dark w-100 rounded-2 py-2 mt-3">Registrasi</button>
                        </div>
                    </form>
                    <p class="text-center mt-4">Sudah punya akun? <a href="index.php" class="fw-semibold text-decoration-none text-dark">Login</a></p>
                </div>
            </div>

            <!-- Setengah Kanan: Foto Kereta -->
            <div class="col-md-6 d-md-block">
                <img src="https://images.unsplash.com/photo-1532105956626-9569c03602f6?auto=format&fit=crop&w=1200&q=80"
                    alt="Kereta FastTrack"
                    class="w-100 vh-100 object-fit-cover">
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>