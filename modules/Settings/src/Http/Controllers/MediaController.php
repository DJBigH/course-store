<?php

namespace Modules\Settings\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\ActiveLogs\src\Models\ActiveLog;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()?->hasPermission('media.manage')) {
            abort(403);
        }

        $disk = $request->get('disk', 'public');
        $path = $request->get('path', '');
        
        $files = [];
        $directories = [];

        try {
            $allDirectories = Storage::disk($disk)->directories($path);
            $allFiles = Storage::disk($disk)->files($path);

            foreach ($allDirectories as $dir) {
                $directories[] = [
                    'name' => basename($dir),
                    'path' => $dir,
                    'type' => 'directory',
                ];
            }

            foreach ($allFiles as $file) {
                $filename = basename($file);
                
                // Ẩn tệp nhạy cảm (bắt đầu bằng .)
                if (str_starts_with($filename, '.')) {
                    continue;
                }

                $size = Storage::disk($disk)->size($file);
                $mime = Storage::disk($disk)->mimeType($file);
                $url = Storage::disk($disk)->url($file);
                
                $files[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'type' => 'file',
                    'size' => $this->formatBytes($size),
                    'mime' => $mime,
                    'url' => $url,
                    'is_image' => str_contains($mime, 'image'),
                    'is_video' => str_contains($mime, 'video'),
                    'last_modified' => date('Y-m-d H:i:s', Storage::disk($disk)->lastModified($file)),
                ];
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        $pageTitle = 'Thư viện Media';
        $pageName = 'Thư viện Media';

        return view('settings::admin.media', compact(
            'files',
            'directories',
            'disk',
            'path',
            'pageTitle',
            'pageName'
        ))->with('error', $error ?? null);
    }

    public function delete(Request $request)
    {
        if (!auth()->user()?->hasPermission('media.delete')) {
            return response()->json(['error' => 'Bạn không có quyền xóa tệp tin này.'], 403);
        }

        $disk = $request->get('disk');
        $path = $request->get('path');

        if (!$disk || !$path) {
            return response()->json(['error' => 'Thiếu thông tin tệp.'], 400);
        }

        try {
            Storage::disk($disk)->delete($path);
            
            ActiveLog::log(
                action: 'media_delete',
                logName: 'admin_media_management',
                description: "Đã xóa tệp {$path} từ đĩa {$disk}."
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
