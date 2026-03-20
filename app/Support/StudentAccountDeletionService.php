<?php

namespace App\Support;

use App\Mail\AccountDeletedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Modules\Courses\src\Models\CourseComment;
use Modules\Orders\src\Models\Order;
use Modules\Students\src\Models\CouponUsage;
use Modules\Students\src\Models\Student;
use Modules\Students\src\Models\StudentsCourses;

class StudentAccountDeletionService
{
    public function __construct(protected ClientMailThrottle $mailThrottle)
    {
    }

    public function delete(Student $student, Request $request, ?string $locale = null): void
    {
        $locale = $locale ?: app()->getLocale();
        $email = (string) $student->email;
        $snapshot = $this->buildMailSnapshot($student);

        DB::transaction(function () use ($student) {
            $this->snapshotOrders($student);

            activity_log(
                'account_deleted',
                $student,
                [
                    'email' => $student->email,
                    'name' => $student->name,
                ],
                'student_security',
                __('students::clients/account.activity_log.account_deleted_desc')
            );

            CourseComment::query()->where('student_id', $student->id)->delete();
            StudentsCourses::query()->where('student_id', $student->id)->delete();
            CouponUsage::query()->where('student_id', $student->id)->delete();

            DB::table('coupons_students')->where('student_id', $student->id)->delete();
            DB::table('notifications')
                ->where('notifiable_type', Student::class)
                ->where('notifiable_id', $student->id)
                ->delete();
            DB::table(config('auth.passwords.students.table'))
                ->where('email', $student->email)
                ->delete();

            $student->delete();
        });

        $this->sendDeletedMail($request, $snapshot, $email, $locale);

        Auth::guard('students')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->flash('students.account_deleted.success', true);
    }

    protected function snapshotOrders(Student $student): void
    {
        if (! $this->hasOrderSnapshotColumns()) {
            return;
        }

        Order::query()
            ->where('student_id', $student->id)
            ->update([
                'customer_name_snapshot' => DB::raw("COALESCE(customer_name_snapshot, '" . $this->escapeForSql($student->name) . "')"),
                'customer_email_snapshot' => DB::raw("COALESCE(customer_email_snapshot, '" . $this->escapeForSql($student->email) . "')"),
                'customer_phone_snapshot' => DB::raw("COALESCE(customer_phone_snapshot, '" . $this->escapeForSql((string) $student->phone) . "')"),
                'customer_address_snapshot' => DB::raw("COALESCE(customer_address_snapshot, '" . $this->escapeForSql((string) ($student->address ?? '')) . "')"),
            ]);
    }

    protected function sendDeletedMail(Request $request, array $snapshot, string $email, string $locale): void
    {
        if ($email === '') {
            return;
        }

        $throttle = config('mail.throttle.account_deleted');
        $maxAttempts = (int) ($throttle['max_attempts'] ?? 1);
        $decaySeconds = (int) ($throttle['decay_seconds'] ?? 86400);

        $throttleKey = $this->mailThrottle->key('account-deleted', [
            $request->ip(),
            $email,
            now()->format('Y-m-d'),
        ]);

        if ($this->mailThrottle->tooManyAttempts($throttleKey, $maxAttempts)) {
            return;
        }

        $this->mailThrottle->hit($throttleKey, $decaySeconds);

        Mail::to($email)
            ->locale($locale)
            ->queue(new AccountDeletedMail($snapshot, $locale));
    }

    protected function buildMailSnapshot(Student $student): array
    {
        return [
            'name' => (string) $student->name,
            'email' => (string) $student->email,
        ];
    }

    protected function escapeForSql(string $value): string
    {
        return str_replace("'", "''", $value);
    }

    protected function hasOrderSnapshotColumns(): bool
    {
        return Schema::hasColumn('orders', 'customer_name_snapshot')
            && Schema::hasColumn('orders', 'customer_email_snapshot')
            && Schema::hasColumn('orders', 'customer_phone_snapshot')
            && Schema::hasColumn('orders', 'customer_address_snapshot');
    }
}
