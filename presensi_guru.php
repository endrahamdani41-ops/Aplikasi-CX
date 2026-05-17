<?php
// ======================
// KONFIGURASI DATABASE
// ======================
$host = "sql301.infinityfree.com";
$user = "if0_41722130";
$pass = "Smpn110jkT";
$db   = "if0_41722130_presensi_guru";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) die("Koneksi gagal");

// ======================
// SIMPAN DATA
// ======================
if (isset($_POST['simpan'])) {
    $nama = trim($_POST['teacher_name']);
    $tanggal = $_POST['tanggal_input']; 
    $status = $_POST['status'];
    $keterangan = trim($_POST['reason']);
    $mapel = $_POST['subject'];
    $kelas = $_POST['class_name'];
    $jam_array = isset($_POST['class_hour']) ? $_POST['class_hour'] : [];

    if ($nama && $status && $mapel && !empty($jam_array) && $kelas && $tanggal) {
        $jam_string = implode(',', $jam_array);
        $stmt = $conn->prepare("INSERT INTO absensi (nama, tanggal, status, keterangan, mapel, jam_ke, kelas) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssss", $nama, $tanggal, $status, $keterangan, $mapel, $jam_string, $kelas);
        $stmt->execute();
        header("Location: ?success=1");
        exit();
    }
}

// HAPUS DATA
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM absensi WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: ?");
    exit();
}

$data = mysqli_query($conn, "SELECT * FROM absensi ORDER BY tanggal DESC, id DESC LIMIT 50");

