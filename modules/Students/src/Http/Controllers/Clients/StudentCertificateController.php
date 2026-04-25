<?php

namespace Modules\Students\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Certificates\src\Models\Certificate;

class StudentCertificateController extends Controller
{
    public function index(string $locale)
    {
        $student = Auth::guard('students')->user();
        $pageTitle = 'Chung chi cua toi';
        $pageName = $pageTitle;

        $certificates = Certificate::query()
            ->with(['teacher', 'course'])
            ->where('student_id', $student->id)
            ->whereNull('revoked_at')
            ->latest('issued_at')
            ->paginate(12);

        return view('students::clients.certificates.index', compact(
            'pageTitle',
            'pageName',
            'certificates'
        ));
    }

    public function show(string $locale, $id)
    {
        $student = Auth::guard('students')->user();
        $certificateId = (int) $id;

        if ($certificateId < 1) {
            abort(404);
        }

        $certificate = Certificate::query()
            ->with(['teacher', 'student', 'course'])
            ->where('student_id', $student->id)
            ->whereNull('revoked_at')
            ->findOrFail($certificateId);

        return view('certificates::pdf.completion', [
            'certificate' => $certificate,
            'viewerMode' => 'student',
            'backUrl' => route('students.account.certificates.index', ['locale' => app()->getLocale()]),
            'backLabel' => 'Quay lai danh sach chung chi',
            'autoPrint' => request()->boolean('print'),
        ]);
    }
}
