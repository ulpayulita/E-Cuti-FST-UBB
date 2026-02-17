<?php
require_once 'config.php';
requireAuth();

$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID tidak ditemukan");
}

$token = getAuthToken();
$response = apiRequest(API_IZIN_CUTI . '?id=' . $id, 'GET', null, $token);
$data = $response['data'] ?? null;

if (!$data) {
    die("Data tidak ditemukan");
}

// Helper untuk format tanggal indonesia
function tgl_indo($tanggal)
{
    $bulan = array(
        1 => 'Januari',
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

    // variabel pecahkan 0 = tanggal
    // variabel pecahkan 1 = bulan
    // variabel pecahkan 2 = tahun

    return $pecahkan[2] . ' ' . $bulan[(int) $pecahkan[1]] . ' ' . $pecahkan[0];
}

// Data Mockup untuk field yang belum ada di database
$jabatan = "Staf/Pegawai";
$masaKerja = "-";
$unitKerja = $data['nama_prodi'] ?? $data['nama_jurusan'] ?? "Universitas Bangka Belitung";
$alamat = $data['alamat'] ?? "-";
$noHP = $data['no_hp'] ?? "-";

// Nama Pejabat (Bisa dibuat dinamis nanti)
$atasanLangsung = "Ketua Jurusan / Koordinator Prodi";
$pejabatBerwenang = "Wakil Rektor Bidang Umum dan Keuangan";
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Izin Cuti</title>
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

        th,
        td {
            border: 1px solid black;
            padding: 1px 3px;
            vertical-align: top;
        }

        .no-border,
        .no-border td {
            border: none;
        }

        .text-center {
            text-align: center;
        }
        
        .text-bold {
            font-weight: bold;
        }

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
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <div></div>
            <div style="width: 50%;">
                <p>Bangka, <?php echo tgl_indo(date('Y-m-d')); ?></p>
                <p>Kepada Yth. Wakil Rektor Bidang Umum dan Keuangan<br>di Bangka</p>
            </div>
        </div>

        <div class="section-title" style="margin-bottom: 10px;">FORMULIR PERMINTAAN DAN PEMBERIAN CUTI</div>

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

        <!-- II. JENIS CUTI YANG DIAMBIL -->
        <!-- Helper to check -->
        <?php
        function isCuti($dbType, $checkType)
        {
            return (stripos($dbType, $checkType) !== false) ? '&#10003;' : '';
        }
        $jenis = $data['nama_cuti'] ?? '';
        ?>
        <table>
            <tr>
                <td colspan="4" class="text-bold">II. JENIS CUTI YANG DIAMBIL</td>
            </tr>
            <tr>
                <td width="40%">1. Cuti Tahunan</td>
                <td width="10%" class="text-center"><?php echo isCuti($jenis, 'Tahunan'); ?></td>
                <td width="40%">2. Cuti Besar</td>
                <td width="10%" class="text-center"><?php echo isCuti($jenis, 'Besar'); ?></td>
            </tr>
            <tr>
                <td>3. Cuti Sakit</td>
                <td class="text-center"><?php echo isCuti($jenis, 'Sakit'); ?></td>
                <td>4. Cuti Melahirkan</td>
                <td class="text-center"><?php echo isCuti($jenis, 'Melahirkan'); ?></td>
            </tr>
            <tr>
                <td>5. Cuti Karena Alasan Penting</td>
                <td class="text-center"><?php echo isCuti($jenis, 'Penting'); ?></td>
                <td>6. Cuti Diluar Tanggungan Negara</td>
                <td class="text-center"><?php echo isCuti($jenis, 'Luar Tanggungan'); ?></td>
            </tr>
        </table>

        <!-- III. ALASAN CUTI -->
        <table>
            <tr>
                <td class="text-bold">III. ALASAN CUTI</td>
            </tr>
            <tr>
                <td style="height: 25px; vertical-align: middle;"><?php echo htmlspecialchars($data['keterangan']); ?>
                </td>
            </tr>
        </table>

        <!-- IV. LAMANYA CUTI -->
        <table>
            <tr>
                <td colspan="6" class="text-bold">IV. LAMANYA CUTI</td>
            </tr>
            <tr>
                <td width="15%">Selama</td>
                <td width="20%"><?php echo $data['jumlah_cuti']; ?> Hari</td>
                <td width="15%">mulai tanggal</td>
                <td width="50%"><?php echo tgl_indo($data['tanggal_awal']); ?> s.d.
                    <?php echo tgl_indo($data['tanggal_akhir']); ?>
                </td>
            </tr>
        </table>

        <!-- V. CATATAN CUTI -->
        <table>
            <tr>
                <td colspan="5" class="text-bold">V. CATATAN CUTI</td>
            </tr>
            <tr>
                <td colspan="3" width="50%">1. CUTI TAHUNAN</td>
                <td width="25%">2. CUTI BESAR</td>
                <td width="25%"></td>
            </tr>
            <tr>
                <td width="15%" class="text-center">Tahun</td>
                <td width="15%" class="text-center">Sisa</td>
                <td width="20%" class="text-center">Keterangan</td>
                <td>3. CUTI SAKIT</td>
                <td></td>
            </tr>
            <tr>
                <td class="text-center">N-2</td>
                <td class="text-center"></td>
                <td></td>
                <td>4. CUTI MELAHIRKAN</td>
                <td></td>
            </tr>
            <tr>
                <td class="text-center">N-1</td>
                <td class="text-center"></td>
                <td></td>
                <td>5. CUTI KARENA ALASAN PENTING</td>
                <td></td>
            </tr>
            <tr>
                <td class="text-center">N</td>
                <td class="text-center"></td>
                <td></td>
                <td>6. CUTI DI LUAR TANGGUNGAN NEGARA</td>
                <td></td>
            </tr>
        </table>

        <!-- VI. ALAMAT SELAMA MENJALANKAN CUTI -->
        <table>
            <tr>
                <td colspan="3" class="text-bold">VI. ALAMAT SELAMA MENJALANKAN CUTI</td>
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
                    <span
                        style="border-bottom: 1px solid black; font-weight: bold;"><?php echo htmlspecialchars($data['nama_pegawai']); ?></span><br>
                    NIP. <?php echo htmlspecialchars($data['nip_np']); ?>
                </td>
            </tr>
        </table>

        <!-- VII. PERTIMBANGAN ATASAN LANGSUNG -->
        <table>
            <tr>
                <td colspan="4" class="text-bold">VII. PERTIMBANGAN ATASAN LANGSUNG</td>
            </tr>
            <tr>
                <td width="20%" class="text-center">DISETUJUI</td>
                <td width="20%" class="text-center">PERUBAHAN</td>
                <td width="20%" class="text-center">DITANGGUHKAN</td>
                <td width="20%" class="text-center">TIDAK DISETUJUI</td>
            </tr>
            <tr>
                <td class="text-center" style="height: 15px;">
                    <?php if ($data['status'] == 'approved_1' || $data['status'] == 'approved')
                        echo '&#10003;'; ?>
                </td>
                <td></td>
                <td></td>
                <td class="text-center">
                    <?php if ($data['status'] == 'rejected' || $data['status'] == 'rejected_1')
                        echo '&#10003;'; ?>
                </td>
            </tr>
            <tr>
                <td colspan="4" class="text-center" style="height: 80px; vertical-align: bottom; padding-bottom: 10px;">
                    <?php if ($data['status'] == 'approved_1' || $data['status'] == 'approved'): ?>
                        <br>
                        <span
                            style="border-bottom: 1px solid black; font-weight: bold; text-decoration: none; padding-bottom: 5px; display: inline-block;"><?php echo htmlspecialchars($data['approver_1_name'] ?? '(Approver 1 / Atasan Langsung)'); ?></span><br>
                        NIP. <?php echo htmlspecialchars($data['approver_1_nip'] ?? '...........................'); ?>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <!-- VIII. KEPUTUSAN PEJABAT -->
        <table>
            <tr>
                <td colspan="4" class="text-bold">VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI</td>
            </tr>
            <tr>
                <td width="20%" class="text-center">DISETUJUI</td>
                <td width="20%" class="text-center">PERUBAHAN</td>
                <td width="20%" class="text-center">DITANGGUHKAN</td>
                <td width="20%" class="text-center">TIDAK DISETUJUI</td>
            </tr>
            <tr>
                <td class="text-center" style="height: 15px;">
                    <?php if ($data['status'] == 'approved')
                        echo '&#10003;'; ?>
                </td>
                <td></td>
                <td></td>
                <td class="text-center">
                    <?php if ($data['status'] == 'rejected' || $data['status'] == 'rejected_2')
                        echo '&#10003;'; ?>
                </td>
            </tr>
            <tr>
                <td colspan="4" class="text-center" style="height: 80px; vertical-align: bottom; padding-bottom: 10px;">
                    <?php if ($data['status'] == 'approved'): ?>
                        <br>
                        <span
                            style="border-bottom: 1px solid black; font-weight: bold; text-decoration: none; padding-bottom: 5px; display: inline-block;"><?php echo htmlspecialchars($data['approver_2_name'] ?? '(Approver 2 / Pejabat Berwenang)'); ?></span><br>
                        NIP. <?php echo htmlspecialchars($data['approver_2_nip'] ?? '...........................'); ?>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

    </div>

</body>

</html>