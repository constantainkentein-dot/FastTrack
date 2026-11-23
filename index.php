<?php
session_start();

require_once __DIR__ . '/koneksi.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM `users` WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    $cek = mysqli_fetch_assoc($result);

    if ($cek && password_verify($password, $cek['password'])) {
        $_SESSION['id']   = $cek['id'];
        $_SESSION['nama'] = $cek['nama'];
        $_SESSION['role'] = $cek['role'];

        if ($cek['role'] == 'admin') {
            echo "<script>
                alert('Login Admin berhasil');
                window.location.href = 'admin/dashboard.php';
            </script>";
        } else {
            echo "<script>
                alert('Login berhasil');
                window.location.href = 'user/dashboard.php';
            </script>";
        }
        exit;
    } else {
        echo "<script>
            alert('Username atau password salah');
        </script>";
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

            <!-- Setengah Kiri: Foto Kereta -->
            <div class="col-md-6 d-md-block">
                <img src="https://images.unsplash.com/photo-1532105956626-9569c03602f6?auto=format&fit=crop&w=1200&q=80"
                    alt="Kereta FastTrack"
                    class="w-100 vh-100 object-fit-cover">
            </div>

            <!-- Setengah Kanan: Form Login -->
            <div class="col-md-6 d-flex justify-content-center align-items-center bg-white p-4">
                <div style="width: 100%; max-width: 400px;">
                    <form method="post" action="">
                        <h1 class="text-center fw-bold" style="margin-bottom: 30px;">Login Sistem Tiket</h1>
                        <div class="mb-3">
                            <label for="exampleInputUsername1" class="form-label">Username:</label>
                            <input type="text" class="form-control rounded-2" id="exampleInputUsername1" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="exampleInputPassword1" class="form-label">Password:</label>
                            <input type="password" class="form-control rounded-2" id="exampleInputPassword1" name="password" required>
                        </div>
                        <div class="text-center">
                            <button name="login" type="submit" class="btn btn-dark w-100 rounded-2 py-2 mt-3">Login</button>
                        </div>
                    </form>
                    <p class="text-center mt-4">Belum punya akun? <a href="register.php" class="fw-semibold text-decoration-none text-dark">Daftar</a></p>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>