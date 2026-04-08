<?php

namespace Modules\Teacher\src\Support;

use Illuminate\Support\Carbon;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Models\TeacherApplication;
use Modules\Teacher\src\Models\TeacherPackage;

class TeacherPackageLifecycleManager
{
    public function sync(Teacher $teacher): Teacher
    {
        $teacher->loadMissing(['application.package', 'student']);

        $currentPackage = $teacher->application?->package;
        if (!$teacher->package_expires_at || !$this->isRecurring($currentPackage)) {
            return $teacher;
        }

        $expiresAt = $teacher->package_expires_at->copy();
        if ($expiresAt->isFuture()) {
            return $teacher;
        }

        $queuedApplication = TeacherApplication::query()
            ->with(['package'])
            ->where('teacher_id', $teacher->id)
            ->where('id', '!=', (int) $teacher->application_id)
            ->where('status', 'approved')
            ->whereNotNull('activates_at')
            ->whereNull('activated_at')
            ->where('activates_at', '<=', now())
            ->orderBy('activates_at')
            ->orderBy('id')
            ->first();

        if ($queuedApplication) {
            $this->activateApplication(
                $teacher,
                $queuedApplication,
                $queuedApplication->package_started_at ?: $queuedApplication->activates_at ?: now(),
                $queuedApplication->package_expires_at
            );

            return $teacher->fresh(['application.package', 'student']);
        }

        $fallbackPackage = $this->resolveFallbackPackage($currentPackage);
        if (!$fallbackPackage) {
            return $teacher->fresh(['application.package', 'student']);
        }

        $sourceApplication = $teacher->application;
        $fallbackApplication = TeacherApplication::query()->create([
            'student_id' => $teacher->student_id,
            'teacher_id' => $teacher->id,
            'applicant_type' => $sourceApplication?->applicant_type ?? 'student',
            'package_id' => $fallbackPackage->id,
            'payment_method' => null,
            'coupon_code' => null,
            'discount_amount' => 0,
            'status' => 'approved',
            'full_name' => $sourceApplication?->full_name ?: $teacher->name,
            'display_name' => $sourceApplication?->display_name ?: $teacher->name,
            'headline' => $sourceApplication?->headline,
            'bio' => $sourceApplication?->bio ?: $teacher->description,
            'experience_years' => $sourceApplication?->experience_years ?: $teacher->exp,
            'specialties' => $sourceApplication?->specialties ?? [],
            'phone' => $sourceApplication?->phone ?: $teacher->student?->phone,
            'email' => $sourceApplication?->email ?: $teacher->student?->email,
            'locale' => $sourceApplication?->locale ?: app()->getLocale(),
            'portfolio_url' => $sourceApplication?->portfolio_url,
            'facebook_url' => $sourceApplication?->facebook_url,
            'youtube_url' => $sourceApplication?->youtube_url,
            'linkedin_url' => $sourceApplication?->linkedin_url,
            'intro_video_url' => $sourceApplication?->intro_video_url,
            'cv_file' => $sourceApplication?->cv_file,
            'identity_file' => $sourceApplication?->identity_file,
            'submitted_at' => now(),
            'reviewed_at' => now(),
            'activates_at' => now(),
            'package_started_at' => now(),
            'package_expires_at' => null,
            'activated_at' => now(),
            'reviewed_by' => null,
            'admin_note' => 'auto_expired_fallback',
        ]);

        $this->activateApplication($teacher, $fallbackApplication, now(), null);

        return $teacher->fresh(['application.package', 'student']);
    }

