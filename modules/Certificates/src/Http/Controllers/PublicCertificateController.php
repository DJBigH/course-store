<?php

namespace Modules\Certificates\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Certificates\src\Models\Certificate;

class PublicCertificateController extends Controller
{
    /**
     * Verify a certificate by its unique code.
     * Anyone can access this without logging in.
     */
    public function verify(string $locale, string $code)
    {
        $certificate = Certificate::query()
            ->with(['teacher', 'student', 'course'])
            ->where('code', $code)
            ->first();

        // 1. Check if certificate exists
        if (!$certificate) {
            return view('certificates::public.verification', [
                'success' => false,
                'message' => __('certificates::teacher/messages.package_features.certificates.verify.not_found'),
                'code' => $code
            ]);
        }

        // 2. Check if the teacher's package allows public verification
        $teacher = $certificate->teacher;
        if (!$teacher || !$teacher->packageHasFeature('can_verify_certificates')) {
            return view('certificates::public.verification', [
                'success' => false,
                'message' => __('certificates::teacher/messages.package_features.certificates.verify.feature_locked'),
                'certificate' => $certificate,
                'code' => $code
            ]);
        }

        // 3. Check if certificate is revoked
        if (!empty($certificate->revoked_at)) {
            return view('certificates::public.verification', [
                'success' => false,
                'is_revoked' => true,
                'message' => __('certificates::teacher/messages.package_features.certificates.verify.revoked'),
                'certificate' => $certificate,
                'code' => $code
            ]);
        }

        // 4. Success: Certificate is authentic
        return view('certificates::public.verification', [
            'success' => true,
            'certificate' => $certificate,
            'code' => $code
        ]);
    }
}
