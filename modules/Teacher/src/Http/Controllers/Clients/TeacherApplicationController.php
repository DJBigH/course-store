<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Mail\TeacherApplicationReceivedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Modules\Students\src\Models\Coupons;
use Modules\Students\src\Models\Student;
use Modules\Teacher\src\Http\Requests\ClientTeacherApplicationRequest;
use Modules\Teacher\src\Models\TeacherApplication;
use Modules\Teacher\src\Models\TeacherPackage;

class TeacherApplicationController extends Controller
{
    public function begin(Request $request, string $locale)
    {
        $request->session()->put('teacher_application_entry_allowed', true);

        return redirect()->route('teacher.account.apply', ['locale' => $locale]);
    }

    public function create(Request $request)
    {
        $student = auth('students')->user();
        $application = $this->resolveCurrentApplication($request);
        $couponPreview = $this->resolveCouponPreview($request, $application);

        if ($application && in_array($application->status, ['pending_payment', 'pending_review', 'approved'], true)) {
            return redirect()->route('teacher.account.status', ['locale' => app()->getLocale()]);
        }

        $pageTitle = __('teacher::portal.titles.apply');
        $pageName = $pageTitle;
        $packages = $this->resolvePublicPackages($application?->package_id);

        return view('teacher::clients.application_form', compact('pageTitle', 'pageName', 'packages', 'application', 'student', 'couponPreview'));
    }

    public function store(ClientTeacherApplicationRequest $request)
    {
        $student = auth('students')->user();
        if (!$student && Student::query()->where('email', $request->string('email')->toString())->exists()) {
            return redirect()->route('teacher.auth.login', ['locale' => app()->getLocale()])
                ->with('msg_danger', __('teacher::portal.flash.email_exists'));
        }

        $package = TeacherPackage::query()->selectable()->findOrFail($request->integer('package_id'));
        $application = $this->resolveWritableApplication($request, $student);

        if ($application->exists && $application->status === 'approved') {
            return redirect()->route('teacher.account.status', ['locale' => app()->getLocale()])
                ->with('msg_danger', __('teacher::portal.flash.approved'));
        }

        if ($application->exists && $application->status === 'pending_review') {
            return redirect()->route('teacher.account.status', ['locale' => app()->getLocale()])
                ->with('msg_danger', __('teacher::portal.flash.pending_review'));
        }

        $couponData = $this->resolveCouponData(
            trim((string) $request->input('coupon_code')),
            $package,
            $student?->id
        );

        $application->fill([
            'package_id' => $package->id,
            'status' => (float) $package->price > 0 ? 'pending_payment' : 'pending_review',
            'full_name' => $request->string('full_name')->toString(),
            'display_name' => $request->string('display_name')->toString() ?: null,
            'headline' => $request->string('headline')->toString() ?: null,
            'bio' => $request->string('bio')->toString() ?: null,
            'experience_years' => $request->filled('experience_years') ? $request->integer('experience_years') : null,
            'specialties' => $this->parseSpecialties($request->string('specialties')->toString()),
            'phone' => $request->string('phone')->toString() ?: null,
            'email' => $request->string('email')->toString(),
            'locale' => $this->normalizeLocale(app()->getLocale()),
            'portfolio_url' => $request->string('portfolio_url')->toString() ?: null,
            'facebook_url' => $request->string('facebook_url')->toString() ?: null,
            'youtube_url' => $request->string('youtube_url')->toString() ?: null,
            'linkedin_url' => $request->string('linkedin_url')->toString() ?: null,
            'intro_video_url' => $request->string('intro_video_url')->toString() ?: null,
            'cv_file' => $request->string('cv_file')->toString() ?: null,
            'identity_file' => $request->string('identity_file')->toString() ?: null,
            'student_id' => $student?->id,
            'applicant_type' => $student ? 'student' : 'guest',
            'payment_method' => $request->string('payment_method')->toString() ?: null,
            'coupon_code' => $couponData['coupon_code'],
            'discount_amount' => $couponData['discount_amount'],
            'submitted_at' => now(),
            'reviewed_at' => null,
            'reviewed_by' => null,
            'admin_note' => null,
        ]);
        $application->save();
        $this->forgetCouponPreview($request);

        if ($student && $student->preferred_locale !== $application->locale) {
            $student->forceFill(['preferred_locale' => $application->locale])->save();
        }

        if (!$student) {
            $request->session()->put('teacher_guest_application_id', $application->id);
            $request->session()->put('teacher_guest_application_email', $application->email);
        }

        if ($student) {
            activity_log(
                action: 'teacher_application_submitted',
                subject: $student,
                properties: [
                    'application_id' => $application->id,
                    'package' => $package->name,
                    'status' => $application->status,
                ],
                logName: 'Dang ky giang vien',
                description: 'Hoc vien gui ho so dang ky giang vien'
            );
        }

        Mail::to($application->email)
            ->locale(app()->getLocale())
            ->queue(new TeacherApplicationReceivedMail($application, app()->getLocale()));

        return redirect()->route('teacher.account.status', ['locale' => app()->getLocale()])
            ->with('msg_success', __('teacher::portal.flash.submitted'));
    }

