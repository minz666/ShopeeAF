<?php
$resultUrl = "";
$errorMsg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shopeeLink = $_POST['shopeeLink'] ?? '';
    $title       = $_POST['title'] ?? '';
    $desc        = $_POST['desc'] ?? 'Facebook.com';
    
    if (!empty($shopeeLink) && !empty($title) && isset($_FILES['imageFile'])) {
        $file = $_FILES['imageFile'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (in_array($fileExtension, $allowedExtensions)) {
            // Pastikan folder uploads ada
            if (!is_dir('uploads')) mkdir('uploads', 0777, true);
            
            $newFileName = time() . '_' . uniqid() . '.' . $fileExtension;
            $uploadTarget = 'uploads/' . $newFileName;
            
            if (move_uploaded_file($file['tmp_name'], $uploadTarget)) {
                $dbFile = 'database.json';
                $currentData = file_exists($dbFile) ? (json_decode(file_get_contents($dbFile), true) ?? []) : [];
                
                $uniqueId = rand(10000, 99999);
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                $currentUrl = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $currentDir = str_replace('index.php', '', $currentUrl);
                
                $currentData[$uniqueId] = [
                    'dest' => $shopeeLink,
                    'title' => $title,
                    'desc' => $desc,
                    'img' => $currentDir . $uploadTarget
                ];
                
                file_put_contents($dbFile, json_encode($currentData, JSON_PRETTY_PRINT));
                $resultUrl = $currentDir . "redirect.php?id=" . $uniqueId;
            } else {
                $errorMsg = "Gagal mengupload gambar.";
            }
        } else {
            $errorMsg = "Format file tidak didukung!";
        }
    } else {
        $errorMsg = "Mohon isi semua kolom wajib.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generator Link Affiliate Modern</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap'); body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-5xl bg-white rounded-3xl shadow-xl border border-slate-100 grid grid-cols-1 lg:grid-cols-2 overflow-hidden">
        <div class="p-8 lg:p-12">
            <h1 class="text-2xl font-bold text-slate-900 mb-6">Generator Link Preview</h1>
            
            <?php if(!empty($errorMsg)): ?>
                <div class="mb-6 p-4 bg-red-50 text-red-600 text-sm rounded-2xl border border-red-100"><?php echo $errorMsg; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-5" id="genForm">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Link Shopee</label>
                    <input type="url" name="shopeeLink" id="shopeeLink" required placeholder="https://s.shopee.co.id/..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Judul</label>
                        <input type="text" name="title" id="title" required placeholder="Judul..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Deskripsi</label>
                        <input type="text" name="desc" id="desc" placeholder="Facebook.com" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Unggah Gambar</label>
                    <input type="file" name="imageFile" id="imageFile" accept="image/*" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-6 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer bg-slate-50 rounded-xl border border-slate-200">
                </div>
                <button type="submit" id="submitBtn" class="w-full bg-slate-900 hover:bg-slate-800 active:scale-95 text-white font-bold py-3.5 rounded-xl transition-all duration-200 shadow-lg mt-4 cursor-pointer">
                    Generate Link
                </button>
            </form>

            <?php if(!empty($resultUrl)): ?>
            <div class="mt-8 p-5 bg-emerald-50 rounded-2xl border border-emerald-100">
                <p class="text-xs font-bold text-emerald-800 uppercase mb-3">Link Siap Digunakan!</p>
                <div class="flex gap-2">
                    <input type="text" id="resultLink" readonly value="<?php echo $resultUrl; ?>" class="w-full p-3 bg-white border border-emerald-200 rounded-lg text-sm text-emerald-700 font-mono focus:outline-none">
                    <button type="button" onclick="copyToClipboard()" id="btnCopy" class="bg-emerald-600 hover:bg-emerald-700 active:scale-90 text-white font-bold px-5 rounded-lg text-sm transition-all duration-150 cursor-pointer">Copy</button>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="bg-slate-100 p-8 lg:p-12 flex flex-col items-center justify-center border-l border-slate-200">
            <h2 class="text-xs font-bold text-slate-400 uppercase mb-6">Live Preview</h2>
            <div class="bg-white w-full max-w-sm rounded-2xl overflow-hidden shadow-2xl">
                <div class="aspect-[1.91/1] bg-slate-200"><img id="prevImg" src="https://via.placeholder.com/600x315?text=Preview" class="w-full h-full object-cover"></div>
                <div class="p-4"><p id="prevDesc" class="text-[10px] font-bold text-slate-400 uppercase">FACEBOOK.COM</p><p id="prevTitle" class="text-sm font-bold text-slate-900 mt-1">Judul postingan Anda</p></div>
            </div>
        </div>
    </div>

    <script>
        const titleInput = document.getElementById('title');
        const descInput = document.getElementById('desc');
        const fileInput = document.getElementById('imageFile');
        const form = document.getElementById('genForm');
        const btn = document.getElementById('submitBtn');

        titleInput.addEventListener('input', () => document.getElementById('prevTitle').innerText = titleInput.value || 'Judul postingan Anda');
        descInput.addEventListener('input', () => document.getElementById('prevDesc').innerText = (descInput.value || 'FACEBOOK.COM').toUpperCase());
        fileInput.addEventListener('change', function() {
            if (this.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => document.getElementById('prevImg').src = e.target.result;
                reader.readAsDataURL(this.files[0]);
            }
        });

        form.addEventListener('submit', () => {
            btn.innerText = 'Memproses...';
            btn.disabled = true;
            btn.classList.add('opacity-70', 'cursor-not-allowed');
        });

        function copyToClipboard() {
            const copyText = document.getElementById("resultLink");
            const btnCopy = document.getElementById("btnCopy");
            navigator.clipboard.writeText(copyText.value).then(() => {
                btnCopy.innerText = "Copied!";
                btnCopy.classList.replace('bg-emerald-600', 'bg-blue-600');
                setTimeout(() => {
                    btnCopy.innerText = "Copy";
                    btnCopy.classList.replace('bg-blue-600', 'bg-emerald-600');
                }, 2000);
            });
        }
    </script>
</body>
</html>
                    'desc' => $desc,
                    'img' => $imageUrl
                ];
                
                file_put_contents($dbFile, json_encode($currentData, JSON_PRETTY_PRINT));
                
                $resultUrl = $currentDir . "redirect.php?id=" . $uniqueId;
                
            } else {
                $errorMsg = "Gagal mengupload gambar ke server.";
            }
        } else {
            $errorMsg = "Format file tidak didukung!";
        }
    } else {
        $errorMsg = "Mohon isi semua kolom wajib.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generator Link Preview Affiliate v2.1</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-xl shadow-lg w-full max-w-4xl grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <div>
            <h2 class="text-xl font-bold mb-4 text-gray-800 flex items-center gap-2">📝 Pengaturan Konten</h2>
            
            <?php if(!empty($errorMsg)): ?>
                <div class="mb-3 p-2 bg-red-100 text-red-700 text-sm rounded-lg"><?php echo $errorMsg; ?></div>
            <?php endif; ?>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Link Affiliate Shopee *</label>
                    <input type="url" name="shopeeLink" id="shopeeLink" required placeholder="https://s.shopee.co.id/..." class="mt-1 w-full p-2 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Judul Postingan (Title) *</label>
                    <input type="text" name="title" id="title" required placeholder="Contoh: Liat tatapan mereka berdua..." class="mt-1 w-full p-2 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Deskripsi Singkat</label>
                    <input type="text" name="desc" id="desc" placeholder="Contoh: Facebook.com" class="mt-1 w-full p-2 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Upload Gambar dari Galeri *</label>
                    <input type="file" name="imageFile" id="imageFile" accept="image/*" required class="mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg transition shadow-md cursor-pointer">
                    Proses & Ambil Link Pendek
                </button>
            </div>

            <?php if(!empty($resultUrl)): ?>
            <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-xl">
                <p class="text-sm font-semibold text-green-800 mb-2">✅ Sukses! Salin link dibawah ini:</p>
                <div class="flex gap-2">
                    <input type="text" id="resultLink" readonly value="<?php echo $resultUrl; ?>" class="w-full p-2 bg-white border border-green-300 rounded-lg text-sm text-blue-600 font-mono focus:outline-none">
                    
                    <button type="button" onclick="copyToClipboard()" id="btnCopy" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-4 py-2 rounded-lg text-sm transition flex items-center gap-1 shrink-0 cursor-pointer">
                        <svg id="iconCopy" class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                        <span id="textCopy">Copy</span>
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 flex flex-col justify-center">
            <h2 class="text-sm font-semibold text-gray-500 mb-3">🌐 Preview di Facebook</h2>
            <div class="bg-white border border-gray-300 rounded-lg overflow-hidden shadow-sm">
                <div class="relative bg-black h-48 flex items-center justify-center overflow-hidden">
                    <img id="prevImg" src="https://via.placeholder.com/600x400?text=Pilih+Gambar" class="w-full h-full object-cover opacity-80">
                </div>
                <div class="p-3 bg-gray-100 border-t border-gray-200">
                    <p id="prevDesc" class="text-xs text-gray-500 uppercase">FACEBOOK.COM</p>
                    <p id="prevTitle" class="text-sm font-bold text-gray-900 mt-0.5 line-clamp-2">Judul postingan Anda</p>
                </div>
            </div>
        </div>
    </form>

    <script>
        const titleInput = document.getElementById('title');
        const descInput = document.getElementById('desc');
        const fileInput = document.getElementById('imageFile');

        titleInput.addEventListener('input', () => document.getElementById('prevTitle').innerText = titleInput.value || 'Judul postingan Anda');
        descInput.addEventListener('input', () => document.getElementById('prevDesc').innerText = (descInput.value || 'FACEBOOK.COM').toUpperCase());
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) { document.getElementById('prevImg').src = e.target.result; }
                reader.readAsDataURL(file);
            }
        });

        // FUNGSI UNTUK MENANGANI COPY TO CLIPBOARD + FEEDBACK VISUAL
        function copyToClipboard() {
            const copyText = document.getElementById("resultLink");
            const btnCopy = document.getElementById("btnCopy");
            const textCopy = document.getElementById("textCopy");
            
            // Menggunakan API modern clipboard
            navigator.clipboard.writeText(copyText.value).then(() => {
                // Beri efek transisi warna hijau saat berhasil disalin
                btnCopy.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
                btnCopy.classList.add('bg-blue-600');
                textCopy.innerText = "Copied!";
                
                // Kembalikan ke tombol semula setelah 2 detik
                setTimeout(() => {
                    btnCopy.classList.remove('bg-blue-600');
                    btnCopy.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
                    textCopy.innerText = "Copy";
                }, 2000);
            }).catch(err => {
                alert("Gagal menyalin teks: ", err);
            });
        }
    </script>
</body>
</html>
