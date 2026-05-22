<?php
$resultUrl = "";
$titleVal = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shopeeLink = $_POST['shopeeLink'] ?? '';
    $titleVal   = $_POST['title'] ?? '';
    $desc       = $_POST['desc'] ?? 'Facebook.com';
    
    if (!empty($shopeeLink) && !empty($titleVal) && isset($_FILES['imageFile'])) {
        if (!is_dir('uploads')) mkdir('uploads', 0777, true);
        $fileExtension = strtolower(pathinfo($_FILES['imageFile']['name'], PATHINFO_EXTENSION));
        $newFileName = time() . '_' . uniqid() . '.' . $fileExtension;
        $uploadTarget = 'uploads/' . $newFileName;
        
        if (move_uploaded_file($_FILES['imageFile']['tmp_name'], $uploadTarget)) {
            $dbFile = 'database.json';
            $currentData = file_exists($dbFile) ? (json_decode(file_get_contents($dbFile), true) ?? []) : [];
            $uniqueId = rand(10000, 99999);
            
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $currentDir = $protocol . $_SERVER['HTTP_HOST'] . str_replace('index.php', '', $_SERVER['REQUEST_URI']);
            
            $currentData[$uniqueId] = ['dest' => $shopeeLink, 'title' => $titleVal, 'desc' => $desc, 'img' => $currentDir . $uploadTarget];
            file_put_contents($dbFile, json_encode($currentData, JSON_PRETTY_PRINT));
            
            $resultUrl = $currentDir . "redirect.php?id=" . $uniqueId;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generator Link Affiliate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap'); body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 p-6">

<div class="max-w-5xl mx-auto">
    <div class="flex gap-2 mb-8 bg-white p-1.5 rounded-2xl shadow-sm border border-slate-200 inline-flex">
        <button onclick="showSection('generator')" id="btnGen" class="px-6 py-2 rounded-xl font-bold text-sm bg-slate-900 text-white transition">Generator</button>
        <button onclick="showSection('history')" id="btnHist" class="px-6 py-2 rounded-xl font-bold text-sm text-slate-500 hover:bg-slate-100 transition">Riwayat</button>
    </div>

    <div id="generator" class="section">
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 grid grid-cols-1 lg:grid-cols-2 overflow-hidden">
            <div class="p-8 lg:p-12">
                <form method="POST" enctype="multipart/form-data" id="genForm" class="space-y-4">
                    <div><label class="block text-xs font-semibold text-slate-400 uppercase">Link Shopee</label>
                    <input type="url" name="shopeeLink" id="shopeeLink" required class="w-full px-4 py-3 bg-slate-50 border rounded-xl mt-1"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-xs font-semibold text-slate-400 uppercase">Judul</label>
                        <input type="text" name="title" id="title" required class="w-full px-4 py-3 bg-slate-50 border rounded-xl mt-1"></div>
                        <div><label class="block text-xs font-semibold text-slate-400 uppercase">Deskripsi</label>
                        <input type="text" name="desc" id="desc" class="w-full px-4 py-3 bg-slate-50 border rounded-xl mt-1"></div>
                    </div>
                    <div><label class="block text-xs font-semibold text-slate-400 uppercase">Gambar</label>
                    <input type="file" name="imageFile" id="imageFile" required class="w-full mt-1 text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:bg-blue-600 file:text-white"></div>
                    <button type="submit" id="submitBtn" class="w-full bg-slate-900 hover:bg-slate-800 active:scale-95 text-white font-bold py-3.5 rounded-xl transition-all">Generate Link</button>
                </form>

                <div id="instantResult" class="hidden mt-6 p-5 bg-emerald-50 rounded-2xl border border-emerald-100">
                    <p class="text-xs font-bold text-emerald-800 uppercase mb-2">Sukses! Salin Link:</p>
                    <div class="flex gap-2">
                        <input type="text" id="resultLink" readonly class="w-full p-2 bg-white border border-emerald-200 rounded-lg text-sm text-emerald-700 font-mono">
                        <button onclick="copyLink()" class="bg-emerald-600 text-white px-4 rounded-lg text-sm font-bold">Copy</button>
                    </div>
                </div>
            </div>

            <div class="bg-slate-100 p-8 lg:p-12 flex flex-col items-center justify-center">
                <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden">
                    <div class="aspect-[1.91/1] bg-slate-200"><img id="prevImg" src="https://via.placeholder.com/600x315" class="w-full h-full object-cover"></div>
                    <div class="p-4"><p id="prevDesc" class="text-[10px] font-bold text-slate-400 uppercase">FACEBOOK.COM</p>
                    <p id="prevTitle" class="text-sm font-bold text-slate-900 mt-1">Judul postingan</p></div>
                </div>
            </div>
        </div>
    </div>

    <div id="history" class="section hidden bg-white rounded-3xl p-8 shadow-xl border border-slate-100">
        <h2 class="text-xl font-bold mb-6">Riwayat Anda</h2>
        <div id="historyList" class="space-y-4"></div>
    </div>
</div>

<script>
    <?php if(!empty($resultUrl)): ?>
        const finalUrl = "<?php echo $resultUrl; ?>";
        const finalTitle = "<?php echo $titleVal; ?>";
        // Tampilkan hasil instan
        document.getElementById('instantResult').classList.remove('hidden');
        document.getElementById('resultLink').value = finalUrl;
        // Simpan ke riwayat
        let history = JSON.parse(localStorage.getItem('myLinks') || '[]');
        history.unshift({ title: finalTitle, link: finalUrl });
        localStorage.setItem('myLinks', JSON.stringify(history));
    <?php endif; ?>

    function showSection(id) {
        document.querySelectorAll('.section').forEach(s => s.classList.add('hidden'));
        document.getElementById(id).classList.remove('hidden');
        document.getElementById('btnGen').className = (id === 'generator') ? 'px-6 py-2 rounded-xl font-bold text-sm bg-slate-900 text-white' : 'px-6 py-2 rounded-xl font-bold text-sm text-slate-500 hover:bg-slate-100';
        document.getElementById('btnHist').className = (id === 'history') ? 'px-6 py-2 rounded-xl font-bold text-sm bg-slate-900 text-white' : 'px-6 py-2 rounded-xl font-bold text-sm text-slate-500 hover:bg-slate-100';
        if(id === 'history') renderHistory();
    }

    function copyLink() {
        const input = document.getElementById('resultLink');
        input.select();
        document.execCommand('copy');
        alert('Link berhasil disalin!');
    }

    function renderHistory() {
        const history = JSON.parse(localStorage.getItem('myLinks') || '[]');
        document.getElementById('historyList').innerHTML = history.map(item => `
            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border">
                <div><p class="font-bold text-sm">${item.title}</p><p class="text-[10px] text-blue-600 truncate">${item.link}</p></div>
                <button onclick="navigator.clipboard.writeText('${item.link}')" class="text-xs bg-white px-3 py-1 border rounded-lg hover:bg-slate-100">Copy</button>
            </div>
        `).join('');
    }

    document.getElementById('title').addEventListener('input', (e) => document.getElementById('prevTitle').innerText = e.target.value || 'Judul postingan');
    document.getElementById('desc').addEventListener('input', (e) => document.getElementById('prevDesc').innerText = (e.target.value || 'FACEBOOK.COM').toUpperCase());
    document.getElementById('imageFile').addEventListener('change', function() {
        const reader = new FileReader();
        reader.onload = (e) => document.getElementById('prevImg').src = e.target.result;
        reader.readAsDataURL(this.files[0]);
    });
</script>
</body>
</html>
