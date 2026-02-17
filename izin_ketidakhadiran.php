<?php
require_once 'config.php';
requireAuth();

$token = getAuthToken();
$currentUser = getCurrentUser();
$userRole = $currentUser['role'] ?? '';
$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $data = [
            'pegawai_id' => $_POST['pegawai_id'] ?? '',
            'tanggal_izin' => $_POST['tanggal_izin'] ?? '',
            'jenis_izin' => $_POST['jenis_izin'] ?? '',
            'alasan' => $_POST['alasan'] ?? '',
            'status' => 'approved' // Auto approve by admin
        ];
        
        $response = apiRequest(API_IZIN_KETIDAKHADIRAN, 'POST', $data, $token);
        if ($response['success']) {
            $message = 'Izin ketidakhadiran berhasil ditambahkan dan disetujui';
            $messageType = 'success';
        } else {
            $message = $response['message'] ?? 'Gagal menambahkan izin ketidakhadiran';
            $messageType = 'danger';
        }
    } elseif ($action === 'update_status') {
        $id = $_POST['id'] ?? '';
        $status = $_POST['status'] ?? '';
        
        $response = apiRequest(API_IZIN_KETIDAKHADIRAN . '?id=' . $id, 'PUT', ['status' => $status], $token);
        if ($response['success']) {
            $message = 'Status izin ketidakhadiran berhasil diupdate';
            $messageType = 'success';
        } else {
            $message = $response['message'] ?? 'Gagal mengupdate status';
            $messageType = 'danger';
        }
    } elseif ($action === 'update') {
        $id = $_POST['id'] ?? '';
        $data = [
            'tanggal_izin' => $_POST['tanggal_izin'] ?? ''
        ];
        
        $response = apiRequest(API_IZIN_KETIDAKHADIRAN . '?id=' . $id, 'PUT', $data, $token);
        if ($response['success']) {
            $message = 'Tanggal izin ketidakhadiran berhasil diupdate';
            $messageType = 'success';
        } else {
            $message = $response['message'] ?? 'Gagal mengupdate tanggal';
            $messageType = 'danger';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $response = apiRequest(API_IZIN_KETIDAKHADIRAN . '?id=' . $id, 'DELETE', null, $token);
        if ($response['success']) {
            $message = 'Izin ketidakhadiran berhasil dihapus';
            $messageType = 'success';
        } else {
            $message = $response['message'] ?? 'Gagal menghapus izin ketidakhadiran';
            $messageType = 'danger';
        }
    }
}

// Get all izin ketidakhadiran
$response = apiRequest(API_IZIN_KETIDAKHADIRAN, 'GET', null, $token);
$izinKetidakhadiran = $response['data'] ?? [];

