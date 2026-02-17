<?php
require_once 'config.php';
requireAuth();

$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID tidak ditemukan");
}

$token = getAuthToken();
$response = apiRequest(API_IZIN_KETIDAKHADIRAN . '?id=' . $id, 'GET', null, $token);
$data = $response['data'] ?? null;

if (!$data) {
    die("Data tidak ditemukan");
}

// Helper untuk format tanggal indonesia
function tgl_indo($tanggal){
	$bulan = array (
		1 =>   'Januari',
		'Februari',
		'Maret',
		'April',
		'Mei',
		'Juni',
		'Juli',
		'Agustus',
		'September',
		'Oktober',
		'November',
		'Desember'
	);
	$pecahkan = explode('-', $tanggal);
	return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}

$jabatan = "Staf/Pegawai";
$masaKerja = "-";
$unitKerja = $data['nama_prodi'] ?? $data['nama_jurusan'] ?? "Universitas Bangka Belitung";
$alamat = $data['alamat'] ?? "-";
$noHP = $data['no_hp'] ?? "-";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Izin Ketidakhadiran</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
            line-height: 1.1;
        }
        .container {
            width: 210mm;
            margin: 0 auto;
            padding: 5mm;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        th, td {
            border: 1px solid black;
            padding: 1px 3px;
            vertical-align: top;
        }
        .no-border, .no-border td {
            border: none;
        }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        
        .section-title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin: 5px 0;
            text-transform: uppercase;
            font-size: 11pt;
        }

        @media print {
            @page { 
                size: A4; 
                margin: 5mm; 
            }
            .no-print {
                display: none;
            }
            body {
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body onload="window.print()">

<div class="container">
    <!-- Header -->
    <div style="display: flex; align-items: center; justify-content: center; border-bottom: 2px solid black; padding-bottom: 5px; margin-bottom: 10px;">
        <img src="assets/css/logo_ubb.jpg" style="width: 60px; height: auto; margin-right: 15px;" alt="Logo UBB">
        <div class="text-center">
            <h3 style="margin:0; font-size: 10pt;">KEMENTERIAN PENDIDIKAN, KEBUDAYAAN,<br>RISET, DAN TEKNOLOGI</h3>
            <h2 style="margin:2px 0; font-size: 12pt;">UNIVERSITAS BANGKA BELITUNG</h2>
            <p style="margin:0; font-size: 8pt;">Kampus Terpadu UBB, Gedung Rektorat, Desa Balunijuk<br>
            Kecamatan Merawang, Propinsi Kepulauan Bangka Belitung 33172<br>
            Telepon (0717) 422145, 422965, Faksimile (0717) 421303<br>
            Laman www.ubb.ac.id</p>
        </div>
    </div>

    <!-- Date & Addressee -->
    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
        <div></div>
        <div style="width: 50%;">
            <p>Bangka, <?php echo tgl_indo(date('Y-m-d')); ?></p>
            <p>Kepada Yth. Wakil Rektor Bidang Umum dan Keuangan<br>di Bangka</p>
        </div>
    </div>

    <div class="section-title">FORMULIR IZIN KETIDAKHADIRAN</div>

    <!-- I. DATA PEGAWAI -->
    <table>
        <tr>
            <td colspan="4" class="text-bold">I. DATA PEGAWAI</td>
        </tr>
        <tr>
            <td width="15%">Nama</td>
            <td width="35%"><?php echo htmlspecialchars($data['nama_pegawai']); ?></td>
            <td width="15%">NIP</td>
            <td width="35%"><?php echo htmlspecialchars($data['nip_np']); ?></td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td><?php echo $jabatan; ?></td>
            <td>Masa Kerja</td>
            <td><?php echo $masaKerja; ?></td>
        </tr>
        <tr>
            <td>Unit Kerja</td>
            <td colspan="3"><?php echo htmlspecialchars($unitKerja); ?></td>
        </tr>
    </table>

    <!-- II. JENIS IZIN -->
    <table>
        <tr>
            <td class="text-bold">II. JENIS IZIN KETIDAKHADIRAN</td>
        </tr>
        <tr>
             <td><?php echo htmlspecialchars($data['jenis_izin']); ?></td>
        </tr>
    </table>

    <!-- III. ALASAN -->
    <table>
        <tr>
            <td class="text-bold">III. ALASAN KETIDAKHADIRAN</td>
        </tr>
        <tr>
            <td style="height: 25px; vertical-align: middle;"><?php echo htmlspecialchars($data['alasan']); ?></td>
        </tr>
    </table>

    <!-- IV. LAMANYA -->
    <table>
        <tr>
            <td colspan="6" class="text-bold">IV. LAMANYA IZIN</td>
        </tr>
        <tr>
            <td width="15%">Tanggal</td>
            <td colspan="3"><?php echo tgl_indo($data['tanggal_izin']); ?></td>
        </tr>
    </table>

    <!-- VI. INFORMASI KONTAK -->
    <table>
        <tr>
            <td colspan="3" class="text-bold">V. INFORMASI KONTAK</td>
        </tr>
        <tr>
            <td width="50%" rowspan="2" style="height: 40px;">
                 <?php echo htmlspecialchars($alamat); ?>
            </td>
            <td width="15%">Telp/No. HP</td>
            <td width="35%"><?php echo htmlspecialchars($noHP); ?></td>
        </tr>
        <tr>
             <td colspan="2" class="text-center">
                 <br>Hormat Saya,<br><br>
                 <span style="border-bottom: 1px solid black; font-weight: bold;"><?php echo htmlspecialchars($data['nama_pegawai']); ?></span><br>
                 NIP. <?php echo htmlspecialchars($data['nip_np']); ?>
             </td>
        </tr>
    </table>

    <!-- VII. PERTIMBANGAN ATASAN LANGSUNG -->
    <table>
        <tr>
            <td colspan="4" class="text-bold">VI. PERTIMBANGAN ATASAN LANGSUNG</td>
        </tr>
        <tr>
            <td width="20%" class="text-center">DISETUJUI</td>
            <td width="20%" class="text-center">PERUBAHAN</td>
            <td width="20%" class="text-center">DITANGGUHKAN</td>
            <td width="20%" class="text-center">TIDAK DISETUJUI</td>
        </tr>
        <tr>
             <td class="text-center" style="height: 15px;">
                 <?php if($data['status'] == 'approved_1' || $data['status'] == 'approved') echo '&#10003;'; ?>
             </td>
             <td></td>
             <td></td>
             <td class="text-center">
                 <?php if($data['status'] == 'rejected') echo '&#10003;'; ?>
             </td>
        </tr>
         <tr>
            <td colspan="4" class="text-center" style="height: 80px; vertical-align: bottom; padding-bottom: 10px;">
                <?php if($data['status'] == 'approved_1' || $data['status'] == 'approved'): ?>
                <br>
                <span style="border-bottom: 1px solid black; font-weight: bold; text-decoration: none; padding-bottom: 5px; display: inline-block;"><?php echo htmlspecialchars($data['approver_1_name'] ?? '(Approver 1 / Atasan Langsung)'); ?></span><br>
                NIP. <?php echo htmlspecialchars($data['approver_1_nip'] ?? '...........................'); ?>
                <?php endif; ?>
            </td>
        </tr>
    </table>

     <!-- VIII. KEPUTUSAN PEJABAT -->
     <table>
        <tr>
            <td colspan="4" class="text-bold">VII. KEPUTUSAN PEJABAT BERWENANG</td>
        </tr>
        <tr>
            <td width="20%" class="text-center">DISETUJUI</td>
            <td width="20%" class="text-center">PERUBAHAN</td>
            <td width="20%" class="text-center">DITANGGUHKAN</td>
            <td width="20%" class="text-center">TIDAK DISETUJUI</td>
        </tr>
        <tr>
             <td class="text-center" style="height: 15px;">
                 <?php if($data['status'] == 'approved') echo '&#10003;'; ?>
             </td>
             <td></td>
             <td></td>
             <td class="text-center">
                 <?php if($data['status'] == 'rejected') echo '&#10003;'; ?>
             </td>
        </tr>
         <tr>
            <td colspan="4" class="text-center" style="height: 80px; vertical-align: bottom; padding-bottom: 10px;">
                <?php if($data['status'] == 'approved'): ?>
                <br>
                <span style="border-bottom: 1px solid black; font-weight: bold; text-decoration: none; padding-bottom: 5px; display: inline-block;"><?php echo htmlspecialchars($data['approver_2_name'] ?? '(Approver 2 / Pejabat Berwenang)'); ?></span><br>
                NIP. <?php echo htmlspecialchars($data['approver_2_nip'] ?? '...........................'); ?>
                <?php endif; ?>
            </td>
        </tr>
    </table>

</div>

</body>
</html>
