<?php
require_once 'config.php';
requireAuth();

// Cek jika role adalah user biasa, redirect ke dashboard
// (Admin roles: admin, dan roles managerial lainnya mungkin punya akses read-only? 
// Namun biasanya halaman ini untuk Admin utama. Mari asumsikan hanya 'admin' yang boleh edit pegawai full, 
// atau mungkin user managerial butuh akses. Untuk aman, kita batasi ke 'admin' saja 
// atau biarkan semua yang lolos requireAuth(). 
// Kode lama memblokir 'wkdu', tapi sekarang role itu tidak ada.
// Defaultnya function isAuthenticated() di config.php sudah memfilter role yang valid login admin panel.)

$currentUser = getCurrentUser();
// Optional: Restrict this page to Admin only?
// if (($currentUser['role'] ?? '') !== 'admin') { ... } 
// user requested "sesuaikan dengan role yang ada sekarang".

$token = getAuthToken();
$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $jurusan_id = !empty($_POST['jurusan_id']) ? $_POST['jurusan_id'] : null;
        $prodi_id = !empty($_POST['prodi_id']) ? $_POST['prodi_id'] : null;
        
        $data = [
            'nip_np' => $_POST['nip_np'] ?? '',
            'nama_pegawai' => $_POST['nama_pegawai'] ?? '',
            'password_pegawai' => $_POST['password_pegawai'] ?? '',
            'role' => $_POST['role'] ?? 'pegawai',
            'username' => $_POST['username'] ?? '',
            'prodi_id' => $prodi_id,
            'jurusan_id' => $jurusan_id,
            'alamat' => $_POST['alamat'] ?? '',
            'tempat_lahir' => $_POST['tempat_lahir'] ?? '',
            'tanggal_lahir' => $_POST['tanggal_lahir'] ?? '',
            'jenis_kelamin' => $_POST['jenis_kelamin'] ?? '',
            'no_hp' => $_POST['no_hp'] ?? ''
        ];
        
        $response = apiRequest(API_PEGAWAI, 'POST', $data, $token);
        if ($response['success']) {
            $message = 'Pegawai berhasil ditambahkan';
            $messageType = 'success';
        } else {
            $message = $response['message'] ?? 'Gagal menambahkan pegawai';
            $messageType = 'danger';
        }
    } elseif ($action === 'update') {
        $id = $_POST['id'] ?? '';
        $jurusan_id = !empty($_POST['jurusan_id']) ? $_POST['jurusan_id'] : null;
        $prodi_id = !empty($_POST['prodi_id']) ? $_POST['prodi_id'] : null;
        
        $data = [
            'nama_pegawai' => $_POST['nama_pegawai'] ?? '',
            'role' => $_POST['role'] ?? '',
            'username' => $_POST['username'] ?? '',
            'prodi_id' => $prodi_id,
            'jurusan_id' => $jurusan_id,
            'alamat' => $_POST['alamat'] ?? '',
            'tempat_lahir' => $_POST['tempat_lahir'] ?? '',
            'tanggal_lahir' => $_POST['tanggal_lahir'] ?? '',
            'jenis_kelamin' => $_POST['jenis_kelamin'] ?? '',
            'no_hp' => $_POST['no_hp'] ?? ''
        ];
        
        if (!empty($_POST['password_pegawai'])) {
            $data['password_pegawai'] = $_POST['password_pegawai'];
        }
        
        $response = apiRequest(API_PEGAWAI . '?id=' . $id, 'PUT', $data, $token);
        if ($response['success']) {
            $message = 'Pegawai berhasil diupdate';
            $messageType = 'success';
        } else {
            $message = $response['message'] ?? 'Gagal mengupdate pegawai';
            $messageType = 'danger';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $response = apiRequest(API_PEGAWAI . '?id=' . $id, 'DELETE', null, $token);
        if ($response['success']) {
            $message = 'Pegawai berhasil dihapus';
            $messageType = 'success';
        } else {
            $message = $response['message'] ?? 'Gagal menghapus pegawai';
            $messageType = 'danger';
        }
    }
}

// Get all pegawai
$response = apiRequest(API_PEGAWAI, 'GET', null, $token);
$pegawai = $response['data'] ?? [];

