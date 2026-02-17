<?php
require_once 'config.php';
requireAuth();

$token = getAuthToken();
$message = '';
$messageType = '';

// Get current profile
$response = apiRequest(API_PROFILE, 'GET', null, $token);
$profile = $response['data'] ?? [];

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nama_pegawai' => $_POST['nama_pegawai'] ?? '',
        'username' => $_POST['username'] ?? '',
        'alamat' => $_POST['alamat'] ?? '',
        'tempat_lahir' => $_POST['tempat_lahir'] ?? '',
        'tanggal_lahir' => $_POST['tanggal_lahir'] ?? '',
        'jenis_kelamin' => $_POST['jenis_kelamin'] ?? '',
        'no_hp' => $_POST['no_hp'] ?? ''
    ];
    
    if (!empty($_POST['password_pegawai'])) {
        $data['password_pegawai'] = $_POST['password_pegawai'];
    }
    
    $response = apiRequest(API_PROFILE, 'PUT', $data, $token);
    if ($response['success']) {
        $message = 'Profile berhasil diupdate';
        $messageType = 'success';
        // Update session
        $profileResponse = apiRequest(API_PROFILE, 'GET', null, $token);
        if ($profileResponse['success']) {
            $_SESSION['admin_user'] = $profileResponse['data'];
        }
    } else {
        $message = $response['message'] ?? 'Gagal mengupdate profile';
        $messageType = 'danger';
    }
    
    // Reload profile
    $response = apiRequest(API_PROFILE, 'GET', null, $token);
    $profile = $response['data'] ?? [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-person-circle"></i> Profile</h1>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Informasi Profile</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">NIP/NP</label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($profile['nip_np'] ?? ''); ?>" disabled>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nama Pegawai *</label>
                                            <input type="text" class="form-control" name="nama_pegawai" value="<?php echo htmlspecialchars($profile['nama_pegawai'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Username</label>
                                            <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($profile['username'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Password Baru</label>
                                            <input type="password" class="form-control" name="password_pegawai" placeholder="Kosongkan jika tidak ingin mengubah">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">No. HP</label>
                                            <input type="text" class="form-control" name="no_hp" value="<?php echo htmlspecialchars($profile['no_hp'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Tempat Lahir</label>
                                            <input type="text" class="form-control" name="tempat_lahir" value="<?php echo htmlspecialchars($profile['tempat_lahir'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Tanggal Lahir</label>
                                            <input type="date" class="form-control" name="tanggal_lahir" value="<?php echo $profile['tanggal_lahir'] ?? ''; ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Jenis Kelamin</label>
                                        <select class="form-select" name="jenis_kelamin">
                                            <option value="">Pilih</option>
                                            <option value="Laki-laki" <?php echo ($profile['jenis_kelamin'] ?? '') === 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                                            <option value="Perempuan" <?php echo ($profile['jenis_kelamin'] ?? '') === 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Alamat</label>
                                        <textarea class="form-control" name="alamat" rows="3"><?php echo htmlspecialchars($profile['alamat'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <input type="text" class="form-control" value="<?php echo ucfirst($profile['role'] ?? 'user'); ?>" disabled>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Simpan Perubahan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="bi bi-person-circle" style="font-size: 80px; color: #0d6efd;"></i>
                                <h5 class="mt-3"><?php echo htmlspecialchars($profile['nama_pegawai'] ?? 'Admin'); ?></h5>
                                <p class="text-muted"><?php echo ucfirst($profile['role'] ?? 'user'); ?></p>
                                <hr>
                                <p class="mb-1"><strong>NIP/NP:</strong></p>
                                <p class="text-muted"><?php echo htmlspecialchars($profile['nip_np'] ?? '-'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