    public function applyApprovedChange(Teacher $teacher, TeacherApplication $application): string
    {
        $teacher->loadMissing(['application.package', 'student']);
        $application->loadMissing(['package']);

        $currentPackage = $teacher->application?->package;
        $targetPackage = $application->package;
        $now = now();

        if (!$currentPackage || !$targetPackage) {
            $this->activateApplication($teacher, $application, $now, $this->calculateExpiresAt($targetPackage, $now));
            return 'activated';
        }

        $currentExpiresAt = $teacher->package_expires_at?->copy();

        if (
            $this->isRecurring($currentPackage) &&
            $currentExpiresAt &&
            $currentExpiresAt->isFuture()
        ) {
            if (
                (int) $currentPackage->id === (int) $targetPackage->id &&
                $currentPackage->billing_cycle === $targetPackage->billing_cycle
            ) {
                $newExpiresAt = $this->calculateExpiresAt($targetPackage, $currentExpiresAt->copy());

                $teacher->forceFill([
                    'package_expires_at' => $newExpiresAt,
                ])->save();

                $application->forceFill([
                    'status' => 'approved',
                    'activates_at' => $currentExpiresAt,
                    'package_started_at' => $currentExpiresAt,
                    'package_expires_at' => $newExpiresAt,
                    'activated_at' => now(),
                ])->save();

                return 'extended';
            }

            if ($this->comparePackageLevel($targetPackage, $currentPackage) >= 0) {
                $this->activateApplication($teacher, $application, $now, $this->calculateExpiresAt($targetPackage, $now));

                return 'activated';
            }

            $queuedStartAt = $currentExpiresAt->copy();
            $queuedExpiresAt = $this->calculateExpiresAt($targetPackage, $queuedStartAt->copy());

            $application->forceFill([
                'status' => 'approved',
                'activates_at' => $queuedStartAt,
                'package_started_at' => $queuedStartAt,
                'package_expires_at' => $queuedExpiresAt,
                'activated_at' => null,
            ])->save();

            return 'queued';
        }

        $this->activateApplication($teacher, $application, $now, $this->calculateExpiresAt($targetPackage, $now));
        return 'activated';
    }

    public function calculateExpiresAt(?TeacherPackage $package, Carbon $startAt): ?Carbon
    {
        if (!$package) {
            return null;
        }

        return match ($package->billing_cycle) {
            'monthly' => $startAt->copy()->addMonth(),
            'yearly' => $startAt->copy()->addYear(),
            default => null,
        };
    }

    public function isRecurring(?TeacherPackage $package): bool
    {
        return in_array($package?->billing_cycle, ['monthly', 'yearly'], true);
    }

    public function daysLeft(?Teacher $teacher): ?int
    {
        if (!$teacher?->package_expires_at) {
            return null;
        }

        return max(now()->startOfDay()->diffInDays($teacher->package_expires_at->copy()->startOfDay(), false), 0);
    }

    private function activateApplication(Teacher $teacher, TeacherApplication $application, Carbon $startAt, ?Carbon $expiresAt): void
    {
        $package = $application->package;

        $teacher->forceFill([
            'application_id' => $application->id,
            'commission_rate' => $package?->commission_rate ?? $teacher->commission_rate,
            'package_started_at' => $startAt,
            'package_expires_at' => $expiresAt,
        ])->save();

        $application->forceFill([
            'status' => 'approved',
            'activates_at' => $startAt,
            'package_started_at' => $startAt,
            'package_expires_at' => $expiresAt,
            'activated_at' => now(),
        ])->save();
    }

    private function resolveFallbackPackage(?TeacherPackage $currentPackage): ?TeacherPackage
    {
        return TeacherPackage::query()
            ->selectable()
            ->where('code', 'free')
            ->when($currentPackage, fn ($query) => $query->where('id', '!=', (int) $currentPackage->id))
            ->orderBy('sort_order')
            ->first();
    }

    private function comparePackageLevel(?TeacherPackage $targetPackage, ?TeacherPackage $currentPackage): int
    {
        $targetOrder = (int) ($targetPackage?->sort_order ?? 0);
        $currentOrder = (int) ($currentPackage?->sort_order ?? 0);

        return $targetOrder <=> $currentOrder;
    }
}
