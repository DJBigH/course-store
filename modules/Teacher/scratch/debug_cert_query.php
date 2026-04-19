<?php

use Illuminate\Support\Facades\Auth;
use Modules\Teacher\src\Models\Teacher;
use Modules\Orders\src\Models\OrderDetail;
use Modules\Courses\src\Models\TeacherCourseGrants;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulating a teacher. I need a valid teacher ID from the DB.
$teacher = Teacher::first();
if (!$teacher) {
    echo "No teacher found\n";
    exit;
}

echo "Testing for Teacher ID: {$teacher->id} (Student ID: {$teacher->student_id})\n";

$paidAccess = OrderDetail::query()
    ->join('orders', 'orders.id', '=', 'orders_detail.order_id')
    ->where('orders.status_id', 2)
    ->whereExists(function ($query) use ($teacher) {
        $query->select(DB::raw(1))
            ->from('courses')
            ->whereColumn('courses.id', 'orders_detail.course_id')
            ->where('courses.teacher_id', $teacher->id);
    })
    ->selectRaw('orders_detail.course_id, orders.student_id, "paid" as access_type, orders_detail.id as source_id');

$countPaid = $paidAccess->count();
echo "Paid Access Count: {$countPaid}\n";

$grantedAccess = TeacherCourseGrants::query()
    ->where('teacher_id', $teacher->id)
    ->selectRaw('course_id, student_id, "grant" as access_type, id as source_id');

$countGrant = $grantedAccess->count();
echo "Grant Access Count: {$countGrant}\n";

$accessUnion = $paidAccess->union($grantedAccess);

$totalAccess = DB::table(DB::raw("({$accessUnion->toSql()}) as access"))
    ->mergeBindings($accessUnion->getQuery())
    ->count();

echo "Union Total Access Count: {$totalAccess}\n";

$finalQuery = DB::table(DB::raw("({$accessUnion->toSql()}) as access"))
    ->mergeBindings($accessUnion->getQuery())
    ->join('students', 'students.id', '=', 'access.student_id')
    ->join('courses', 'courses.id', '=', 'access.course_id')
    ->select('access.*', 'students.name as student_name', 'courses.name as course_name');

echo "Final Query Count: " . $finalQuery->count() . "\n";

if ($finalQuery->count() === 0 && $totalAccess > 0) {
    echo "Potential Join Failure. Checking first student/course IDs from union:\n";
    $first = DB::table(DB::raw("({$accessUnion->toSql()}) as access"))
        ->mergeBindings($accessUnion->getQuery())
        ->first();
    if ($first) {
        print_r($first);
        $studentExists = DB::table('students')->where('id', $first->student_id)->exists();
        $courseExists = DB::table('courses')->where('id', $first->course_id)->exists();
        echo "Student ID {$first->student_id} exists: " . ($studentExists ? 'Yes' : 'No') . "\n";
        echo "Course ID {$first->course_id} exists: " . ($courseExists ? 'Yes' : 'No') . "\n";
    }
}
