<?php

namespace Modules\ActiveLogs\src\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Http\Request;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\ActiveLogs\src\Repositories\ActiveLogsRepositoryInterface;

class ActiveLogsRepository extends BaseRepository implements ActiveLogsRepositoryInterface
{
    public function getModel()
    {
        return ActiveLog::class;
    }

    public function getAllLogs(Request $request)
    {
        $query = $this->model->query()->withoutGlobalScopes();

        // ===== Filters =====
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('ip')) {
            $query->where('ip', 'like', '%' . $request->ip . '%');
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('description', 'like', "%{$q}%")
                    ->orWhere('log_name', 'like', "%{$q}%")
                    ->orWhere('action', 'like', "%{$q}%")
                    ->orWhere('causer_type', 'like', "%{$q}%")
                    ->orWhere('ip', 'like', "%{$q}%");
            });
        }

        // ===== Data =====
        $logs = $query->latest()->paginate(10)->withQueryString();

        // ===== Dropdown options (lấy từ toàn bảng để filter đầy đủ) =====
        $logNames = $this->model->withoutGlobalScopes()
            ->select('log_name')
            ->whereNotNull('log_name')
            ->distinct()
            ->orderBy('log_name')
            ->pluck('log_name');

        $actions = $this->model->withoutGlobalScopes()
            ->select('action')
            ->whereNotNull('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $subjectTypes = $this->model->withoutGlobalScopes()
            ->whereNotNull('subject_type')
            ->select('subject_type')
            ->distinct()
            ->pluck('subject_type')
            ->map(function ($type) {
                return class_basename($type); // 👈 Coupons
            })
            ->unique()
            ->values();


        return compact('logs', 'logNames', 'actions', 'subjectTypes');
    }
}