// MASTER DATA
$guru = ["Sunardi, M.Pd.", "Popy, S.Ag.", "Purwanto, S.Pd.", "Sri Murwani, M.Pd.", "Endang Retna Ningsih, M.Pd.", "Badriah, M.Pd.", "Lelalahi, S.Pd.", "Dra. Lilin Suryani, M.M.", "Endra Triyanto Hamdani, S.Kom.", "Asril Yansah, S.Pd.", "Sukiswati, S.Pd.", "Ing.Muh. Nurus Syiroj, S.Pd.", "Sri Purnamawati, S.Pd.", "Suningsih, S.Pd.", "Bahrul Ulum, S.Pd.", "Siti Maimona, S.Pd.I, M.M.", "Sapari, S.Pd.", "Hani Realita Alvi, S.Pd.", "Amrullah, S.Pd.I, S.Pd.", "Eny Setyowati, S.Pd.", "Hasbi Nurhidayat, S.Sos.I", "Nurina Permatasari, S.Pd.", "Heru Damara, S.Pd.", "Endah Sulstyorini, M.Pd.", "Riswadi, S.Pd.", "Sidiq Prakoso, S.Pd."];
$mapel_kurmer = ["PAI", "Pendidikan Pancasila", "Bahasa Indonesia", "Matematika", "IPA", "IPS", "Bahasa Inggris", "PJOK", "Informatika", "Seni Budaya", "BK"];
$pilihan_kelas = [];
foreach (['7', '8', '9'] as $t) { foreach (range('A', 'F') as $h) { $pilihan_kelas[] = $t . $h; } }
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presensi Guru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #0f172a; color: #e2e8f0; font-family: sans-serif; overflow-x: hidden; }
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); }
        .check-item input:checked + span { background-color: #8b5cf6; border-color: #8b5cf6; color: white; }
        /* Responsivitas untuk input date dan select agar tidak zoom di iPhone */
        input, select, textarea { font-size: 16px !important; }
    </style>
</head>
<body class="p-3 md:p-6">
    <div class="max-w-7xl mx-auto">
        <header class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold text-indigo-400">PRESENSI GURU</h1>
            <button onclick="exportExcel()" class="bg-emerald-600 hover:bg-emerald-500 px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2 transition-all">
                <i class="fa-solid fa-file-excel"></i> Excel
            </button>
        </header>

        <div class="grid lg:grid-cols-12 gap-6">
            <div class="lg:col-span-4">
                <div class="glass p-5 rounded-2xl">
                    <form method="POST" class="space-y-4">
                        <input type="date" name="tanggal_input" value="<?= date('Y-m-d') ?>" class="w-full p-3 rounded-xl bg-slate-800 border border-slate-700 outline-none text-white" required>
                        <select name="teacher_name" class="w-full p-3 rounded-xl bg-slate-800 border border-slate-700 outline-none text-white" required>
                            <option value="">-- Pilih Guru --</option>
                            <?php foreach($guru as $g): ?> <option value="<?= $g ?>"><?= $g ?></option> <?php endforeach; ?>
                        </select>
                        <div class="grid grid-cols-2 gap-3">
                            <select name="status" class="w-full p-3 rounded-xl bg-slate-800 border border-slate-700 outline-none text-white">
                                <option>Sakit</option><option>Izin</option><option>Alpha</option>
                            </select>
                            <select name="class_name" class="w-full p-3 rounded-xl bg-slate-800 border border-slate-700 outline-none text-white" required>
                                <option value="">Kelas</option>
                                <?php foreach($pilihan_kelas as $k): ?> <option value="<?= $k ?>"><?= $k ?></option> <?php endforeach; ?>
                            </select>
                        </div>
                        <select name="subject" class="w-full p-3 rounded-xl bg-slate-800 border border-slate-700 outline-none text-white" required>
                            <option value="">-- Mata Pelajaran --</option>
                            <?php foreach($mapel_kurmer as $m): ?> <option value="<?= $m ?>"><?= $m ?></option> <?php endforeach; ?>
                        </select>
                        <div class="grid grid-cols-4 gap-2">
                            <?php for($i=1; $i<=8; $i++): ?>
                            <label class="check-item cursor-pointer">
                                <input type="checkbox" name="class_hour[]" value="<?= $i ?>" class="hidden">
                                <span class="block text-center py-2 rounded-lg border border-slate-700 text-xs font-bold transition-all text-slate-400"><?= $i ?></span>
                            </label>
                            <?php endfor; ?>
                        </div>
                        <textarea name="reason" class="w-full p-3 rounded-xl bg-slate-800 border border-slate-700 outline-none text-white text-sm" placeholder="Alasan ketidakhadiran..."></textarea>
                        <button name="simpan" class="w-full bg-indigo-600 hover:bg-indigo-500 p-4 rounded-xl font-bold uppercase tracking-widest active:scale-95 transition-all shadow-lg">Simpan Data</button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-8">
                <div class="relative mb-4">
                    <input type="text" id="search" placeholder="Cari data..." class="w-full p-4 pl-12 rounded-2xl glass outline-none text-sm border-none shadow-xl">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                </div>
                
                <div class="glass rounded-2xl overflow-hidden shadow-2xl">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" id="tabel-tampil">
                            <thead>
                                <tr class="bg-slate-800/80 text-slate-400 uppercase text-[10px] border-b border-slate-700">
                                    <th class="p-3">Info Guru</th>
                                    <th class="p-3 hidden md:table-cell">Mata Pelajaran</th>
                                    <th class="p-3 text-center">Kls</th>
                                    <th class="p-3 text-center">Jam</th>
                                    <th class="p-3 hidden md:table-cell">Alasan</th>
                                    <th class="p-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php mysqli_data_seek($data, 0); while($d = mysqli_fetch_assoc($data)): ?>
                                <tr class="search-item border-b border-slate-800/50 hover:bg-white/5 transition-all">
                                    <td class="p-3">
                                        <div class="font-bold text-white text-sm"><?= htmlspecialchars($d['nama']) ?></div>
                                        <div class="md:hidden text-[10px] text-slate-500 mt-1 uppercase leading-tight">
                                            <?= date('d/m/y', strtotime($d['tanggal'])) ?> • <span class="text-indigo-400"><?= $d['status'] ?></span> • <?= $d['mapel'] ?>
                                            <?php if($d['keterangan']): ?>
                                                <div class="text-slate-400 italic normal-case mt-1 border-l-2 border-slate-700 pl-2">"<?= htmlspecialchars($d['keterangan']) ?>"</div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="p-3 text-xs text-slate-400 hidden md:table-cell"><?= htmlspecialchars($d['mapel']) ?></td>
                                    <td class="p-3 text-center font-bold text-xs"><?= $d['kelas'] ?></td>
                                    <td class="p-3 text-center font-mono text-purple-400 text-xs"><?= $d['jam_ke'] ?></td>
                                    <td class="p-3 text-xs text-slate-400 italic hidden md:table-cell max-w-[150px] truncate">
                                        <?= htmlspecialchars($d['keterangan']) ?: '-' ?>
                                    </td>
                                    <td class="p-3 text-right">
                                        <a href="?hapus=<?= $d['id'] ?>" onclick="return confirm('Hapus data ini?')" class="text-red-500 hover:text-red-400 bg-red-500/10 p-2 rounded-lg transition-all">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <p class="text-[10px] text-slate-500 mt-4 text-center italic">Tampilan HP telah disederhanakan. Gunakan tombol Excel untuk laporan lengkap per kolom.</p>
            </div>
        </div>
    </div>

    <table id="tabel-ekspor" style="display:none">
        <thead>
            <tr><th>Tanggal</th><th>Nama Guru</th><th>Status</th><th>Mata Pelajaran</th><th>Kelas</th><th>Jam Ke</th><th>Alasan</th></tr>
        </thead>
        <tbody>
            <?php mysqli_data_seek($data, 0); while($e = mysqli_fetch_assoc($data)): ?>
            <tr>
                <td><?= $e['tanggal'] ?></td>
                <td><?= $e['nama'] ?></td>
                <td><?= $e['status'] ?></td>
                <td><?= $e['mapel'] ?></td>
                <td><?= $e['kelas'] ?></td>
                <td><?= $e['jam_ke'] ?></td>
                <td><?= htmlspecialchars($e['keterangan']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <script>
        // Pencarian Real-time
        document.getElementById("search").addEventListener("keyup", function() {
            let v = this.value.toLowerCase();
            document.querySelectorAll(".search-item").forEach(i => {
                i.style.display = i.innerText.toLowerCase().includes(v) ? "" : "none";
            });
        });

        // Ekspor Excel yang tetap memisahkan kolom
        function exportExcel() {
            let table = document.getElementById("tabel-ekspor");
            let wb = XLSX.utils.table_to_book(table, { sheet: "Laporan" });
            let today = new Date().toISOString().slice(0, 10);
            XLSX.writeFile(wb, "Presensi_Guru_" + today + ".xlsx");
        }
    </script>
</body>
</html>