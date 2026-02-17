<?php
require_once 'config.php';

// Redirect if already logged in
if (isAuthenticated()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $response = apiRequest(API_LOGIN, 'POST', [
            'username' => $username,
            'password' => $password
        ]);
        
        if ($response['success']) {
            $role = $response['data']['role'];
            $allowed_roles = [
                'admin', 
                'dosen', 'kajur', 'wdku', 'kaprodi', 'dosen_tugas', 
                'pegawai', 'dekan', 'wdak', 'wakil_rektor_2'
            ];
            
            if (in_array($role, $allowed_roles)) {
                $_SESSION['admin_token'] = $response['data']['id'];
                $_SESSION['admin_user'] = $response['data'];
                header('Location: dashboard.php');
                exit();
            } else {
                 $error = 'Anda tidak memiliki akses ke halaman admin';
            }
        } else {
            $error = $response['message'] ?? 'Username atau password salah';
        }
    } else {
        $error = 'Username dan password harus diisi';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - E-cuti FST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body.login-page {
            background-color: #0d6efd;
            background-image: linear-gradient(160deg, #0d6efd 0%, #0a58ca 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            border: none;
        }

        .login-logo {
            width: 120px;
            height: auto;
            margin-bottom: 15px;
        }

        .login-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 30px;
            font-size: 1.5rem;
        }

        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            background-color: #f8f9fa;
        }

        .form-control:focus {
            background-color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        .input-group-text {
            border-radius: 8px;
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            color: #6c757d;
        }
        
        .btn-primary {
            border-radius: 8px;
            padding: 10px;
            font-weight: 600;
            background-color: #0d6efd;
            border: none;
        }

        .btn-outline-success {
            border-radius: 8px;
            padding: 10px;
            font-weight: 500;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 5px;
            color: #495057;
        }
    </style>
</head>
<body class="login-page">
    <div class="container d-flex justify-content-center">
        <div class="login-card">
            <div class="text-center mb-4">
                <img src="assets/logo_ubb.jpg" alt="Logo UBB" class="login-logo">
                <h2 class="login-title">E-Cuti FST UBB</h2>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger py-2 mb-3" role="alert" style="font-size: 0.9rem;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Username / NIP</label>
                    <div class="input-group">
                        <span class="input-group-text border-end-0"><i class="bi bi-person-fill"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" id="username" name="username" required autofocus placeholder="">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text border-end-0"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" required placeholder="">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Masuk
                </button>
            </form>

            <div class="mt-4 pt-3 border-top text-center">
                <p class="text-muted small mb-2">Ingin menggunakan aplikasi mobile?</p>
                <a href="../app-debug.apk" class="btn btn-outline-success w-100" download>
                    <i class="bi bi-android2 me-2"></i> Download App Android
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
