<?php
require_once 'config.php';
requireAuth();

// Only admin
$currentUser = getCurrentUser();
if (($currentUser['role'] ?? '') !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

$token = getAuthToken();
$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $data = [
            'nama_jurusan' => $_POST['nama_jurusan'] ?? ''
        ];
        
        $response = apiRequest(API_JURUSAN, 'POST', $data, $token);
        if ($response['success']) {
            $message = 'Jurusan berhasil ditambahkan';
            $messageType = 'success';
        } else {
            $message = $response['message'] ?? 'Gagal menambahkan jurusan';
            $messageType = 'danger';
        }
    } elseif ($action === 'update') {
        $id = $_POST['id'] ?? '';
        $data = [
            'nama_jurusan' => $_POST['nama_jurusan'] ?? ''
        ];
        
        $response = apiRequest(API_JURUSAN . '?id=' . $id, 'PUT', $data, $token);
        if ($response['success']) {
            $message = 'Jurusan berhasil diupdate';
            $messageType = 'success';
        } else {
            $message = $response['message'] ?? 'Gagal mengupdate jurusan';
            $messageType = 'danger';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $response = apiRequest(API_JURUSAN . '?id=' . $id, 'DELETE', null, $token);
        if ($response['success']) {
            $message = 'Jurusan berhasil dihapus';
            $messageType = 'success';
        } else {
            $message = $response['message'] ?? 'Gagal menghapus jurusan';
            $messageType = 'danger';
        }
    }
}

// Get all jurusan
$response = apiRequest(API_JURUSAN, 'GET', null, $token);
$jurusan = $response['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Jurusan - Admin</title>
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
                    <h1 class="h2"><i class="bi bi-diagram-3"></i> Manajemen Jurusan</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalJurusan" onclick="resetForm()">
                        <i class="bi bi-plus-circle"></i> Tambah Jurusan
                    </button>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Jurusan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($jurusan)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Tidak ada data jurusan</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($jurusan as $index => $j): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($j['nama_jurusan'] ?? '-'); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" onclick="editJurusan(<?php echo htmlspecialchars(json_encode($j)); ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus jurusan ini? Hati-hati, prodi terkait juga akan terhapus.');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $j['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Modal Jurusan -->
    <div class="modal fade" id="modalJurusan" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Tambah Jurusan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id" id="formId">
                        
                        <div class="mb-3">
                            <label class="form-label">Nama Jurusan *</label>
                            <input type="text" class="form-control" name="nama_jurusan" id="nama_jurusan" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetForm() {
            document.getElementById('formAction').value = 'create';
            document.getElementById('formId').value = '';
            document.getElementById('modalTitle').textContent = 'Tambah Jurusan';
            document.querySelector('form').reset();
        }
        
        function editJurusan(data) {
            document.getElementById('formAction').value = 'update';
            document.getElementById('formId').value = data.id;
            document.getElementById('modalTitle').textContent = 'Edit Jurusan';
            document.getElementById('nama_jurusan').value = data.nama_jurusan || '';
            
            const modal = new bootstrap.Modal(document.getElementById('modalJurusan'));
            modal.show();
        }
    </script>
</body>
</html>
