<?php

namespace Modules\ActiveLogs\src\Repositories;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;

interface ActiveLogsRepositoryInterface extends RepositoryInterface
{
    public function getAllLogs(Request $request);

    public function getTeacherLogs($teacher, Request $request);

    public function getTeacherActivityPreview($teacher, array $courseIds);

    public function getStudentActivityHistory($teacher, $student);
}
