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
        $pegawai_id = $_POST['pegawai_id'] ?? '';
        $jenis_cuti_id = $_POST['jenis_cuti_id'] ?? '';
        $tanggal_awal = $_POST['tanggal_awal'] ?? '';
        $tanggal_akhir = $_POST['tanggal_akhir'] ?? '';
        $jumlah_cuti = $_POST['jumlah_cuti'] ?? '';
        $keterangan = $_POST['keterangan'] ?? '';
        $jabatan = $_POST['jabatan'] ?? '';
        
        $data = [
            'pegawai_id' => $pegawai_id,
            'jenis_cuti_id' => $jenis_cuti_id,
            'tanggal_awal' => $tanggal_awal,
            'tanggal_akhir' => $tanggal_akhir,
            'jumlah_cuti' => $jumlah_cuti,
            'keterangan' => $keterangan,
            'jabatan' => $jabatan,
            'status' => 'approved' // Auto approve request
        ];

        // Pass manual approvers if set
        if (!empty($_POST['approved_by_1'])) $data['approved_by_1'] = $_POST['approved_by_1'];
        if (!empty($_POST['approved_by_2'])) $data['approved_by_2'] = $_POST['approved_by_2'];

        $response = apiRequest(API_IZIN_CUTI, 'POST', $data, $token);
        
        if ($response['success']) {
            $message = 'Izin cuti berhasil ditambahkan dan disetujui';
            $messageType = 'success';
        } else {
            $message = $response['message'] ?? 'Gagal menambahkan izin cuti';
            $messageType = 'danger';
        }
    } elseif ($action === 'update_status') {
        $id = $_POST['id'] ?? '';
        $status = $_POST['status'] ?? '';
        
        $data = ['status' => $status];
        
        $response = apiRequest(API_IZIN_CUTI . '?id=' . $id, 'PUT', $data, $token);
        
        if ($response['success']) {
            $message = 'Status izin cuti berhasil diupdate';
            $messageType = 'success';
        } else {
            $message = $response['message'] ?? 'Gagal mengupdate status';
            $messageType = 'danger';
        }
    } elseif ($action === 'update') {
        $id = $_POST['id'] ?? '';
        $data = [
            'tanggal_awal' => $_POST['tanggal_awal'] ?? '',
            'tanggal_akhir' => $_POST['tanggal_akhir'] ?? '',
            'jumlah_cuti' => $_POST['jumlah_cuti'] ?? '',
            'jabatan' => $_POST['jabatan'] ?? ''
        ];

        // Pass manual approvers if set
        if (!empty($_POST['approved_by_1'])) $data['approved_by_1'] = $_POST['approved_by_1'];
        if (!empty($_POST['approved_by_2'])) $data['approved_by_2'] = $_POST['approved_by_2'];

        $response = apiRequest(API_IZIN_CUTI . '?id=' . $id, 'PUT', $data, $token);
        
        if ($response['success']) {
            $message = 'Data izin cuti berhasil diupdate';
            $messageType = 'success';
        } else {
            $message = $response['message'] ?? 'Gagal mengupdate data';
            $messageType = 'danger';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $response = apiRequest(API_IZIN_CUTI . '?id=' . $id, 'DELETE', null, $token);
        
        if ($response['success']) {
            $message = 'Izin cuti berhasil dihapus';
            $messageType = 'success';
        } else {
            $message = $response['message'] ?? 'Gagal menghapus izin cuti';
            $messageType = 'danger';
        }
    }
}

// Get all izin cuti
$response = apiRequest(API_IZIN_CUTI, 'GET', null, $token);
$izinCuti = $response['data'] ?? [];

// Get data for form (Only for admin)
$pegawaiList = [];
$jenisCutiList = [];
$approver1List = [];
$approver2List = [];

