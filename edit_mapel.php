<?php
// Memanggil koneksi database
require_once ('koneksi.php');

// 1. Mengambil ID Mapel dari URL ($_GET['id'])
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die("ID mata pelajaran tidak valid.");
}

// 2. LOGIKA PROSES UPDATE DATA (SEBELUM HTML DIRENDER)
if (isset($_POST['submit'])) {
    $kode_mapel = mysqli_real_escape_string($koneksi, trim($_POST['kode_mapel']));
    $nama_mapel = mysqli_real_escape_string($koneksi, trim($_POST['nama_mapel']));
    $guru_id    = mysqli_real_escape_string($koneksi, $_POST['guru_id']);

    // Cek apakah kode_mapel sudah dipakai oleh mapel LAIN
    $cek_kode = mysqli_query($koneksi, "SELECT id FROM mapel WHERE kode_mapel = '$kode_mapel' AND id != $id");

    if ($cek_kode && mysqli_num_rows($cek_kode) > 0) {
        echo "<script>
                alert('Gagal! Kode Mapel \"$kode_mapel\" sudah digunakan oleh mata pelajaran lain.');
                window.history.back();
              </script>";
        exit();
    } else {
        try {
            // Update data mapel
            $sqlUpdate = "UPDATE mapel SET 
                            kode_mapel = '$kode_mapel', 
                            nama_mapel = '$nama_mapel', 
                            guru_id = '$guru_id' 
                          WHERE id = $id";
            
            if (mysqli_query($koneksi, $sqlUpdate)) {
                echo "<script>
                        alert('Data mata pelajaran berhasil diperbarui!');
                        window.location.href = 'mapel.php';
                      </script>";
                exit();
            }
        } catch (mysqli_sql_exception $e) {
            echo "<script>
                    alert('Gagal memperbarui data: " . addslashes($e->getMessage()) . "');
                    window.history.back();
                  </script>";
            exit();
        }
    }
}

// 3. Mengambil data mapel yang akan di-edit berdasarkan ID
$queryMapel = mysqli_query($koneksi, "SELECT * FROM mapel WHERE id = $id");

if (!$queryMapel || mysqli_num_rows($queryMapel) == 0) {
    die("Data mata pelajaran tidak ditemukan.");
}

$mapel = mysqli_fetch_assoc($queryMapel);

// 4. Mengambil daftar guru untuk pilihan dropdown Guru Pengampu
$queryGuru = mysqli_query($koneksi, "SELECT id, nama FROM guru ORDER BY nama ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Mapel</title>
    <!-- Memanggil CSS Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Memanggil Navbar jika ada -->
<?php if (file_exists('navbar.php')) { require_once ('navbar.php'); } ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <!-- Kartu pembungkus form -->
            <div class="card shadow border-0">
                <div class="card-body p-4">

                    <h3 class="fw-bold mb-4">Edit Data Mata Pelajaran</h3>

                    <!-- FORM EDIT DATA MAPEL -->
                    <form method="POST" action="">
                        <!-- Input Kode Mapel -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kode Mapel</label>
                            <input type="text" name="kode_mapel" class="form-control" value="<?= htmlspecialchars($mapel['kode_mapel'] ?? ''); ?>" required placeholder="Contoh: MP001">
                        </div>

                        <!-- Input Nama Mapel -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Mata Pelajaran</label>
                            <input type="text" name="nama_mapel" class="form-control" value="<?= htmlspecialchars($mapel['nama_mapel'] ?? ''); ?>" required placeholder="Contoh: Pemrograman Web">
                        </div>

                        <!-- Pilihan Guru Pengampu -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Guru Pengampu</label>
                            <select name="guru_id" class="form-select" required>
                                <option value="">-- Pilih Guru --</option>
                                <?php 
                                if ($queryGuru && mysqli_num_rows($queryGuru) > 0) {
                                    while ($g = mysqli_fetch_assoc($queryGuru)) {
                                        // Menandai guru yang terpilih saat ini
                                        $selected = ($g['id'] == $mapel['guru_id']) ? 'selected' : '';
                                        echo "<option value='".$g['id']."' $selected>".htmlspecialchars($g['nama'])."</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="mt-4">
                            <button type="submit" name="submit" class="btn btn-primary">Simpan Perubahan</button>
                            <a href="mapel.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>