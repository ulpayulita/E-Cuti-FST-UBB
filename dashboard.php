<?php
require_once 'config.php';
requireAuth();

// Get statistics
$token = getAuthToken();

// Get all data for statistics
$izinCutiResponse = apiRequest(API_IZIN_CUTI, 'GET', null, $token);
$izinKetidakhadiranResponse = apiRequest(API_IZIN_KETIDAKHADIRAN, 'GET', null, $token);

$izinCuti = $izinCutiResponse['data'] ?? [];
$izinKetidakhadiran = $izinKetidakhadiranResponse['data'] ?? [];

$user = getCurrentUser();
$userRole = $user['role'] ?? '';

// Hanya ambil data pegawai jika bukan WKDU
$totalPegawai = 0;
if ($userRole !== 'wkdu') {
    $pegawaiResponse = apiRequest(API_PEGAWAI, 'GET', null, $token);
    $pegawai = $pegawaiResponse['data'] ?? [];
    $totalPegawai = count($pegawai);
}

// Calculate statistics
$totalIzinCuti = count($izinCuti);
$totalIzinKetidakhadiran = count($izinKetidakhadiran);
$pendingCuti = count(array_filter($izinCuti, fn($item) => ($item['status'] ?? 'pending') === 'pending'));
$pendingKetidakhadiran = count(array_filter($izinKetidakhadiran, fn($item) => ($item['status'] ?? 'pending') === 'pending'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin E-cuti FST</title>
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
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-2 mb-4 border-bottom" style="margin-top: 30px;">
                    <h1 class="h2"><i class="bi bi-speedometer2"></i> Dashboard</h1>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <?php if ($userRole !== 'wkdu'): ?>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card stat-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Total Pegawai</h6>
                                        <h3 class="mb-0"><?php echo $totalPegawai; ?></h3>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-people-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="col-md-<?php echo $userRole === 'wkdu' ? '4' : '3'; ?> mb-3">
                        <div class="card stat-card stat-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Izin Cuti</h6>
                                        <h3 class="mb-0"><?php echo $totalIzinCuti; ?></h3>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-calendar-check-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-<?php echo $userRole === 'wkdu' ? '4' : '3'; ?> mb-3">
                        <div class="card stat-card stat-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Izin Ketidakhadiran</h6>
                                        <h3 class="mb-0"><?php echo $totalIzinKetidakhadiran; ?></h3>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-calendar-x-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-<?php echo $userRole === 'wkdu' ? '4' : '3'; ?> mb-3">
                        <div class="card stat-card stat-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Menunggu Persetujuan</h6>
                                        <h3 class="mb-0"><?php echo $pendingCuti + $pendingKetidakhadiran; ?></h3>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-clock-history"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Izin Cuti -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Izin Cuti Terbaru</h5>
                                <a href="izin_cuti.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($izinCuti)): ?>
                                    <p class="text-muted text-center">Tidak ada data</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Pegawai</th>
                                                    <th>Tanggal</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (array_slice($izinCuti, 0, 5) as $item): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($item['nama_pegawai'] ?? '-'); ?></td>
                                                    <td><?php echo formatDate($item['tanggal_awal'] ?? ''); ?></td>
                                                    <td><?php echo getStatusBadge($item['status'] ?? 'pending'); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Izin Ketidakhadiran -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-calendar-x"></i> Izin Ketidakhadiran Terbaru</h5>
                                <a href="izin_ketidakhadiran.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($izinKetidakhadiran)): ?>
                                    <p class="text-muted text-center">Tidak ada data</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Pegawai</th>
                                                    <th>Tanggal</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (array_slice($izinKetidakhadiran, 0, 5) as $item): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($item['nama_pegawai'] ?? '-'); ?></td>
                                                    <td><?php echo formatDate($item['tanggal_izin'] ?? ''); ?></td>
                                                    <td><?php echo getStatusBadge($item['status'] ?? 'pending'); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
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
