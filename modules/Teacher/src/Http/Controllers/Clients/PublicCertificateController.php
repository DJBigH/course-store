<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Modules\Teacher\src\Models\TeacherCourseCertificate;

class PublicCertificateController extends Controller
{
    /**
     * Verify a certificate by its unique code.
     * Anyone can access this without logging in.
     */
    public function verify(string $locale, string $code)
    {
        $certificate = TeacherCourseCertificate::query()
            ->with(['teacher', 'student', 'course'])
            ->where('code', $code)
            ->first();

        // 1. Check if certificate exists
        if (!$certificate) {
            return view('teacher::clients.certificates.verify', [
                'success' => false,
                'message' => __('courses::teacher/messages.package_features.certificates.verify.not_found'),
                'code' => $code
            ]);
        }

        // 2. Check if the teacher's package allows public verification
        $teacher = $certificate->teacher;
        if (!$teacher || !$teacher->packageHasFeature('can_verify_certificates')) {
            return view('teacher::clients.certificates.verify', [
                'success' => false,
                'message' => __('courses::teacher/messages.package_features.certificates.verify.feature_locked'),
                'certificate' => $certificate,
                'code' => $code
            ]);
        }

        // 3. Check if certificate is revoked
        if (!empty($certificate->revoked_at)) {
            return view('teacher::clients.certificates.verify', [
                'success' => false,
                'is_revoked' => true,
                'message' => __('courses::teacher/messages.package_features.certificates.verify.revoked'),
                'certificate' => $certificate,
                'code' => $code
            ]);
        }

        // 4. Success: Certificate is authentic
        return view('teacher::clients.certificates.verify', [
            'success' => true,
            'certificate' => $certificate,
            'code' => $code
        ]);
    }
}
