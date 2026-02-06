<?php

namespace Modules\Teacher\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\Teacher\src\Http\Requests\TeacherRequest;
use Modules\Teacher\src\Repositories\TeacherRepository;
use Modules\Teacher\src\Repositories\TeacherRepositoryInterface;
use Yajra\DataTables\Facades\DataTables;

class TeacherController extends Controller
{

    protected $teacherRepository;

    public function __construct(TeacherRepositoryInterface $teacherRepository)
    {
        $this->teacherRepository = $teacherRepository;
    }
    public function index()
    {
        $pageTitle = 'Quản lý giáo viên';
        return view('teacher::lists', compact('pageTitle'));
    }

    public function data()
    {
        $teacher = $this->teacherRepository->getAllTeacher();

        return DataTables::of($teacher)
            ->addColumn('logs', function ($teachers) {
                return '<a href="' . route('teacher.logs', $teachers->id) . '" class="btn btn-info">Lịch sử</a>';
            })

            ->addColumn('edit', function ($teachers) {
                return '<a href="' . route('teacher.edit', $teachers->id) . '" class="btn btn-warning">Sửa</a>';
            })
            ->addColumn('delete', function ($teachers) {
                return '<a href="' . route('teacher.delete', $teachers->id) . '" class="btn btn-danger delete-action">Xóa</a>';
            })
            ->editColumn('created_at', function ($teachers) {
                return Carbon::parse($teachers->created_at)->format('d/m/Y H:i:s');
            })
            ->editColumn('image', function ($teachers) {
                return $teachers->image ? '<img src="' . $teachers->image . '" style="width: 80px;">' : 'Không có ảnh';
            })
            ->rawColumns(['edit', 'delete', 'image', 'logs'])
            ->toJson();
    }

    public function create()
    {
        $pageTitle = 'Thêm mới giáo viên';
        return view('teacher::create', compact('pageTitle'));
    }

    public function store(TeacherRequest $request)
    {
        $data = $request->except(['_token']);

        $teacher = $this->teacherRepository->create($data); // ✅ nên return model

        activity_log(
            action: 'create',
            subject: $teacher,
            properties: [
                'data' => array_diff_key($data, array_flip(['password'])), // không log password
            ],
            logName: 'Thêm mới',
            description: 'Tạo mới giáo viên'
        );

        return redirect()->route('teacher.index')->with('msg', __('teacher::messages.create.success'));
    }


    public function edit($id)
    {
        $pageTitle = 'Cập nhập giáo viên';
        $teacher = $this->teacherRepository->find($id);
        if (empty($teacher)) {
            abort(404);
        }

        return view('teacher::edit', compact('teacher', 'pageTitle'));
    }

    public function update(TeacherRequest $request, $id)
    {
        $teacherModel = $this->teacherRepository->find($id);
        if (empty($teacherModel)) abort(404);

        $old = $teacherModel->toArray();

        $data = $request->except('_token');

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        } else {
            unset($data['password']);
        }

        $status = $this->teacherRepository->update($id, $data);

        if (!empty($status)) {
            $teacherFresh = $this->teacherRepository->find($id);
            $new = $teacherFresh ? $teacherFresh->toArray() : [];

            // không log password
            unset($old['password'], $new['password']);

            // nếu bạn thấy bio/description HTML quá dài, chỉ log bản rút gọn:
            if (isset($old['description'])) $old['description'] = formatHtmlForLog($old['description'], 120);
            if (isset($new['description'])) $new['description'] = formatHtmlForLog($new['description'], 120);

            \activity_log(
                action: 'update',
                subject: $teacherFresh ?? $teacherModel,
                properties: [
                    'old' => $old,
                    'new' => $new,
                ],
                logName: 'Cập nhập',
                description: 'Cập nhật giáo viên'
            );

            return back()->with('msg', __('teacher::messages.update.success'));
        }

        return back()->with('msg_danger', __('teacher::messages.update.failure'));
    }


    public function delete($id)
    {
        $teacher = $this->teacherRepository->find($id);
        if (empty($teacher)) abort(404);

        $snapshot = $teacher->toArray();
        unset($snapshot['password']); // không log password

        $image = $teacher->image;

        $status = $this->teacherRepository->delete($id);

        if ($status) {
            if ($image) deleteFileStorage($image);

            \activity_log(
                action: 'delete',
                subject: $teacher,
                properties: [
                    'data' => $snapshot,
                    'deleted_image' => $image ? basename($image) : null,
                ],
                logName: 'Xóa',
                description: 'Xóa giáo viên'
            );

            return back()->with('msg', __('teacher::messages.delete.success'));
        }

        return back()->with('msg_danger', 'Xóa thất bại');
    }


    public function logs(Request $request, $id)
    {
        $teacher = $this->teacherRepository->find($id);
        if (empty($teacher)) abort(404);

        $pageTitle = "Lịch sử: {$teacher->name}";

        $query = ActiveLog::query()
            ->where('subject_type', get_class($teacher))
            ->where('subject_id', $teacher->id)->withoutGlobalScopes();

        // 🔹 Filter theo action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // 🔹 Filter theo khoảng thời gian
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // 🔹 Filter theo keyword (description hoặc log_name)
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('description', 'like', "%{$q}%")
                    ->orWhere('log_name', 'like', "%{$q}%");
            });
        }

        $logs = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('teacher::logs', compact('pageTitle', 'teacher', 'logs'));
    }
}
