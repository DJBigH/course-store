<?php

namespace Modules\Contacts\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Notifications\NewContactNotification;
use App\Support\ClientMailThrottle;
use Illuminate\Support\Facades\Auth;
use Modules\Contacts\src\Http\Requests\ContactRequest;
use Modules\Contacts\src\Models\Contacts;
use Modules\Contacts\src\Repositories\ContactsRepositoryInterface;
use Modules\User\src\Models\User;

class ContactController extends Controller
{
    protected $contactrepository;

    public function __construct(
        ContactsRepositoryInterface $contactsRepository,
        protected ClientMailThrottle $mailThrottle
    ) {
        $this->contactrepository = $contactsRepository;
    }

    public function index()
    {
        $pageTitle = __('contacts::clients/common.pageTitle');
        $pageName = __('contacts::clients/common.pageTitle');
        $studentData = Auth::guard('students')->user();

        return view('contacts::clients.index', compact(
            'pageName',
            'pageTitle',
            'studentData',
        ));
    }

    public function support()
    {
        $pageTitle = __('contacts::clients/common.support_page_title');
        $pageName = __('contacts::clients/common.support_page_title');
        $studentData = Auth::guard('students')->user();
        $submissionTypes = Contacts::submissionTypes();
        $categories = Contacts::categories();

        return view('contacts::clients.support', compact(
            'pageName',
            'pageTitle',
            'studentData',
            'submissionTypes',
            'categories'
        ));
    }

    public function store(ContactRequest $request)
    {
        $throttle = config('mail.throttle.contact_form');
        $throttleKey = $this->mailThrottle->key('contact-form', [
            $request->ip(),
            $request->input('email'),
        ]);

        if ($this->mailThrottle->tooManyAttempts($throttleKey, (int) $throttle['max_attempts'])) {
            $message = __('contacts::clients/messages.throttled', [
                'seconds' => $this->mailThrottle->availableIn($throttleKey),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'errors' => [
                        'g-recaptcha-response' => [$message],
                    ],
                ], 429);
            }

            return back()->with('msg_danger', $message);
        }

        $this->mailThrottle->hit($throttleKey, (int) $throttle['decay_seconds']);

        $student = Auth::guard('students')->user();
        $teacher = $student?->teacher;

        $contacts = $request->except(['_token', 'g-recaptcha-response']);
        $contacts['workflow_status'] = Contacts::STATUS_NEW;
        $contacts['status'] = 0;
        $contacts['source'] = $teacher ? 'teacher_portal' : ($student ? 'student_portal' : 'public');
        $contacts['student_id'] = $student?->id;
        $contacts['teacher_id'] = $teacher?->id;
        $contacts = $this->contactrepository->create($contacts);

        $admins = User::query()->inGroup('super_admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(new NewContactNotification($contacts));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $request->routeIs('contacts.post-contacts')
                    ? __('contacts::clients/messages.success.contact')
                    : __('contacts::clients/messages.success.request'),
            ]);
        }

        return back()->with('msg', $request->routeIs('contacts.post-contacts')
            ? __('contacts::clients/messages.success.contact')
            : __('contacts::clients/messages.success.request'));
    }
}
