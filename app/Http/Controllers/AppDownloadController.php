<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AppDownloadController extends Controller
{
    /**
     * Download native desktop or mobile application installer.
     */
    public function download(string $platform)
    {
        $platform = strtolower(trim($platform));
        $downloadsDir = public_path('downloads');

        if (!File::isDirectory($downloadsDir)) {
            File::makeDirectory($downloadsDir, 0755, true, true);
        }

        if ($platform === 'windows' || $platform === 'exe') {
            $specificExe = $downloadsDir . '/Invenco-Setup.exe';
            if (File::exists($specificExe)) {
                return response()->download($specificExe, 'Invenco-Wholesaler-Setup.exe', [
                    'Content-Type' => 'application/octet-stream',
                ]);
            }

            // Find any .exe file in public/downloads
            $exeFiles = File::glob($downloadsDir . '/*.exe');
            if (!empty($exeFiles)) {
                $filePath = $exeFiles[0];
                return response()->download($filePath, 'Invenco-Wholesaler-Setup.exe', [
                    'Content-Type' => 'application/octet-stream',
                ]);
            }

            // Fallback to pre-built Windows package zip
            $zipFiles = File::glob($downloadsDir . '/*.zip');
            if (!empty($zipFiles)) {
                $filePath = $zipFiles[0];
                return response()->download($filePath, 'Invenco-Wholesaler-Windows.zip', [
                    'Content-Type' => 'application/zip',
                ]);
            }

            return redirect()->back()->with('error', 'Windows app package is being uploaded or compiled. Please check back shortly.');
        }

        if ($platform === 'android' || $platform === 'apk') {
            $specificApk = $downloadsDir . '/Invenco-Wholesaler.apk';
            if (File::exists($specificApk)) {
                return response()->download($specificApk, 'Invenco-Wholesaler.apk', [
                    'Content-Type' => 'application/vnd.android.package-archive',
                ]);
            }

            // Find any .apk file in public/downloads or specific names
            $apkFiles = File::glob($downloadsDir . '/*.apk');
            if (!empty($apkFiles)) {
                $filePath = $apkFiles[0];
                return response()->download($filePath, 'Invenco-Wholesaler.apk', [
                    'Content-Type' => 'application/vnd.android.package-archive',
                ]);
            }

            return redirect()->back()->with('error', 'Android APK is being uploaded or compiled. Please check back shortly.');
        }

        abort(404, 'Download platform not supported.');
    }
}
