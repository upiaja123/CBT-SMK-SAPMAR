<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;

class SystemAdminController extends Controller
{
    public function index()
    {
        // Require super_admin role
        abort_unless(auth()->user()->hasRole('super_admin'), 403, 'Akses ditolak.');

        // Server Information
        $serverInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'os' => php_uname('s') . ' ' . php_uname('r') . ' (' . php_uname('m') . ')',
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        ];

        // Database Information
        $dbConnection = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $dbVersion = DB::select("select version() as version")[0]->version ?? 'Unknown';
        $dbSize = 0;
        
        try {
            $databaseName = DB::connection()->getDatabaseName();
            if ($dbConnection === 'mysql') {
                $sizeQuery = DB::select("SELECT SUM(data_length + index_length) / 1024 / 1024 AS size FROM information_schema.tables WHERE table_schema = ?", [$databaseName]);
                $dbSize = round($sizeQuery[0]->size ?? 0, 2);
            }
        } catch (\Exception $e) {
            // Log error or ignore
        }

        $dbInfo = [
            'connection' => $dbConnection,
            'version' => $dbVersion,
            'size_mb' => $dbSize,
            'name' => DB::connection()->getDatabaseName()
        ];

        // Retrieve Backups
        $diskName = config('backup.backup.destination.disks')[0] ?? 'local';
        $disk = Storage::disk($diskName);
        $backupPath = config('backup.backup.name', config('app.name'));
        
        $backups = [];
        if ($disk->exists($backupPath)) {
            $files = $disk->files($backupPath);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                    $backups[] = [
                        'path' => $file,
                        'name' => basename($file),
                        'size' => round($disk->size($file) / 1024 / 1024, 2),
                        'date' => \Carbon\Carbon::createFromTimestamp($disk->lastModified($file)),
                    ];
                }
            }
        }

        // Sort backups (newest first)
        usort($backups, function($a, $b) {
            return $b['date']->timestamp - $a['date']->timestamp;
        });

        // Maintenance Mode Status
        $isDown = app()->isDownForMaintenance();

        return view('system.index', compact('serverInfo', 'dbInfo', 'backups', 'isDown'));
    }

    public function toggleMaintenance(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('super_admin'), 403, 'Akses ditolak.');

        if (app()->isDownForMaintenance()) {
            Artisan::call('up');
            // AuditLog::record('system.maintenance.disabled', null, null, null);
            return back()->with('success', 'Sistem kembali online (Maintenance Dinonaktifkan).');
        } else {
            Artisan::call('down', [
                '--secret' => 'sapmar-admin-123'
            ]);
            // AuditLog::record('system.maintenance.enabled', null, null, null);
            return back()->with('warning', 'Sistem dalam mode pemeliharaan (Maintenance Aktif). Rute khusus sistem dan login tetap dapat diakses.');
        }
    }

    public function runBackup(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('super_admin'), 403, 'Akses ditolak.');
        
        try {
            // For now, only backup DB to avoid timeout and disk space issues
            Artisan::call('backup:run', ['--only-db' => true]);
            
            // AuditLog::record('system.backup.created', null, null, null);
            return back()->with('success', 'Backup database berhasil dilakukan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal melakukan backup: ' . $e->getMessage());
        }
    }

    public function downloadBackup(Request $request)
    {
        abort_unless(auth()->user()->hasRole('super_admin'), 403, 'Akses ditolak.');
        
        $path = $request->query('path');
        $diskName = config('backup.backup.destination.disks')[0] ?? 'local';
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($diskName);

        if ($path && $disk->exists($path)) {
            return $disk->download($path);
        }

        return back()->with('error', 'File backup tidak ditemukan.');
    }
}
