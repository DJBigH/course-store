<?php

namespace Modules\Teacher\src\Support;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Modules\Courses\src\Models\Courses;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Models\TeacherAffiliateLink;
use Modules\Teacher\src\Models\TeacherAffiliateLinkClick;
use Modules\Teacher\src\Models\TeacherCourseBundle;

class TeacherAffiliateLinkManager
{
    public function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (TeacherAffiliateLink::query()->where('code', $code)->exists());

        return $code;
    }

    public function resolveTargetUrl(TeacherAffiliateLink $link, ?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return match ($link->target_type) {
            'course' => $this->resolveCourseUrl($link, $locale),
            'bundle' => $this->resolveBundleUrl($link, $locale),
            default => $this->resolveLandingUrl($link, $locale),
        };
    }

    public function buildPublicUrl(TeacherAffiliateLink $link, ?string $locale = null): string
    {
        $baseUrl = $this->resolveTargetUrl($link, $locale);
        $separator = str_contains($baseUrl, '?') ? '&' : '?';

        return $baseUrl . $separator . 'ref=' . urlencode($link->code);
    }

    public function captureClick(Request $request, Teacher $teacher, string $targetType, ?int $targetId, string $targetUrl): void
    {
        $code = trim((string) $request->query('ref', ''));
        if ($code === '') {
            return;
        }

        $link = TeacherAffiliateLink::query()
            ->where('teacher_id', $teacher->id)
            ->where('code', $code)
            ->where('status', true)
            ->first();

        if (!$link) {
            return;
        }

        if ($link->target_type !== $targetType) {
            return;
        }

        if ($targetType !== 'landing' && (int) $link->target_id !== (int) $targetId) {
            return;
        }

        $sessionKey = 'teacher_affiliate_clicks.' . $link->id . '.' . $targetType . '.' . ((int) $targetId);
        if ($request->session()->has($sessionKey)) {
            return;
        }

        TeacherAffiliateLinkClick::query()->create([
            'affiliate_link_id' => $link->id,
            'teacher_id' => $teacher->id,
            'student_id' => auth('students')->id(),
            'target_type' => $targetType,
            'target_id' => $targetId,
            'locale' => app()->getLocale(),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 65535, ''),
            'referer_url' => Str::limit((string) $request->headers->get('referer'), 500, ''),
            'target_url' => Str::limit($targetUrl, 500, ''),
            'clicked_at' => now(),
        ]);

        $link->forceFill([
            'clicks_count' => (int) $link->clicks_count + 1,
            'last_clicked_at' => now(),
        ])->save();

        $request->session()->put($sessionKey, now()->timestamp);
        $request->session()->put('teacher_affiliate.active_code', $link->code);
    }

    public function ensurePublicAccessAllowed(Request $request, Teacher $teacher, string $targetType, ?int $targetId): ?Response
    {
        $code = trim((string) $request->query('ref', ''));
        if ($code === '') {
            return null;
        }

        $link = TeacherAffiliateLink::query()
            ->where('teacher_id', $teacher->id)
            ->where('code', $code)
            ->first();

        if (!$link) {
            return null;
        }

        if ($link->target_type !== $targetType) {
            abort(404);
        }

        if ($targetType !== 'landing' && (int) $link->target_id !== (int) $targetId) {
            abort(404);
        }

        if (!$link->status) {
            return response()->view('errors.clients.affiliate_link_expired', [], 410);
        }

        return null;
    }

    public function resolveTrackedLink(Request $request, Teacher $teacher, string $targetType, ?int $targetId): ?TeacherAffiliateLink
    {
        $code = trim((string) ($request->query('ref') ?: $request->session()->get('teacher_affiliate.active_code', '')));
        if ($code === '') {
            return null;
        }

        $link = TeacherAffiliateLink::query()
            ->where('teacher_id', $teacher->id)
            ->where('code', $code)
            ->where('status', true)
            ->first();

        if (!$link) {
            return null;
        }

        if ($link->target_type !== $targetType) {
            return null;
        }

        if ($targetType !== 'landing' && (int) $link->target_id !== (int) $targetId) {
            return null;
        }

        return $link;
    }

    private function resolveCourseUrl(TeacherAffiliateLink $link, string $locale): string
    {
        $course = $link->relationLoaded('course') ? $link->course : $link->course()->first();
        abort_unless($course, 404);

        return route('courses.detail', [
            'locale' => $locale,
            'slug' => $course->slug_locale ?: $course->slug,
        ]);
    }

    private function resolveBundleUrl(TeacherAffiliateLink $link, string $locale): string
    {
        $bundle = $link->relationLoaded('bundle') ? $link->bundle : $link->bundle()->first();
        abort_unless($bundle, 404);

        return route('courses.bundle.detail', [
            'locale' => $locale,
            'slug' => $bundle->slug,
        ]);
    }

    private function resolveLandingUrl(TeacherAffiliateLink $link, string $locale): string
    {
        $teacher = $link->relationLoaded('teacher') ? $link->teacher : $link->teacher()->first();
        abort_unless($teacher, 404);

        return route('teacher.public.show', [
            'locale' => $locale,
            'slug' => $teacher->slug_locale ?: $teacher->slug,
        ]);
    }
}