    public function status(Request $request)
    {
        $student = auth('students')->user();
        $application = $this->resolveCurrentApplication($request, true);

        if (!$application) {
            return redirect()->route('teacher.portal.index', ['locale' => app()->getLocale()]);
        }

        $pageTitle = __('teacher::portal.titles.status');
        $pageName = $pageTitle;

        return view('teacher::clients.application_status', compact('pageTitle', 'pageName', 'application', 'student'));
    }

    public function previewCoupon(Request $request)
    {
        $package = TeacherPackage::query()->selectable()->find($request->integer('package_id'));

        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => __('teacher::portal.flash.invalid_package'),
            ], 422);
        }

        try {
            $couponData = $this->resolveCouponData(
                trim((string) $request->input('coupon_code')),
                $package,
                auth('students')->id()
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => collect($exception->errors())->flatten()->first() ?: __('teacher::portal.flash.invalid_coupon'),
            ], 422);
        }

        $basePrice = (float) $package->price;
        $payableAmount = max($basePrice - (float) $couponData['discount_amount'], 0);
        $expiresAt = now()->addMinutes(30)->toIso8601String();

        $request->session()->put('teacher_coupon_preview', [
            'package_id' => $package->id,
            'coupon_code' => $couponData['coupon_code'],
            'discount_amount' => (float) $couponData['discount_amount'],
            'base_price' => $basePrice,
            'payable_amount' => $payableAmount,
            'expires_at' => $expiresAt,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'coupon_code' => $couponData['coupon_code'],
                'discount_amount' => (float) $couponData['discount_amount'],
                'base_price' => $basePrice,
                'payable_amount' => $payableAmount,
                'expires_at' => $expiresAt,
            ],
        ]);
    }

    public function clearCouponPreview(Request $request)
    {
        $this->forgetCouponPreview($request);

        return response()->json([
            'success' => true,
        ]);
    }

    public function edit(Request $request)
    {
        $student = auth('students')->user();
        $application = $this->resolveCurrentApplication($request, true);

        if (!$application) {
            return redirect()->route('teacher.portal.index', ['locale' => app()->getLocale()]);
        }

        if (!in_array($application->status, ['draft', 'rejected', 'pending_payment'], true)) {
            return redirect()->route('teacher.account.status', ['locale' => app()->getLocale()]);
        }

        $pageTitle = __('teacher::portal.titles.edit');
        $pageName = $pageTitle;
        $packages = $this->resolvePublicPackages($application?->package_id);
        $couponPreview = $this->resolveCouponPreview($request, $application);

        return view('teacher::clients.application_form', compact('pageTitle', 'pageName', 'packages', 'application', 'student', 'couponPreview'));
    }

    public function update(ClientTeacherApplicationRequest $request)
    {
        return $this->store($request);
    }

    public function markPaid(Request $request)
    {
        $application = $this->resolveCurrentApplication($request, true);

        if (!$application) {
            return redirect()->route('teacher.portal.index', ['locale' => app()->getLocale()]);
        }

        if ($application->status !== 'pending_payment') {
            return back()->with('msg_danger', __('teacher::portal.flash.invalid_payment_status'));
        }

        $application->update([
            'status' => 'pending_review',
            'submitted_at' => now(),
        ]);

        return back()->with('msg_success', __('teacher::portal.flash.paid_marked'));
    }

    private function resolveCurrentApplication(Request $request, bool $withRelations = false): ?TeacherApplication
    {
        $student = auth('students')->user();
        $query = TeacherApplication::query();

        if ($withRelations) {
            $query->with(['package', 'teacher', 'reviewer', 'student']);
        }

        if ($student) {
            return $query->where('student_id', $student->id)->latest('id')->first();
        }

        $applicationId = (int) $request->session()->get('teacher_guest_application_id', 0);
        $email = (string) $request->session()->get('teacher_guest_application_email', '');

        if ($applicationId > 0) {
            $application = (clone $query)->where('id', $applicationId)->latest('id')->first();
            if ($application) {
                return $application;
            }
        }

        if ($email !== '') {
            return $query
                ->whereNull('student_id')
                ->where('email', $email)
                ->latest('id')
                ->first();
        }

        return null;
    }

    private function resolvePublicPackages(?int $selectedPackageId = null)
    {
        $packages = TeacherPackage::query()->visibleForListing()->get();

        if ($selectedPackageId && !$packages->contains('id', $selectedPackageId)) {
            $selectedPackage = TeacherPackage::query()
                ->selectable()
                ->find($selectedPackageId);

            if ($selectedPackage) {
                $packages->push($selectedPackage);
                $packages = $packages->sortBy('sort_order')->values();
            }
        }

        return $packages;
    }

    private function resolveWritableApplication(Request $request, ?object $student): TeacherApplication
    {
        if ($student) {
            return TeacherApplication::query()->firstOrNew(['student_id' => $student->id]);
        }

        return TeacherApplication::query()->firstOrNew([
            'student_id' => null,
            'email' => $request->string('email')->toString(),
        ]);
    }

    private function parseSpecialties(?string $raw): array
    {
        return collect(explode(',', (string) $raw))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    private function resolveCouponData(?string $couponCode, TeacherPackage $package, ?int $studentId): array
    {
        $couponCode = strtoupper(trim((string) $couponCode));
        if ($couponCode === '' || (float) $package->price <= 0) {
            return [
                'coupon_code' => null,
                'discount_amount' => 0,
            ];
        }

        $coupon = Coupons::query()
            ->active()
            ->visibleForStudent($studentId)
            ->where('code', $couponCode)
            ->first();

        if (!$coupon) {
            throw ValidationException::withMessages([
                'coupon_code' => __('teacher::portal.flash.coupon_invalid_or_expired'),
            ]);
        }

        if ($coupon->students()->exists() && !$studentId) {
            throw ValidationException::withMessages([
                'coupon_code' => __('teacher::portal.flash.coupon_login_required'),
            ]);
        }

        if ($coupon->courses()->exists()) {
            throw ValidationException::withMessages([
                'coupon_code' => __('teacher::portal.flash.coupon_courses_only'),
            ]);
        }

        $basePrice = (float) $package->price;

        if ($coupon->total_condition && $basePrice < (float) $coupon->total_condition) {
            throw ValidationException::withMessages([
                'coupon_code' => __('teacher::portal.flash.coupon_total_condition'),
            ]);
        }

        $discount = $coupon->discount_type === 'percent'
            ? ($basePrice * (float) $coupon->discount_value) / 100
            : (float) $coupon->discount_value;

        return [
            'coupon_code' => $coupon->code,
            'discount_amount' => min($discount, $basePrice),
        ];
    }

    private function resolveCouponPreview(Request $request, ?TeacherApplication $application): ?array
    {
        if ($application && $application->coupon_code) {
            return null;
        }

        $preview = $request->session()->get('teacher_coupon_preview');
        if (!is_array($preview)) {
            return null;
        }

        $packageId = (int) ($preview['package_id'] ?? 0);
        if ($packageId <= 0) {
            $this->forgetCouponPreview($request);
            return null;
        }

        if (!empty($preview['expires_at']) && now()->greaterThan($preview['expires_at'])) {
            $this->forgetCouponPreview($request);
            return null;
        }

        return $preview;
    }

    private function forgetCouponPreview(Request $request): void
    {
        $request->session()->forget('teacher_coupon_preview');
    }

    private function normalizeLocale(?string $locale): string
    {
        return in_array($locale, ['vi', 'en', 'ko', 'ja', 'zh'], true)
            ? $locale
            : config('app.locale', 'vi');
    }
}