if ($userRole === 'admin') {
    // Initialize error variable
    $pegawaiError = '';

    $respPegawai = apiRequest(API_PEGAWAI, 'GET', null, $token);
    if (($respPegawai['success'] ?? false) && isset($respPegawai['data'])) {
        // Filter users for pegawai dropdown (Exclude admin)
        $pegawaiList = array_filter($respPegawai['data'], function ($p) {
            $role = $p['role'] ?? '';
            return $role !== 'admin' && $role !== 'verifikator'; 
        });
        
        // Get Approver 1 list (Kajur as per label, but let's include other stage 1 approvers)
        $approver1List = array_filter($respPegawai['data'], function ($p) {
            $role = $p['role'] ?? '';
            return in_array($role, ['kajur', 'wdku', 'wdak', 'dekan']); 
        });
        
        // Get Approver 2 list (WDKU/Dekan as per label)
        $approver2List = array_filter($respPegawai['data'], function ($p) {
            $role = $p['role'] ?? '';
            return in_array($role, ['wdku', 'dekan', 'wakil_rektor_2']);
        });
    } else {
        $pegawaiError = $respPegawai['message'] ?? 'Gagal mengambil data pegawai';
    }

    $respJenis = apiRequest(API_JENIS_CUTI, 'GET', null, $token); // Fixed variable name from respCuti to respJenis
    $jenisCutiList = $respJenis['data'] ?? [];
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Izin Cuti - Admin</title>
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
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-calendar-check"></i> Manajemen Izin Cuti</h1>
                    <?php if ($userRole === 'admin'): ?>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                            <i class="bi bi-plus-lg"></i> Input Izin Cuti
                        </button>
                    <?php endif; ?>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($userRole === 'admin' && !empty($pegawaiError)): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>Warning API:</strong> <?php echo htmlspecialchars($pegawaiError); ?>
                        <br><small>Saran: Coba Logout dan Login kembali jika errornya "Unauthorized".</small>
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
                                        <th>Jenis Cuti</th>
                                        <th>Tanggal Awal</th>
                                        <th>Tanggal Akhir</th>
                                        <th>Jumlah Hari</th>
                                        <th>Status</th>
                                        <th>Approve/Rejected</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($izinCuti)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">Tidak ada data izin cuti</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($izinCuti as $izin): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($izin['nama_pegawai'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($izin['nip_np'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($izin['nama_cuti'] ?? '-'); ?></td>
                                                <td><?php echo formatDate($izin['tanggal_awal'] ?? ''); ?></td>
                                                <td><?php echo formatDate($izin['tanggal_akhir'] ?? ''); ?></td>
                                                <td><?php echo $izin['jumlah_cuti'] ?? '0'; ?> hari</td>
                                                <td>
                                                    <?php
                                                    $status = $izin['status'] ?? 'pending';
                                                    if ($status === 'pending')
                                                        echo '<span class="badge bg-secondary">Menunggu</span>';
                                                    elseif ($status === 'approved_1')
                                                        echo '<span class="badge bg-warning">Disetujui Tahap 1</span>';
                                                    elseif ($status === 'approved')
                                                        echo '<span class="badge bg-success">Disetujui</span>';
                                                    elseif ($status === 'rejected' || $status === 'rejected_1' || $status === 'rejected_2')
                                                        echo '<span class="badge bg-danger">Ditolak</span>';
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status = $izin['status'] ?? 'pending';
                                                    $reqId = $izin['pegawai_id'] ?? 0;
                                                    $myId = $currentUser['id'] ?? 0;
                                                    
                                                    // Determine permissions based on Requester Role and My Role
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
                                                    
                                                    // Display Logic
                                                    if ($userRole === 'admin') {
                                                        // Admin View Only
                                                    }
                                                    elseif ($reqId != $myId) {
                                                        // Stage 1 Approval
                                                        if ($status === 'pending' && $canApproveStage1) {
                                                            echo '<button class="btn btn-sm btn-success me-1" onclick="updateStatus(' . $izin['id'] . ', \'approved_1\')" title="Approve"><i class="bi bi-check-circle"></i> Setujui Tahap 1</button>';
                                                            echo '<button class="btn btn-sm btn-danger" onclick="updateStatus(' . $izin['id'] . ', \'rejected_1\')" title="Reject"><i class="bi bi-x-circle"></i> Tolak</button>';
                                                        }
                                                        // Stage 2 Approval
                                                        elseif ($status === 'approved_1' && $canApproveStage2) {
                                                            echo '<button class="btn btn-sm btn-success me-1" onclick="updateStatus(' . $izin['id'] . ', \'approved\')" title="Approve Final"><i class="bi bi-check-double"></i> Setujui Final</button>';
                                                            echo '<button class="btn btn-sm btn-danger" onclick="updateStatus(' . $izin['id'] . ', \'rejected_2\')" title="Reject"><i class="bi bi-x-circle"></i> Tolak</button>';
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
                                                    <a href="cetak_cuti.php?id=<?php echo $izin['id']; ?>" target="_blank"
                                                        class="btn btn-sm btn-secondary" title="Cetak">
                                                        <i class="bi bi-printer"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-info"
                                                        onclick="viewDetail(<?php echo htmlspecialchars(json_encode($izin)); ?>)">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <?php if ($userRole === 'admin'): ?>
                                                        <button class="btn btn-sm btn-warning"
                                                            onclick="editTanggal(<?php echo htmlspecialchars(json_encode($izin)); ?>)">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <form method="POST" style="display: inline;"
                                                            onsubmit="return confirm('Yakin ingin menghapus izin cuti ini?');">
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
                    <h5 class="modal-title">Detail Izin Cuti</h5>
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
                            <h5 class="modal-title">Input Izin Cuti (Langsung Disetujui)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="action" value="create">

                            <div class="mb-3">
                                <label class="form-label">Pegawai</label>
                                <select class="form-select" name="pegawai_id" required>
                                    <option value="">Pilih Pegawai</option>
                                    <?php if (empty($pegawaiList)): ?>
                                        <option value="" disabled>Error: <?php echo htmlspecialchars($pegawaiError ?? 'Koneksi Gagal'); ?></option>
                                    <?php endif; ?>
                                    <?php foreach ($pegawaiList as $p): ?>
                                        <option value="<?php echo $p['id']; ?>">
                                            <?php echo htmlspecialchars($p['nama_pegawai']); ?>
                                            (<?php echo htmlspecialchars($p['nip_np']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Jabatan</label>
                                <input type="text" class="form-control" name="jabatan"
                                    placeholder="Contoh: Dosen Tetap, Staf Akademik">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Jenis Cuti</label>
                                <select class="form-select" name="jenis_cuti_id" required>
                                    <option value="">Pilih Jenis Cuti</option>
                                    <?php foreach ($jenisCutiList as $j): ?>
                                        <option value="<?php echo $j['id']; ?>"><?php echo htmlspecialchars($j['nama_cuti']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tanggal Mulai Cuti</label>
                                <input type="date" class="form-control" name="tanggal_awal" id="createTanggalAwal" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tanggal Akhir Cuti</label>
                                <input type="date" class="form-control" name="tanggal_akhir" id="createTanggalAkhir"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Jumlah Hari</label>
                                <input type="number" class="form-control" name="jumlah_cuti" id="createJumlahCuti" min="1"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alasan</label>
                                <textarea class="form-control" name="keterangan" rows="3"></textarea>
                            </div>

                            <hr>
                            <p class="text-muted small">Pilih siapa yang menyetujui cuti ini:</p>

                            <div class="mb-3">
                                <label class="form-label">Approved 1 (Kajur)</label>
                                <select class="form-select" name="approved_by_1">
                                    <option value="">Pilih Kajur</option>
                                    <?php foreach ($approver1List as $a): ?>
                                        <option value="<?php echo $a['id']; ?>">
                                            <?php echo htmlspecialchars($a['nama_pegawai']); ?> -
                                            <?php echo htmlspecialchars($a['nama_prodi'] ?? $a['nama_jurusan'] ?? ''); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Approved 2 (WDKU/Dekan)</label>
                                <select class="form-select" name="approved_by_2">
                                    <option value="">Pilih WDKU/Dekan</option>
                                    <?php foreach ($approver2List as $a): ?>
                                        <option value="<?php echo $a['id']; ?>">
                                            <?php echo htmlspecialchars($a['nama_pegawai']); ?> -
                                            <?php echo htmlspecialchars($a['nama_jurusan'] ?? ''); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
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
                        <h5 class="modal-title">Edit Tanggal Izin Cuti</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="editId">

                        <div class="mb-3">
                            <label class="form-label">Tanggal Awal *</label>
                            <input type="date" class="form-control" name="tanggal_awal" id="editTanggalAwal" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Akhir *</label>
                            <input type="date" class="form-control" name="tanggal_akhir" id="editTanggalAkhir" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jumlah Hari *</label>
                            <input type="number" class="form-control" name="jumlah_cuti" id="editJumlahCuti" min="1"
                                required>
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
                    <tr><th>Jenis Cuti</th><td>${data.nama_cuti || '-'}</td></tr>
                    <tr><th>Tanggal Awal</th><td>${data.tanggal_awal || '-'}</td></tr>
                    <tr><th>Tanggal Akhir</th><td>${data.tanggal_akhir || '-'}</td></tr>
                    <tr><th>Jumlah Hari</th><td>${data.jumlah_cuti || '0'} hari</td></tr>
                    <tr><th>Keterangan</th><td>${data.keterangan || '-'}</td></tr>
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
            document.getElementById('editTanggalAwal').value = data.tanggal_awal || '';
            document.getElementById('editTanggalAkhir').value = data.tanggal_akhir || '';
            document.getElementById('editJumlahCuti').value = data.jumlah_cuti || '';

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

        // Auto calculate jumlah hari for edit
        document.getElementById('editTanggalAwal')?.addEventListener('change', () => calculateDays('edit'));
        document.getElementById('editTanggalAkhir')?.addEventListener('change', () => calculateDays('edit'));

        // Auto calculate jumlah hari for create
        document.getElementById('createTanggalAwal')?.addEventListener('change', () => calculateDays('create'));
        document.getElementById('createTanggalAkhir')?.addEventListener('change', () => calculateDays('create'));

        function calculateDays(prefix) {
            const tanggalAwal = document.getElementById(prefix + 'TanggalAwal').value;
            const tanggalAkhir = document.getElementById(prefix + 'TanggalAkhir').value;

            if (tanggalAwal && tanggalAkhir) {
                const start = new Date(tanggalAwal);
                const end = new Date(tanggalAkhir);
                const diffTime = end - start;
                // If end date is before start date, reset or handle error? For now simple math.
                if (diffTime >= 0) {
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    document.getElementById(prefix + 'JumlahCuti').value = diffDays;
                }
            }
        }
    </script>
</body>

</html>