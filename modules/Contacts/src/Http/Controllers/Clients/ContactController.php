<?php

namespace Modules\Contacts\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Notifications\NewContactNotification;
use App\Support\ClientMailThrottle;
use Illuminate\Support\Facades\Auth;
use Modules\Contacts\src\Http\Requests\ContactRequest;
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
        return view('contacts::clients.index', compact('pageName', 'pageTitle', 'studentData'));
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

        $contacts = $request->except(['_token', 'g-recaptcha-response']);
        $contacts = $this->contactrepository->create($contacts);
        $admins = User::where('group_id', 1)->get();

        foreach ($admins as $admin) {
            $admin->notify(new NewContactNotification($contacts));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('contacts::clients/messages.success.request'),
            ]);
        }

        return back()->with('msg', __('contacts::clients/messages.success.request'));
    }
}
