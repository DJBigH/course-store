<?php

use Illuminate\Support\Facades\DB;
use Modules\Teacher\src\Models\Teacher;
use Modules\Orders\src\Models\OrderDetail;
use Modules\Courses\src\Models\TeacherCourseGrants;

require __DIR__ . '/../../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Find a teacher that has at least one order detail or grant
$teacherIdWithPaid = DB::table('orders_detail')
    ->join('courses', 'courses.id', '=', 'orders_detail.course_id')
    ->value('courses.teacher_id');

$teacherIdWithGrant = DB::table('teacher_course_grants')
    ->value('teacher_id');

$teacherId = $teacherIdWithPaid ?: $teacherIdWithGrant;

if (!$teacherId) {
    echo "No teacher with any access data found in DB\n";
    exit;
}

$teacher = Teacher::find($teacherId);
if (!$teacher) {
    echo "Teacher ID $teacherId not found in teachers table\n";
    exit;
}

echo "Testing for Teacher ID: {$teacher->id} (Name: {$teacher->student_name_snapshot})\n";

$paidAccess = OrderDetail::query()
    ->join('orders', 'orders.id', '=', 'orders_detail.order_id')
    ->where('orders.status_id', 2)
    ->whereExists(function ($query) use ($teacher) {
        $query->select(DB::raw(1))
            ->from('courses')
            ->whereColumn('courses.id', 'orders_detail.course_id')
            ->where('courses.teacher_id', $teacher->id);
    })
    ->selectRaw('orders_detail.course_id as course_id, orders.student_id as student_id, "paid" as access_type, orders_detail.id as source_id');

$countPaid = $paidAccess->count();
echo "Paid Access Count: {$countPaid}\n";

$grantedAccess = TeacherCourseGrants::query()
    ->where('teacher_id', $teacher->id)
    ->selectRaw('course_id, student_id, "grant" as access_type, id as source_id');

$countGrant = $grantedAccess->count();
echo "Grant Access Count: {$countGrant}\n";

$accessUnion = $paidAccess->union($grantedAccess);

$unionResults = $accessUnion->get();
echo "Union Results Collection Count: " . $unionResults->count() . "\n";

// Now testing the toSql approach
$unionSql = $accessUnion->toSql();
$unionBindings = $accessUnion->getBindings();

$totalAccessViaSubquery = DB::table(DB::raw("($unionSql) as access"))
    ->mergeBindings($accessUnion->getQuery())
    ->count();

echo "Total Access via toSql Subquery Count: {$totalAccessViaSubquery}\n";

if ($totalAccessViaSubquery !== $unionResults->count()) {
    echo "CRITICAL: Mapping via toSql/mergeBindings failed to match Collection count!\n";
}

$finalQuery = DB::table(DB::raw("($unionSql) as access"))
    ->mergeBindings($accessUnion->getQuery())
    ->join('students', 'students.id', '=', 'access.student_id')
    ->join('courses', 'courses.id', '=', 'access.course_id')
    ->select('access.*', 'students.name as student_name', 'courses.name as course_name');

echo "Final Query Count: " . $finalQuery->count() . "\n";
