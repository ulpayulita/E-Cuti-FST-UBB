<?php
require_once 'config.php';
requireAuth();

$token = getAuthToken();
$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $data = [
            'nama_cuti' => $_POST['nama_cuti'] ?? '',
            'jenis_cuti' => $_POST['jenis_cuti'] ?? ''
        ];
        
        $response = apiRequest(API_JENIS_CUTI, 'POST', $data, $token);
        if ($response['success']) {
            $message = 'Jenis cuti berhasil ditambahkan';
            $messageType = 'success';
        } else {
            $message = $response['message'] ?? 'Gagal menambahkan jenis cuti';
            $messageType = 'danger';
        }
    } elseif ($action === 'update') {
        $id = $_POST['id'] ?? '';
        $data = [
            'nama_cuti' => $_POST['nama_cuti'] ?? '',
            'jenis_cuti' => $_POST['jenis_cuti'] ?? ''
        ];
        
        $response = apiRequest(API_JENIS_CUTI . '?id=' . $id, 'PUT', $data, $token);
        if ($response['success']) {
            $message = 'Jenis cuti berhasil diupdate';
            $messageType = 'success';
        } else {
            $message = $response['message'] ?? 'Gagal mengupdate jenis cuti';
            $messageType = 'danger';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $response = apiRequest(API_JENIS_CUTI . '?id=' . $id, 'DELETE', null, $token);
        if ($response['success']) {
            $message = 'Jenis cuti berhasil dihapus';
            $messageType = 'success';
        } else {
            $message = $response['message'] ?? 'Gagal menghapus jenis cuti';
            $messageType = 'danger';
        }
    }
}

// Get all jenis cuti
$response = apiRequest(API_JENIS_CUTI, 'GET', null, $token);
$jenisCuti = $response['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jenis Cuti - Admin</title>
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
                    <h1 class="h2"><i class="bi bi-list-ul"></i> Manajemen Jenis Cuti</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalJenisCuti" onclick="resetForm()">
                        <i class="bi bi-plus-circle"></i> Tambah Jenis Cuti
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
                                        <th>ID</th>
                                        <th>Nama Cuti</th>
                                        <th>Jenis Cuti</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($jenisCuti)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Tidak ada data jenis cuti</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($jenisCuti as $jenis): ?>
                                        <tr>
                                            <td><?php echo $jenis['id']; ?></td>
                                            <td><?php echo htmlspecialchars($jenis['nama_cuti'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($jenis['jenis_cuti'] ?? '-'); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" onclick="editJenisCuti(<?php echo htmlspecialchars(json_encode($jenis)); ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus jenis cuti ini?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $jenis['id']; ?>">
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
    
    <!-- Modal Jenis Cuti -->
    <div class="modal fade" id="modalJenisCuti" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Tambah Jenis Cuti</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id" id="formId">
                        
                        <div class="mb-3">
                            <label class="form-label">Nama Cuti *</label>
                            <input type="text" class="form-control" name="nama_cuti" id="nama_cuti" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Jenis Cuti *</label>
                            <input type="text" class="form-control" name="jenis_cuti" id="jenis_cuti" required>
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
            document.getElementById('modalTitle').textContent = 'Tambah Jenis Cuti';
            document.querySelector('form').reset();
        }
        
        function editJenisCuti(data) {
            document.getElementById('formAction').value = 'update';
            document.getElementById('formId').value = data.id;
            document.getElementById('modalTitle').textContent = 'Edit Jenis Cuti';
            document.getElementById('nama_cuti').value = data.nama_cuti || '';
            document.getElementById('jenis_cuti').value = data.jenis_cuti || '';
            
            const modal = new bootstrap.Modal(document.getElementById('modalJenisCuti'));
            modal.show();
        }
    </script>
</body>
</html>
