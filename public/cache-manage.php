<?php
// file: cache_manage.php
// ⚠ Hapus file ini setelah digunakan untuk keamanan

use Illuminate\Support\Facades\Artisan;

$base_dir = realpath(__DIR__ . '/../');
require $base_dir . '/vendor/autoload.php';
$app = require_once $base_dir . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Helper untuk hapus file dengan aman
function safeDelete($filePath)
{
    if (file_exists($filePath)) {
        @unlink($filePath);
    }
}

// Ambil action dari query string
$action = $_GET['action'] ?? null;

if (!$action) {
    echo "Harap tambahkan parameter ?action=generate atau ?action=clear";
    exit;
}

try {
    if ($action === 'generate') {
        // Clear Blade views
        $kernel->call('view:clear');

        // Generate config & route cache
        $kernel->call('config:cache');
        $kernel->call('route:cache');

        echo "✅ Cache berhasil di-generate dan view cache dibersihkan.";
    } elseif ($action === 'clear') {
        // Hapus file config.php dan routes.php
        safeDelete($base_dir . '/bootstrap/cache/config.php');
        safeDelete($base_dir . '/bootstrap/cache/routes-v7.php');

        echo "✅ Cache config/routes berhasil dihapus.";
    } else {
        echo "Parameter action tidak valid. Gunakan 'generate' atau 'clear'.";
    }
} catch (Throwable $e) {
    echo "⚠ Terjadi error saat mengeksekusi cache: " . $e->getMessage();
}
