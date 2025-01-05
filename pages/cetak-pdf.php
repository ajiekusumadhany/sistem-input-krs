<?php
require_once('../config/koneksi.php'); 
require_once('../vendor/autoload.php'); 

use Mpdf\Mpdf;

// Ambil ID mahasiswa dari parameter GET
$mhs_id = isset($_GET['idMhs']) ? (int)$_GET['idMhs'] : 0;

// Query untuk mengambil data mahasiswa
$sql_mhs = "SELECT namaMhs, nim, ipk FROM inputmhs WHERE id = ?";
$stmt_mhs = $mysqli->prepare($sql_mhs);
$stmt_mhs->bind_param("i", $mhs_id);
$stmt_mhs->execute();
$stmt_mhs->bind_result($nama_mhs, $nim_mhs, $ipk_mhs);
$stmt_mhs->fetch();
$stmt_mhs->close();

// Query untuk mengambil data matakuliah
$sql_jwl_mhs = "SELECT matakuliah, sks, kelp, ruangan FROM jwl_mhs WHERE mhs_id = ?";
$stmt_jwl_mhs = $mysqli->prepare($sql_jwl_mhs);
$stmt_jwl_mhs->bind_param("i", $mhs_id);
$stmt_jwl_mhs->execute();
$result_jwl_mhs = $stmt_jwl_mhs->get_result();

// Inisialisasi mPDF
$mpdf = new Mpdf();

// Mendapatkan tanggal cetak
$tanggal_cetak = date('d-m-Y');

// Header HTML
$html = '
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Rencana Studi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .card {
            border-radius: 10px;
            background-color: white;
        }
        .table th {
            background-color: #343a40;
            color: white;
            text-align: center;
        }
        .table td {
            text-align: center;
        }
        .total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }
        .h2 {
            font-size: 24px;
            font-weight: bold;
        }
        strong {
            font-weight: bold;
        }
        .alert {
            border: 1px solid #12c4ad;
            border-radius: 10px;
            padding: 5px;
        }

    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card p-4">
            <div class="text-center mb-4">
                <h2 class="h2">Kartu Rencana Studi</h2>
                <p class="text-muted">Lihat jadwal matakuliah yang telah diinputkan di sini!</p>
            </div>
            <div class="alert alert-info d-flex justify-content-center align-items-center" role="alert" style="font-size: 16px;">
                <p class="mb-0">
                    <strong>Mahasiswa:</strong> ' . htmlspecialchars($nama_mhs) . ' | 
                    <strong>NIM:</strong> ' . htmlspecialchars($nim_mhs) . ' | 
                    <strong>IPK:</strong> ' . number_format($ipk_mhs, 2) . ' 
                </p>
            </div>

            <table class="table table-bordered table-striped mt-3">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Matakuliah</th>
                        <th>SKS</th>
                        <th>Kelompok</th>
                        <th>Ruangan</th>
                    </tr>
                </thead>
                <tbody>';

// Menambahkan data matakuliah ke tabel
$no = 1;
$total_sks = 0;
while ($row = $result_jwl_mhs->fetch_assoc()) {
    $html .= '
                    <tr>
                        <td>' . $no++ . '</td>
                        <td>' . htmlspecialchars($row['matakuliah']) . '</td>
                        <td>' . htmlspecialchars($row['sks']) . '</td>
                        <td>' . htmlspecialchars($row['kelp']) . '</td>
                        <td>' . htmlspecialchars($row['ruangan']) . '</td>
                    </tr>';
    $total_sks += (int)$row['sks'];
}

// Menambahkan total SKS
$html .= '
                    <tr class="total-row">
                        <td colspan="2"><strong>Total SKS</strong></td>
                        <td>' . $total_sks . '</td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>
            </table>
            <p>' . $tanggal_cetak . '</p>

        </div>
    </div>
</body>
</html>';

// Menutup koneksi database
$stmt_jwl_mhs->close();
$mysqli->close();

// Memuat HTML ke mPDF
$mpdf->WriteHTML($html);

// Output file PDF
$mpdf->Output('KRS_' . $nim_mhs . '.pdf', 'D'); // 'D' untuk download
?>