// Get data for form (Only for admin)
$pegawaiList = [];
if ($userRole === 'admin') {
    $respPegawai = apiRequest(API_PEGAWAI, 'GET', null, $token);
    if (($respPegawai['success'] ?? false) && isset($respPegawai['data'])) {
        // Filter users only
        $pegawaiList = array_filter($respPegawai['data'], function($p) {
            return ($p['role'] ?? '') === 'user';
        });
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Izin Ketidakhadiran - Admin</title>
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
                    <h1 class="h2"><i class="bi bi-calendar-x"></i> Manajemen Izin Ketidakhadiran</h1>
                    <?php if ($userRole === 'admin'): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-plus-lg"></i> Input Izin Ketidakhadiran
                    </button>
                    <?php endif; ?>
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
                                        <th>Pegawai</th>
                                        <th>NIP/NP</th>
                                        <th>Tanggal Izin</th>
                                        <th>Jenis Izin</th>
                                        <th>Alasan</th>
                                        <th>Status</th>
                                        <th>Approve/Rejected</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($izinKetidakhadiran)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">Tidak ada data izin ketidakhadiran</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($izinKetidakhadiran as $izin): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($izin['nama_pegawai'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($izin['nip_np'] ?? '-'); ?></td>
                                            <td><?php echo formatDate($izin['tanggal_izin'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($izin['jenis_izin'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars(substr($izin['alasan'] ?? '-', 0, 50)) . (strlen($izin['alasan'] ?? '') > 50 ? '...' : ''); ?></td>
                                            <td>
                                                <?php 
                                                    $status = $izin['status'] ?? 'pending';
                                                    if ($status === 'pending') echo '<span class="badge bg-secondary">Menunggu</span>';
                                                    elseif ($status === 'approved_1') echo '<span class="badge bg-warning">Disetujui Tahap 1</span>';
                                                    elseif ($status === 'approved') echo '<span class="badge bg-success">Disetujui</span>';
                                                    elseif ($status === 'rejected' || $status === 'rejected_1' || $status === 'rejected_2') echo '<span class="badge bg-danger">Ditolak</span>';
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    $status = $izin['status'] ?? 'pending';
                                                    $reqId = $izin['pegawai_id'] ?? 0;
                                                    $myId = $currentUser['id'] ?? 0;
                                                    
                                                    // Determine permissions based on Requester Role and My Role
                                                    // Note: izin-ketidakhadiran API also returns requester_role (aliased as p.role)
                                                    $reqRole = $izin['requester_role'] ?? '';
                                                    $canApproveStage1 = false;
                                                    $canApproveStage2 = false;

                                                    // 1. Dosen, Kaprodi -> Apv1: Kajur, Apv2: WDKU
                                                    if (in_array($reqRole, ['dosen', 'kaprodi'])) {
                                                        if ($userRole === 'kajur') $canApproveStage1 = true;
                                                        if ($userRole === 'wdku') $canApproveStage2 = true;
                                                    } 
                                                    // 2. Kajur, Dosen Tugas, Pegawai -> Apv1: WDKU, Apv2: Dekan
                                                    elseif (in_array($reqRole, ['kajur', 'dosen_tugas', 'pegawai'])) {
                                                        if ($userRole === 'wdku') $canApproveStage1 = true;
                                                        if ($userRole === 'dekan') $canApproveStage2 = true;
                                                    } 
                                                    // 3. WDKU -> Apv1: WDAK, Apv2: Dekan
                                                    elseif ($reqRole === 'wdku') {
                                                        if ($userRole === 'wdak') $canApproveStage1 = true;
                                                        if ($userRole === 'dekan') $canApproveStage2 = true;
                                                    } 
                                                    // 4. WDAK -> Apv1: Dekan, Apv2: Wakil Rektor 2
                                                    elseif ($reqRole === 'wdak') {
                                                        if ($userRole === 'dekan') $canApproveStage1 = true;
                                                        if ($userRole === 'wakil_rektor_2') $canApproveStage2 = true;
                                                    }

                                                    if ($userRole === 'admin') {
                                                        // Admin View Only
                                                    }
                                                    elseif ($reqId != $myId) {
                                                        // Stage 1 Approval
                                                        if ($status === 'pending' && $canApproveStage1) {
                                                            echo '<button class="btn btn-sm btn-success me-1" onclick="updateStatus('.$izin['id'].', \'approved_1\')" title="Approve"><i class="bi bi-check-circle"></i> Setujui Tahap 1</button>';
                                                            echo '<button class="btn btn-sm btn-danger" onclick="updateStatus('.$izin['id'].', \'rejected_1\')" title="Reject"><i class="bi bi-x-circle"></i> Tolak</button>';
                                                        }
                                                        // Stage 2 Approval
                                                        elseif ($status === 'approved_1' && $canApproveStage2) {
                                                            echo '<button class="btn btn-sm btn-success me-1" onclick="updateStatus('.$izin['id'].', \'approved\')" title="Approve Final"><i class="bi bi-check-double"></i> Setujui Final</button>';
                                                            echo '<button class="btn btn-sm btn-danger" onclick="updateStatus('.$izin['id'].', \'rejected_2\')" title="Reject"><i class="bi bi-x-circle"></i> Tolak</button>';
                                                        }
                                                        // Status Messages
                                                        elseif ($status === 'approved_1' && $canApproveStage1) {
                                                             echo '<span class="text-success"><i class="bi bi-check"></i> Disetujui Tahap 1</span>';
                                                        }
                                                        elseif ($status === 'rejected_1' && $canApproveStage1) {
                                                             echo '<span class="text-danger"><i class="bi bi-x"></i> Ditolak Tahap 1</span>';
                                                        }
                                                        elseif ($status === 'rejected_2' && $canApproveStage2) {
                                                             echo '<span class="text-danger"><i class="bi bi-x"></i> Ditolak Tahap 2</span>';
                                                        }
                                                        elseif ($status === 'pending' && $canApproveStage2) {
                                                             echo '<span class="text-muted">Menunggu Tahap 1</span>';
                                                        }
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <a href="cetak_ketidakhadiran.php?id=<?php echo $izin['id']; ?>" target="_blank" class="btn btn-sm btn-secondary" title="Cetak">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                                <button class="btn btn-sm btn-info" onclick="viewDetail(<?php echo htmlspecialchars(json_encode($izin)); ?>)">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <?php if ($userRole === 'admin'): ?>
                                                <button class="btn btn-sm btn-warning" onclick="editTanggal(<?php echo htmlspecialchars(json_encode($izin)); ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus izin ketidakhadiran ini?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $izin['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
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
    
    <!-- Modal Detail -->
    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Izin Ketidakhadiran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Izin (Admin) -->
    <?php if ($userRole === 'admin'): ?>
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Input Izin Ketidakhadiran (Langsung Disetujui)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="mb-3">
                            <label class="form-label">Pegawai</label>
                            <select class="form-select" name="pegawai_id" required>
                                <option value="">Pilih Pegawai</option>
                                <?php foreach($pegawaiList as $p): ?>
                                    <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nama_pegawai']); ?> (<?php echo htmlspecialchars($p['nip_np']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Izin</label>
                            <input type="date" class="form-control" name="tanggal_izin" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jenis Izin</label>
                            <select class="form-select" name="jenis_izin" required>
                                <option value="">Pilih Jenis Izin</option>
                                <option value="Sakit">Sakit</option>
                                <option value="Izin">Izin</option>
                                <option value="Tugas Luar">Tugas Luar</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alasan</label>
                            <textarea class="form-control" name="alasan" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan & Setujui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Modal Edit Tanggal -->
    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="editForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Tanggal Izin Ketidakhadiran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="editId">
                        
                        <div class="mb-3">
                            <label class="form-label">Tanggal Izin *</label>
                            <input type="date" class="form-control" name="tanggal_izin" id="editTanggalIzin" required>
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
    
    <!-- Form untuk update status -->
    <form id="statusForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="id" id="statusId">
        <input type="hidden" name="status" id="statusValue">
    </form>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewDetail(data) {
            let statusBadge = '';
            if (data.status === 'pending') statusBadge = '<span class="badge bg-secondary">Menunggu</span>';
            else if (data.status === 'approved_1') statusBadge = '<span class="badge bg-warning">Disetujui Tahap 1</span>';
            else if (data.status === 'approved') statusBadge = '<span class="badge bg-success">Disetujui Final</span>';
            else if (data.status === 'rejected' || data.status === 'rejected_1' || data.status === 'rejected_2') statusBadge = '<span class="badge bg-danger">Ditolak</span>';

            const content = `
                <table class="table table-bordered">
                    <tr><th width="30%">Pegawai</th><td>${data.nama_pegawai || '-'}</td></tr>
                    <tr><th>NIP/NP</th><td>${data.nip_np || '-'}</td></tr>
                    <tr><th>Tanggal Izin</th><td>${data.tanggal_izin || '-'}</td></tr>
                    <tr><th>Jenis Izin</th><td>${data.jenis_izin || '-'}</td></tr>
                    <tr><th>Alasan</th><td>${data.alasan || '-'}</td></tr>
                    <tr><th>Lampiran</th><td>${data.lampiran ? '<a href="' + data.lampiran + '" target="_blank">Lihat Lampiran</a>' : '-'}</td></tr>
                    <tr><th>Status</th><td>${statusBadge}</td></tr>
                    <tr><th>Tanggal Pengajuan</th><td>${data.tanggal_pengajuan || '-'}</td></tr>
                    <tr><th>Prodi</th><td>${data.nama_prodi || '-'}</td></tr>
                </table>
            `;
            document.getElementById('detailContent').innerHTML = content;
            const modal = new bootstrap.Modal(document.getElementById('modalDetail'));
            modal.show();
        }
        
        function editTanggal(data) {
            document.getElementById('editId').value = data.id;
            document.getElementById('editTanggalIzin').value = data.tanggal_izin || '';
            
            const modal = new bootstrap.Modal(document.getElementById('modalEdit'));
            modal.show();
        }
        
        function updateStatus(id, status) {
            let confirmMsg = '';
            if (status === 'approved_1') confirmMsg = 'Yakin ingin menyetujui tahap 1?';
            else if (status === 'approved') confirmMsg = 'Yakin ingin menyetujui final?';
            else confirmMsg = 'Yakin ingin menolak pengajuan ini?';

            if (confirm(confirmMsg)) {
                document.getElementById('statusId').value = id;
                document.getElementById('statusValue').value = status;
                document.getElementById('statusForm').submit();
            }
        }
    </script>
</body>
</html>
