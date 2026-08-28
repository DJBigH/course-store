<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SidebarServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        View::composer('part.backend.sidebar', function ($view) {
            // Danh sách các route và key tương ứng để theo dõi "Đã xem"
            $trackRoutes = [
                'admin/students*' => 'last_viewed_students_at',
                'admin/orders*' => 'last_viewed_orders_at',
                'admin/contacts' => 'last_viewed_contacts_at',
                'admin/contacts/support*' => 'last_viewed_supports_at',
                'admin/courses/comments*' => 'last_viewed_comments_at',
                'admin/teacher-applications*' => 'last_viewed_teacher_apps_at',
                'admin/teacher-finance/payouts*' => 'last_viewed_payouts_at',
                'admin/teacher-finance/cancellations*' => 'last_viewed_cancellations_at',
            ];

            // Nếu đang ở trang đó, cập nhật thời điểm xem vào Session
            foreach ($trackRoutes as $pattern => $sessionKey) {
                if (request()->is($pattern)) {
                    session([$sessionKey => now()->toDateTimeString()]);
                }
            }

            // Lấy thời điểm xem từ Session (mặc định là 24h trước nếu chưa bao giờ xem)
            $lastViewed = [];
            foreach ($trackRoutes as $sessionKey) {
                $lastViewed[$sessionKey] = session($sessionKey, now()->subDay()->toDateTimeString());
            }

            $counts = [
                // Students: New since last view
                'new_students' => \Modules\Students\src\Models\Student::where('created_at', '>', $lastViewed['last_viewed_students_at'])->count(),

                // Orders: New since last view
                'new_orders' => \Modules\Orders\src\Models\Order::where('created_at', '>', $lastViewed['last_viewed_orders_at'])->count(),

                // Contacts: New since last view
                'new_contacts' => \Modules\Contacts\src\Models\Contacts::where('submission_type', 'contact')
                    ->where('workflow_status', 'new')
                    ->where('created_at', '>', $lastViewed['last_viewed_contacts_at'])
                    ->count(),

                // Support (Feedback & Reports): New since last view
                'new_supports' => \Modules\Contacts\src\Models\Contacts::whereIn('submission_type', ['feedback', 'report'])
                    ->where('workflow_status', 'new')
                    ->where('created_at', '>', $lastViewed['last_viewed_supports_at'])
                    ->count(),

                // Comments: Pending (Always show count, but can filter by last view if needed)
                'pending_comments' => \Modules\Courses\src\Models\CourseComment::where('is_visible', false)->count(),

                // Teacher Applications: Pending
                'teacher_applications' => \Modules\Teacher\src\Models\TeacherApplication::where('status', 'pending_review')->count(),

                // Payouts: Pending
                'payout_requests' => \Modules\Finances\src\Models\PayoutRequest::where('status', 'pending')->count(),
                
                // Payout Account Change Requests: Pending
                'payout_account_changes' => \Modules\Finances\src\Models\PayoutAccountChangeRequest::where('status', 'pending')->count(),
                
                // Teacher Cancellations: New since last view
                'teacher_cancellations' => \Modules\Teacher\src\Models\TeacherCancellationRequest::where('status', 'pending')
                    ->where('created_at', '>', $lastViewed['last_viewed_cancellations_at'])
                    ->count(),
            ];

            $view->with('sidebarCounts', $counts);
        });
    }
}
