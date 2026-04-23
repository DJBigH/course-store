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

    public function getTeacherLogs($teacher, Request $request)
    {
        $type = trim((string) $request->query('type', 'all'));
        $search = trim((string) $request->query('q', ''));
        $typeMap = [
            'students' => 'teacher_student_management',
            'courses' => 'teacher_course_management',
            'coupons' => 'teacher_coupon_management',
            'bundles' => 'teacher_bundle_management',
            'support' => 'teacher_support_management',
            'cancellation' => 'teacher_cancellation_management',
        ];

        $selectedType = array_key_exists($type, $typeMap) ? $type : 'all';

        $query = $this->model->query()
            ->where('properties->teacher_id', $teacher->id)
            ->whereIn('log_name', array_values($typeMap));

        if ($selectedType !== 'all') {
            $query->where('log_name', $typeMap[$selectedType]);
        }

        if ($search !== '') {
            $query->where(function ($nested) use ($search) {
                $nested->where('action', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('properties->student_name', 'like', '%' . $search . '%')
                    ->orWhere('properties->course_name', 'like', '%' . $search . '%')
                    ->orWhere('properties->coupon_code', 'like', '%' . $search . '%')
                    ->orWhere('properties->certificate_code', 'like', '%' . $search . '%')
                    ->orWhere('properties->teacher_name', 'like', '%' . $search . '%');
            });
        }

        $summary = [
            'total' => (clone $query)->count(),
            'students' => (clone $query)->where('log_name', $typeMap['students'])->count(),
            'courses' => (clone $query)->where('log_name', $typeMap['courses'])->count(),
            'coupons' => (clone $query)->where('log_name', $typeMap['coupons'])->count(),
            'bundles' => (clone $query)->where('log_name', $typeMap['bundles'])->count(),
        ];

        $logs = $query->latest('id')->paginate(20)->withQueryString();

        return compact('logs', 'summary', 'search', 'selectedType');
    }

    public function getTeacherActivityPreview($teacher, array $courseIds)
    {
        return $this->model->query()
            ->where('log_name', 'teacher_course_management')
            ->where(function ($query) use ($courseIds) {
                $query->where(function ($subjectQuery) use ($courseIds) {
                    $subjectQuery->where('subject_type', 'Modules\Courses\src\Models\Courses')
                        ->whereIn('subject_id', $courseIds ?: [0]);
                })->orWhere(function ($propertyQuery) use ($courseIds) {
                    $propertyQuery->whereNull('subject_id')
                        ->whereIn('properties->course_id', $courseIds ?: [0]);
                });
            })
            ->where('properties->teacher_id', $teacher->id)
            ->latest('id')
            ->get()
            ->groupBy(function ($log) {
                return (int) ($log->subject_id ?: data_get($log->properties, 'course_id', 0));
            });
    }

    public function getStudentActivityHistory($teacher, $student)
    {
        return $this->model->query()
            ->where('log_name', 'teacher_student_management')
            ->where('subject_type', get_class($student))
            ->where('subject_id', $student->id)
            ->where('properties->teacher_id', $teacher->id)
            ->latest('id')
            ->take(12)
            ->get();
    }
}
