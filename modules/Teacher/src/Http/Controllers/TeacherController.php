<?php

namespace Modules\Teacher\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
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
            ->rawColumns(['edit', 'delete', 'image'])
            ->toJson();
    }

    public function create()
    {
        $pageTitle = 'Thêm mới giáo viên';
        return view('teacher::create', compact('pageTitle'));
    }

    public function store(TeacherRequest $request)
    {
        $teacher = $request->except(['_token']);
        $this->teacherRepository->create($teacher);

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
        $teacher = $request->except('_token');

        if ($request->password) {
            $teacher['password'] = bcrypt($request->password);
        }

        $status = $this->teacherRepository->update($id, $teacher);
        if (!empty($status)) {
            return back()->with('msg', __('teacher::messages.update.success'));
        } else {
            return back()->with('msg_danger', __('teacher::messages.update.failure'));
        }
    }

    public function delete($id)
    {
        $teacher = $this->teacherRepository->find($id);
        if (empty($teacher)) {
            abort(404);
        }
        $status = $this->teacherRepository->delete($id);
        if ($status) {
            $image = $teacher->image;
            deleteFileStorage($image);
        }
        return back()->with('msg', __('teacher::messages.delete.success'));
    }
}
