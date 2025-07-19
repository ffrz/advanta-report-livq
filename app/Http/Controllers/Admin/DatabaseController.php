<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DatabaseController extends Controller
{
    public function index()
    {
        return inertia('admin/database/Index', []);
    }

    public function backup()
    {
        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST');

        // Pastikan path mysqldump tersedia di PATH atau berikan path lengkap
        $command = "mysqldump --user={$username} --password={$password} --host={$host} {$database} > " . storage_path('app/backup.sql');

        try {
            $process = Process::fromShellCommandline($command);
            $process->setTimeout(3600); // Set timeout for large databases
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            return response()->download(storage_path('app/backup.sql'))->deleteFileAfterSend(true);
        } catch (ProcessFailedException $exception) {
            // Handle error, e.g., log it or return an error response
            return back()->with('error', 'Gagal membuat backup: ' . $exception->getMessage());
        }
    }

    public function restore() {}
}