// Get all prodi
$responseProdi = apiRequest(API_PRODI, 'GET', null, $token);
$prodiList = $responseProdi['data'] ?? [];

// Get all jurusan
$responseJurusan = apiRequest(API_JURUSAN, 'GET', null, $token);
$jurusanList = $responseJurusan['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pegawai - Admin</title>
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
                    <h1 class="h2"><i class="bi bi-people"></i> Manajemen Pegawai</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPegawai" onclick="resetForm()">
                        <i class="bi bi-plus-circle"></i> Tambah Pegawai
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
                                        <th>NIP/NP</th>
                                        <th>Nama</th>
                                        <th>Username</th>
                                        <th>Unit Kerja</th>
                                        <th>Role</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($pegawai)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Tidak ada data pegawai</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($pegawai as $p): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($p['nip_np'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($p['nama_pegawai'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($p['username'] ?? '-'); ?></td>
                                            <td>
                                                <?php 
                                                    if (!empty($p['nama_prodi'])) {
                                                        echo htmlspecialchars($p['nama_prodi']);
                                                    } elseif (!empty($p['nama_jurusan'])) {
                                                        echo htmlspecialchars($p['nama_jurusan']);
                                                    } else {
                                                        echo htmlspecialchars($p['prodi'] ?? '-');
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    $role = $p['role'] ?? 'pegawai';
                                                    if ($role === 'admin') echo 'danger';
                                                    elseif (in_array($role, ['dekan', 'wakil_rektor_2'])) echo 'dark';
                                                    elseif (in_array($role, ['kajur', 'wdku', 'wdak', 'kaprodi'])) echo 'primary';
                                                    else echo 'info';
                                                ?>">
                                                    <?php echo strtoupper(str_replace('_', ' ', $role)); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" onclick="editPegawai(<?php echo htmlspecialchars(json_encode($p)); ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus pegawai ini?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
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
    
    <!-- Modal Pegawai -->
    <div class="modal fade" id="modalPegawai" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Tambah Pegawai</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id" id="formId">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">NIP/NP *</label>
                                <input type="text" class="form-control" name="nip_np" id="nip_np" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Pegawai *</label>
                                <input type="text" class="form-control" name="nama_pegawai" id="nama_pegawai" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password *</label>
                                <input type="password" class="form-control" name="password_pegawai" id="password_pegawai" required>
                                <small class="text-muted">Kosongkan jika edit dan tidak ingin mengubah password</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role *</label>
                                <select class="form-select" name="role" id="role" onchange="toggleUnitKerja()" required>
                                    <option value="pegawai">Pegawai</option>
                                    <option value="dosen">Dosen</option>
                                    <option value="dosen_tugas">Dosen Tugas</option>
                                    <option value="kaprodi">Kaprodi</option>
                                    <option value="kajur">Kajur</option>
                                    <option value="wdku">WDKU</option>
                                    <option value="wdak">WDAK</option>
                                    <option value="dekan">Dekan</option>
                                    <option value="wakil_rektor_2">Wakil Rektor 2</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" id="username">
                            </div>
                            
                            <!-- Section Jurusan (Untuk role tingkat Fakultas/Jurusan) -->
                            <div class="col-md-6 mb-3" id="jurusanSection" style="display: none;">
                                <label class="form-label">Jurusan / Fakultas</label>
                                <select class="form-select" name="jurusan_id" id="jurusan_id">
                                    <option value="">Pilih Jurusan</option>
                                    <?php foreach ($jurusanList as $jurusan): ?>
                                    <option value="<?php echo $jurusan['id']; ?>">
                                        <?php echo htmlspecialchars($jurusan['nama_jurusan']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Section Prodi (Untuk role tingkat Prodi/Dosen/Pegawai) -->
                            <div class="col-md-6 mb-3" id="prodiSection">
                                <label class="form-label">Program Studi</label>
                                <select class="form-select" name="prodi_id" id="prodi_id">
                                    <option value="">Pilih Prodi</option>
                                    <?php foreach ($prodiList as $prodi): ?>
                                    <option value="<?php echo $prodi['id']; ?>">
                                        <?php echo htmlspecialchars($prodi['nama_prodi']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tempat Lahir</label>
                                <input type="text" class="form-control" name="tempat_lahir" id="tempat_lahir">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="date" class="form-control" name="tanggal_lahir" id="tanggal_lahir">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Kelamin</label>
                                <select class="form-select" name="jenis_kelamin" id="jenis_kelamin">
                                    <option value="">Pilih</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. HP</label>
                                <input type="text" class="form-control" name="no_hp" id="no_hp">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" name="alamat" id="alamat" rows="3"></textarea>
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
        // Map Prodi ID to Jurusan ID from PHP
        const prodiToJurusanMap = <?php 
            $map = [];
            foreach ($prodiList as $prodi) {
                // Assuming prodi list includes 'jurusan_id'
                if (isset($prodi['jurusan_id'])) {
                    $map[$prodi['id']] = $prodi['jurusan_id'];
                }
            }
            echo json_encode($map);
        ?>;

        function resetForm() {
            document.getElementById('formAction').value = 'create';
            document.getElementById('formId').value = '';
            document.getElementById('modalTitle').textContent = 'Tambah Pegawai';
            document.getElementById('password_pegawai').required = true;
            document.querySelector('form').reset();
            // Default role is pegawai
            document.getElementById('role').value = 'pegawai';
            toggleUnitKerja();
        }
        
        function editPegawai(data) {
            document.getElementById('formAction').value = 'update';
            document.getElementById('formId').value = data.id;
            document.getElementById('modalTitle').textContent = 'Edit Pegawai';
            document.getElementById('nip_np').value = data.nip_np || '';
            document.getElementById('nama_pegawai').value = data.nama_pegawai || '';
            document.getElementById('password_pegawai').value = '';
            document.getElementById('password_pegawai').required = false;
            document.getElementById('role').value = data.role || 'pegawai';
            document.getElementById('username').value = data.username || '';
            document.getElementById('prodi_id').value = data.prodi_id || '';
            document.getElementById('jurusan_id').value = data.jurusan_id || ''; 
            document.getElementById('tempat_lahir').value = data.tempat_lahir || '';
            document.getElementById('tanggal_lahir').value = data.tanggal_lahir || '';
            document.getElementById('jenis_kelamin').value = data.jenis_kelamin || '';
            document.getElementById('no_hp').value = data.no_hp || '';
            document.getElementById('alamat').value = data.alamat || '';
            
            toggleUnitKerja();
            
            const modal = new bootstrap.Modal(document.getElementById('modalPegawai'));
            modal.show();
        }

        function toggleUnitKerja() {
            const role = document.getElementById('role').value;
            const prodiSection = document.getElementById('prodiSection');
            const jurusanSection = document.getElementById('jurusanSection');
            
            // Logic Display
            let showProdi = true;
            let showJurusan = false; // Default hidden

            if (role === 'wakil_rektor_2') {
                showProdi = false;
                showJurusan = false;
            } else if (['admin'].includes(role)) {
                showProdi = true;
                showJurusan = true;
            } else if (['dekan', 'wdak', 'wdku', 'kajur'].includes(role)) {
                // Roles that usually operate at Jurusan/Faculty level
                showProdi = false;
                showJurusan = true;
            } else {
                // Roles at Prodi level (Dosen, Pegawai, Kaprodi, Dosen Tugas)
                showProdi = true;
                showJurusan = false;
            }

            prodiSection.style.display = showProdi ? 'block' : 'none';
            jurusanSection.style.display = showJurusan ? 'block' : 'none';
        }

        // Auto-select Jurusan when Prodi changes
        document.getElementById('prodi_id').addEventListener('change', function() {
            const selectedProdiId = this.value;
            if (prodiToJurusanMap[selectedProdiId]) {
                const associatedJurusanId = prodiToJurusanMap[selectedProdiId];
                
                // Set value
                document.getElementById('jurusan_id').value = associatedJurusanId;
                
                // Use a little trick: if Jurusan section is hidden but we need to submit it, 
                // the form submission handles it. 
                // However, user said "jurusan otomatis terisi". 
                // Since user might be creating a 'Pegawai' (Prodi level), the Jurusan field is hidden.
                // But we still update the SELECT input so it gets POSTed.
                
                // Optional: If you want to visualize it for debugging, you can console log
                // console.log("Auto-selected Jurusan ID:", associatedJurusanId);
            }
        });
    </script>
</body>
</html>
