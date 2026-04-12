<?php

namespace App\Console\Commands;

use App\Notifications\AdminInactiveTeacherAlertNotification;
use App\Notifications\TeacherInactiveReminderNotification;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Modules\Teacher\src\Models\Teacher;
use Modules\User\src\Models\User;

class NotifyInactiveTeachers extends Command
{
    protected $signature = 'teachers:notify-inactive';

    protected $description = 'Send inactivity reminders to teachers and alerts to admins.';

    public function handle(): int
    {
        $teacherReminders = $this->notifyTeachers();
        $adminAlerts = $this->notifyAdmins();

        $this->info('Teacher reminders sent: ' . $teacherReminders);
        $this->info('Admin alerts sent: ' . $adminAlerts);

        return self::SUCCESS;
    }

    protected function notifyTeachers(): int
    {
        $threshold = now()->subDays(30);
        $count = 0;

        Teacher::query()
            ->with(['student', 'application.package'])
            ->where('status', 'active')
            ->whereNotNull('student_id')
            ->whereNull('inactive_teacher_notified_at')
            ->where(function ($query) use ($threshold) {
                $query->where('last_active_at', '<', $threshold)
                    ->orWhere(function ($subQuery) use ($threshold) {
                        $subQuery->whereNull('last_active_at')
                            ->where('created_at', '<', $threshold);
                    });
            })
            ->chunkById(100, function ($teachers) use (&$count) {
                foreach ($teachers as $teacher) {
                    if (!$teacher->student) {
                        continue;
                    }

                    $inactiveDays = $this->resolveInactiveDays($teacher);
                    $teacher->student->notify(new TeacherInactiveReminderNotification($teacher, $inactiveDays));
                    $teacher->forceFill([
                        'inactive_teacher_notified_at' => now(),
                    ])->save();

                    $count++;
                }
            });

        return $count;
    }

    protected function notifyAdmins(): int
    {
        $threshold = now()->subDays(60);
        $count = 0;

        $admins = User::query()
            ->with('group.permissions')
            ->adminPanelUsers()
            ->get()
            ->filter(fn(User $user) => !$user->isLocked() && $user->hasPermission('teachers.view'));

        if ($admins->isEmpty()) {
            return 0;
        }

        Teacher::query()
            ->with(['application.package'])
            ->where('status', 'active')
            ->whereNull('inactive_admin_notified_at')
            ->where(function ($query) use ($threshold) {
                $query->where('last_active_at', '<', $threshold)
                    ->orWhere(function ($subQuery) use ($threshold) {
                        $subQuery->whereNull('last_active_at')
                            ->where('created_at', '<', $threshold);
                    });
            })
            ->chunkById(100, function ($teachers) use ($admins, &$count) {
                foreach ($teachers as $teacher) {
                    $inactiveDays = $this->resolveInactiveDays($teacher);

                    foreach ($admins as $admin) {
                        $admin->notify(new AdminInactiveTeacherAlertNotification($teacher, $inactiveDays));
                    }

                    $teacher->forceFill([
                        'inactive_admin_notified_at' => now(),
                    ])->save();

                    $count++;
                }
            });

        return $count;
    }

    protected function resolveInactiveDays(Teacher $teacher): int
    {
        $reference = $teacher->last_active_at instanceof CarbonInterface
            ? $teacher->last_active_at
            : $teacher->created_at;

        return max(1, (int) $reference->diffInDays(now()));
    }
}
