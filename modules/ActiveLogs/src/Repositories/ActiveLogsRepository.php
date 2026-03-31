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

        if ($request->filled('subject')) {
            $subject = trim((string) $request->subject);

            $query->where(function ($sub) use ($subject) {
                if (is_numeric($subject)) {
                    $sub->orWhere('subject_id', (int) $subject);
                }

                $sub->orWhere('subject_type', 'like', "%{$subject}%");
            });
        }

        if ($request->filled('ip')) {
            $query->where('ip', 'like', '%' . $request->ip . '%');
        }

        if ($request->filled('causer')) {
            $causer = trim((string) $request->causer);

            $query->where(function ($sub) use ($causer) {
                if (is_numeric($causer)) {
                    $sub->orWhere('causer_id', (int) $causer);
                }

                $sub->orWhere('causer_type', 'like', "%{$causer}%");
            });
        }

        if ($request->filled('property')) {
            $property = trim((string) $request->property);
            $query->whereRaw('CAST(properties AS CHAR) LIKE ?', ["%{$property}%"]);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->q);

            $query->where(function ($sub) use ($q) {
                $sub->where('description', 'like', "%{$q}%")
                    ->orWhere('log_name', 'like', "%{$q}%")
                    ->orWhere('action', 'like', "%{$q}%")
                    ->orWhere('subject_type', 'like', "%{$q}%")
                    ->orWhereRaw('CAST(subject_id AS CHAR) LIKE ?', ["%{$q}%"])
                    ->orWhere('causer_type', 'like', "%{$q}%")
                    ->orWhereRaw('CAST(causer_id AS CHAR) LIKE ?', ["%{$q}%"])
                    ->orWhere('ip', 'like', "%{$q}%")
                    ->orWhere('user_agent', 'like', "%{$q}%")
                    ->orWhereRaw('CAST(properties AS CHAR) LIKE ?', ["%{$q}%"]);
            });
        }

        $logs = $query->latest()->paginate(10)->withQueryString();

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
            ->orderBy('subject_type')
            ->pluck('subject_type')
            ->map(fn ($type) => [
                'value' => $type,
                'label' => subjectLabel($type),
            ])
            ->values();

        return compact('logs', 'logNames', 'actions', 'subjectTypes');
    }
}
