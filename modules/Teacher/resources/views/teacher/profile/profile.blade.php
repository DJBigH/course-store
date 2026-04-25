@extends('layouts.teacher')

@section('content')
    @php
        $teacherLocale = session('locale', app()->getLocale());
        $teacherCanCustomizeLanding = $teacher?->packageHasFeature('can_customize_teacher_landing') ?? false;
        $teacherLandingUrl = $teacher
            ? route('teacher.public.show', ['locale' => $teacherLocale, 'slug' => $teacher->slug_locale ?: $teacher->slug])
            : route('teacher.dashboard.profile');
        $teacherLandingActionUrl = $teacherCanCustomizeLanding ? $teacherLandingUrl : route('teacher.dashboard.package.upgrade');
        $teacherLandingDisplayUrl = preg_replace('#^https?://#', '', $teacherLandingUrl);
        $avatarValue = old('image', $teacher?->image);
        $portfolioValue = old('portfolio_url', $application?->portfolio_url);
        $introVideoValue = old('intro_video_url', $application?->intro_video_url);
        $linkedinValue = old('linkedin_url', $application?->linkedin_url);
        $customLinksValue = old('custom_links', $application?->custom_links ?? []);
        $customLinksValue = collect(is_array($customLinksValue) ? $customLinksValue : [])
            ->map(fn ($row) => [
                'label' => trim((string) ($row['label'] ?? '')),
                'url' => trim((string) ($row['url'] ?? '')),
            ])
            ->values()
            ->all();

        if (empty($customLinksValue)) {
            $customLinksValue = [['label' => '', 'url' => '']];
        }
        $avatarFallback = strtoupper(mb_substr((string) ($application?->display_name ?: $student->name ?: 'T'), 0, 1));
        $extractLabel = static function (?string $value, string $fallback): string {
            $value = trim((string) $value);
            if ($value === '') {
                return $fallback;
            }

            $path = parse_url($value, PHP_URL_PATH) ?: $value;
            $label = basename((string) $path);

            return $label !== '' ? urldecode($label) : $fallback;
        };
        $displayStoredPath = static function (?string $value): string {
            $value = trim((string) $value);
            if ($value === '') {
                return '';
            }

            if (str_contains($value, '/storage/') || str_contains($value, 'storage/')) {
                $path = parse_url($value, PHP_URL_PATH) ?: $value;
                $label = basename((string) $path);

                return $label !== '' ? urldecode($label) : $value;
            }

            return $value;
        };
        $fieldLabel = static function (string $key): string {
            return __("courses::teacher/messages.profile.fields.$key");
        };
        $teacherBadge = $teacher?->primary_badge;
        $badgeIcon = match($teacherBadge['tone'] ?? 'slate') {
            'blue' => 'fa-circle-check',
            'gold' => 'fa-star',
            'emerald' => 'fa-arrow-trend-up',
            'violet' => 'fa-magic-wand-sparkles',
            'rose' => 'fa-gem',
            'slate' => 'fa-certificate',
            default => 'fa-certificate',
        };
        $profileErrorKeys = [
            'name', 'phone', 'address', 'image',
            'display_name', 'headline', 'experience_years', 'specialties', 'bio',
            'portfolio_url', 'linkedin_url', 'intro_video_url',
        ];
        $passwordErrorKeys = ['current_password', 'password', 'password_confirmation'];
        $securityErrorKeys = [];
        $activeProfileTab = 'profile';

        if (collect($passwordErrorKeys)->contains(fn ($key) => $errors->has($key))) {
            $activeProfileTab = 'password';
        } elseif (collect($securityErrorKeys)->contains(fn ($key) => $errors->has($key))) {
            $activeProfileTab = 'security';
        } elseif (collect($profileErrorKeys)->contains(fn ($key) => $errors->has($key)) || $errors->has('custom_links.*.url')) {
            $activeProfileTab = 'profile';
        }
    @endphp
    <div class="teacher-page-shell">
        <div class="teacher-panel teacher-profile-shell">
            <div class="teacher-section-title">
                <div>
                    <h3 class="fw-bold mb-2">{{ __('courses::teacher/messages.profile.title') }}</h3>
                    <p class="text-muted mb-0">{{ __('courses::teacher/messages.profile.description') }}</p>
                    {{-- @if ($teacherBadge)
                        <div class="teacher-profile-current-badge teacher-profile-current-badge--{{ $teacherBadge['tone'] }}">
                            {{ $teacherBadge['label'] }}
                        </div>
                    @endif --}}
                </div>
                <span class="teacher-status-badge">
                    {{ $student->two_factor_email_enabled ? __('courses::teacher/messages.profile.two_factor.enabled') : __('courses::teacher/messages.profile.two_factor.disabled') }}
                </span>
            </div>

            @if (session('msg_success') || session('msg') || session('msg_danger'))
                <div class="mb-3">
                    @if (session('msg_success') || session('msg'))
                        <div class="alert alert-success mb-2">{{ session('msg_success') ?? session('msg') }}</div>
                    @endif
                    @if (session('msg_danger'))
                        <div class="alert alert-danger mb-0">{{ session('msg_danger') }}</div>
                    @endif
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-3">{{ __('courses::teacher/messages.common.validation_summary') }}</div>
            @endif

            <div class="teacher-profile-tabs-scroll">
                <div class="teacher-profile-tabs" data-profile-tabs>
                    <button type="button"
                        class="teacher-profile-tab-button {{ $activeProfileTab === 'profile' ? 'is-active' : '' }}"
                        data-profile-tab="profile">
                        <i class="fas fa-id-card"></i>
                        <span>{{ __('courses::teacher/messages.profile.tabs.profile') }}</span>
                    </button>
                    <button type="button"
                        class="teacher-profile-tab-button {{ $activeProfileTab === 'password' ? 'is-active' : '' }}"
                        data-profile-tab="password">
                        <i class="fas fa-key"></i>
                        <span>{{ __('courses::teacher/messages.profile.tabs.password') }}</span>
                    </button>
                    <button type="button"
                        class="teacher-profile-tab-button {{ $activeProfileTab === 'security' ? 'is-active' : '' }}"
                        data-profile-tab="security">
                        <i class="fas fa-shield-halved"></i>
                        <span>{{ __('courses::teacher/messages.profile.tabs.security') }}</span>
                        <span class="teacher-profile-tab-badge {{ $student->two_factor_email_enabled ? 'is-enabled' : 'is-disabled' }}">
                            {{ $student->two_factor_email_enabled ? __('courses::teacher/messages.profile.two_factor.enabled_short') : __('courses::teacher/messages.profile.two_factor.disabled_short') }}
                        </span>
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('teacher.dashboard.profile.update') }}" class="teacher-profile-form" id="teacher-profile-form">
                @csrf

                <div class="teacher-profile-tab-panel {{ $activeProfileTab === 'profile' ? 'is-active' : '' }}" data-profile-panel="profile">
                    <div class="row g-4">
                        <div class="col-xl-12">
                            <div class="teacher-profile-card">
                            <h4>{{ __('courses::teacher/messages.profile.basic.title') }}</h4>
                            <p class="text-muted mb-4">{{ __('courses::teacher/messages.profile.basic.description') }}</p>

                            @if ($teacherBadge)
                                <div class="teacher-profile-badge-banner teacher-profile-badge-banner--{{ $teacherBadge['tone'] }}">
                                    <div class="teacher-profile-badge-banner__icon">
                                        <i class="fas {{ $badgeIcon }}"></i>
                                    </div>
                                    <strong>{{ __('courses::teacher/messages.profile.basic.badge_label') }}</strong>
                                    <span class="">{{ $teacherBadge['label'] }}</span>
                                </div>
                            @endif

                            <div class="teacher-profile-avatar mb-4">
                                <div class="teacher-profile-avatar__preview" data-avatar-preview>
                                    <img src="{{ $avatarValue }}" alt="{{ __('courses::teacher/messages.profile.fields.avatar') }}"
                                        @if (empty($avatarValue)) hidden @endif>
                                    <span @if (!empty($avatarValue)) hidden @endif>{{ $avatarFallback }}</span>
                                </div>
                                <div class="teacher-profile-avatar__content">
                                    <label class="form-label">{{ __('courses::teacher/messages.profile.fields.avatar') }} *</label>
                                    <div class="teacher-file-picker">
                                        <input type="hidden" id="teacher-profile-avatar" name="image"
                                            value="{{ $avatarValue }}">
                                        <input type="text" id="teacher-profile-avatar-display"
                                            class="form-control @error('image') is-invalid @enderror" readonly
                                            value="{{ $displayStoredPath($avatarValue) }}"
                                            placeholder="{{ __('courses::teacher/messages.profile.fields.avatar_placeholder') }}">
                                        <button type="button" class="btn btn-outline-secondary js-lfm"
                                            data-input="teacher-profile-avatar" data-preview-target="avatar"
                                            data-display-input="teacher-profile-avatar-display"
                                            data-type="image">
                                            {{ __('courses::teacher/messages.profile.actions.choose_image') }}
                                        </button>
                                        <button type="button" class="btn btn-outline-danger js-clear-input"
                                            data-input="teacher-profile-avatar" data-display-input="teacher-profile-avatar-display"
                                            data-preview-target="avatar" data-submit-section="account">
                                            {{ __('courses::teacher/messages.profile.actions.remove') }}
                                        </button>
                                    </div>
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('courses::teacher/messages.profile.fields.name') }} *</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $student->name) }}" maxlength="225" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('courses::teacher/messages.profile.fields.email') }}</label>
                                    <input type="email" class="form-control" value="{{ $student->email }}" readonly>
                                    <div class="form-text">{{ __('courses::teacher/messages.profile.basic.email_hint') }}</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('courses::teacher/messages.profile.fields.phone') }} *</label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                        value="{{ old('phone', $student->phone) }}" inputmode="tel" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('courses::teacher/messages.profile.fields.package') }}</label>
                                    <input type="text" class="form-control"
                                        value="{{ $teacher?->application?->package?->name_locale ?: $teacher?->application?->package?->name ?: __('courses::teacher/messages.profile.basic.no_package') }}"
                                        readonly>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">{{ __('courses::teacher/messages.profile.fields.address') }}</label>
                                    <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                                        value="{{ old('address', $student->address) }}" maxlength="255">
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="teacher-section-submit mt-4">
                                <button type="submit" name="profile_section" value="account" class="btn btn-primary" formnovalidate>
                                    {{ __('courses::teacher/messages.profile.actions.save_account') }}
                                </button>
                            </div>
                            </div>

                            <div class="teacher-profile-card teacher-profile-card--wide mt-4">
                                <div class="teacher-card-header">
                                <div>
                                    <h4>{{ __('courses::teacher/messages.profile.professional.title') }}</h4>
                                    <p class="text-muted mb-0">{{ __('courses::teacher/messages.profile.professional.description') }}</p>
                                    <div class="teacher-card-header__meta">
                                        <span class="teacher-card-header__meta-label">
                                            <i class="fas fa-globe"></i>
                                            {{ __('courses::teacher/messages.profile.professional.landing_page_label') }}
                                        </span>
                                        <a href="{{ $teacherLandingActionUrl }}" class="teacher-card-header__meta-link"
                                            target="_blank" rel="noopener">
                                            <span>{{ $teacherCanCustomizeLanding ? $teacherLandingDisplayUrl : __('courses::teacher/messages.profile.professional.landing_page_locked') }}</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="teacher-card-header__actions">
                                    <a href="{{ $teacherLandingActionUrl }}"
                                        class="btn teacher-card-header__button {{ $teacherCanCustomizeLanding ? 'btn-outline-secondary' : 'btn-outline-warning' }}"
                                        target="_blank" rel="noopener">
                                        <i class="fas fa-window-maximize me-2"></i>
                                        {{ $teacherCanCustomizeLanding ? __('courses::teacher/messages.profile.professional.view') : __('courses::teacher/messages.profile.professional.upgrade') }}
                                    </a>
                                    @if ($teacherCanCustomizeLanding)
                                        <button type="button"
                                            class="btn btn-outline-secondary teacher-card-header__button js-copy-static-link"
                                            data-copy-value="{{ $teacherLandingUrl }}"
                                            data-copied-label="{{ __('courses::teacher/messages.profile.actions.copied') }}">
                                            <i class="fas fa-link me-2"></i>
                                            {{ __('courses::teacher/messages.profile.professional.copy_link') }}
                                        </button>
                                    @endif
                                </div>
                                </div>

                                <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ $fieldLabel('display_name') }} *</label>
                                    <input type="text" name="display_name"
                                        class="form-control @error('display_name') is-invalid @enderror"
                                        value="{{ old('display_name', $application?->display_name) }}" maxlength="100" required>
                                    @error('display_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ $fieldLabel('headline') }} *</label>
                                    <input type="text" name="headline"
                                        class="form-control @error('headline') is-invalid @enderror"
                                        value="{{ old('headline', $application?->headline) }}" maxlength="255" required>
                                    @error('headline')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ $fieldLabel('experience_years') }} *</label>
                                    <input type="number" name="experience_years"
                                        class="form-control @error('experience_years') is-invalid @enderror"
                                        value="{{ old('experience_years', $application?->experience_years) }}"
                                        min="0" max="80" inputmode="numeric" required>
                                    @error('experience_years')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ $fieldLabel('specialties') }} *</label>
                                    <input type="text" name="specialties"
                                        class="form-control @error('specialties') is-invalid @enderror"
                                        value="{{ old('specialties', is_array($application?->specialties) ? implode(', ', $application->specialties) : '') }}"
                                        placeholder="{{ $fieldLabel('specialties_placeholder') }}" required>
                                    @error('specialties')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">{{ $fieldLabel('bio') }}</label>
                                    <textarea name="bio" id="teacher-profile-bio" rows="8" class="form-control js-ckeditor @error('bio') is-invalid @enderror">{{ old('bio', $application?->bio) }}</textarea>
                                    @error('bio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                </div>

                                <div class="teacher-section-submit mt-4">
                                    <button type="submit" name="profile_section" value="professional" class="btn btn-primary" formnovalidate>
                                        {{ __('courses::teacher/messages.profile.actions.save_professional') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="teacher-profile-card teacher-profile-card--links mt-4">
                        <h4>{{ __('courses::teacher/messages.profile.links.title') }}</h4>
                        <p class="text-muted mb-4">{{ __('courses::teacher/messages.profile.links.description') }}</p>

                    <div class="teacher-links-layout">
                        <section class="teacher-links-group">
                            <div class="teacher-links-group__header">
                                <h5>{{ __('courses::teacher/messages.profile.links.public_title') }}</h5>
                                <p>{{ __('courses::teacher/messages.profile.links.public_description') }}</p>
                            </div>

                            <div class="teacher-links-stack">
                                <div class="teacher-link-panel">
                                    <label class="form-label">{{ $fieldLabel('portfolio_url') }}</label>
                                    <div class="teacher-link-field">
                                        <div class="teacher-link-input">
                                            <input type="url" id="teacher-profile-portfolio" name="portfolio_url" class="form-control @error('portfolio_url') is-invalid @enderror" value="{{ $portfolioValue }}">
                                        </div>
                                        <div class="teacher-link-actions">
                                            <a href="{{ $portfolioValue }}" class="btn btn-outline-secondary js-link-action" data-input="teacher-profile-portfolio" target="_blank" rel="noopener noreferrer" @if (empty($portfolioValue)) hidden @endif>{{ __('courses::teacher/messages.profile.actions.preview') }}</a>
                                            <button type="button" class="btn btn-outline-secondary js-copy-link" data-input="teacher-profile-portfolio" data-copied-label="{{ __('courses::teacher/messages.profile.actions.copied') }}">{{ __('courses::teacher/messages.profile.actions.copy') }}</button>
                                            <button type="button" class="btn btn-outline-danger js-clear-input" data-input="teacher-profile-portfolio" data-submit-section="links">{{ __('courses::teacher/messages.profile.actions.remove') }}</button>
                                        </div>
                                    </div>
                                    @error('portfolio_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="teacher-link-feature">
                                    <div class="teacher-link-feature__form">
                                        <label class="form-label">{{ $fieldLabel('intro_video_url') }}</label>
                                        <div class="teacher-link-field">
                                            <div class="teacher-link-input">
                                                <input type="text" id="teacher-profile-intro-video" name="intro_video_url" class="form-control @error('intro_video_url') is-invalid @enderror" value="{{ $introVideoValue }}" placeholder="https://... hoac /storage/...">
                                            </div>
                                            <div class="teacher-link-actions">
                                                <button type="button" class="btn btn-outline-secondary js-lfm" data-input="teacher-profile-intro-video" data-type="video">{{ __('courses::teacher/messages.profile.actions.choose_video') }}</button>
                                                <a href="{{ $introVideoValue }}" class="btn btn-outline-secondary js-link-action" data-input="teacher-profile-intro-video" target="_blank" rel="noopener noreferrer" @if (empty($introVideoValue)) hidden @endif>{{ __('courses::teacher/messages.profile.actions.watch') }}</a>
                                                <button type="button" class="btn btn-outline-secondary js-copy-link" data-input="teacher-profile-intro-video" data-copied-label="{{ __('courses::teacher/messages.profile.actions.copied') }}">{{ __('courses::teacher/messages.profile.actions.copy') }}</button>
                                                <button type="button" class="btn btn-outline-danger js-clear-input" data-input="teacher-profile-intro-video" data-submit-section="links">{{ __('courses::teacher/messages.profile.actions.remove') }}</button>
                                            </div>
                                        </div>
                                        @error('intro_video_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="teacher-link-feature__preview">
                                        <div class="teacher-resource-card" data-resource-card data-input="teacher-profile-intro-video" data-resource-type="video" data-fallback-label="{{ $fieldLabel('intro_video_url') }}" @if (empty($introVideoValue)) hidden @endif>
                                            <div class="teacher-resource-card__media">
                                                <img data-resource-image alt="{{ $fieldLabel('intro_video_url') }}" hidden>
                                                <div class="teacher-resource-card__icon teacher-resource-card__icon--video" data-resource-icon><i class="fas fa-circle-play"></i></div>
                                            </div>
                                            <div class="teacher-resource-card__content">
                                                <strong>{{ $fieldLabel('intro_video_url') }}</strong>
                                                <span data-resource-name>{{ $extractLabel($introVideoValue, $fieldLabel('intro_video_url')) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="teacher-link-socials teacher-link-socials--single">
                                    <div class="teacher-link-panel">
                                        <label class="form-label">{{ $fieldLabel('linkedin_url') }}</label>
                                        <div class="teacher-link-field">
                                            <div class="teacher-link-input">
                                                <input type="url" id="teacher-profile-linkedin" name="linkedin_url" class="form-control @error('linkedin_url') is-invalid @enderror" value="{{ $linkedinValue }}">
                                            </div>
                                            <div class="teacher-link-actions">
                                                <a href="{{ $linkedinValue }}" class="btn btn-outline-secondary js-link-action" data-input="teacher-profile-linkedin" target="_blank" rel="noopener noreferrer" @if (empty($linkedinValue)) hidden @endif>{{ __('courses::teacher/messages.profile.actions.open') }}</a>
                                                <button type="button" class="btn btn-outline-secondary js-copy-link" data-input="teacher-profile-linkedin" data-copied-label="{{ __('courses::teacher/messages.profile.actions.copied') }}">{{ __('courses::teacher/messages.profile.actions.copy') }}</button>
                                                <button type="button" class="btn btn-outline-danger js-clear-input" data-input="teacher-profile-linkedin" data-submit-section="links">{{ __('courses::teacher/messages.profile.actions.remove') }}</button>
                                            </div>
                                        </div>
                                        @error('linkedin_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="teacher-links-group">
                            <div class="teacher-links-group__header">
                                <h5>{{ __('courses::teacher/messages.profile.links.custom_title') }}</h5>
                                <p>{{ __('courses::teacher/messages.profile.links.custom_description') }}</p>
                            </div>

                            <div class="teacher-links-stack">
                                <div class="teacher-custom-links" data-custom-links data-next-index="{{ count($customLinksValue) }}">
                                    <div class="teacher-custom-links__list" data-custom-links-list>
                                        @foreach ($customLinksValue as $index => $customLink)
                                            <div class="teacher-custom-link-row" data-custom-link-row>
                                                <div class="teacher-custom-link-row__fields">
                                                    <div>
                                                        <label class="form-label">{{ $fieldLabel('custom_link_label') }}</label>
                                                        <input type="text"
                                                            name="custom_links[{{ $index }}][label]"
                                                            class="form-control"
                                                            value="{{ $customLink['label'] ?? '' }}"
                                                            maxlength="60"
                                                            placeholder="TikTok">
                                                    </div>
                                                    <div>
                                                        <label class="form-label">{{ $fieldLabel('custom_link_url') }}</label>
                                                        <input type="url"
                                                            id="teacher-custom-link-{{ $index }}"
                                                            name="custom_links[{{ $index }}][url]"
                                                            class="form-control @error("custom_links.$index.url") is-invalid @enderror"
                                                            value="{{ $customLink['url'] ?? '' }}"
                                                            placeholder="https://...">
                                                        @error("custom_links.$index.url")
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="teacher-link-actions">
                                                    <a href="{{ $customLink['url'] ?? '' }}"
                                                        class="btn btn-outline-secondary js-link-action"
                                                        data-input="teacher-custom-link-{{ $index }}"
                                                        target="_blank" rel="noopener noreferrer"
                                                        @if (empty($customLink['url'])) hidden @endif>
                                                        {{ __('courses::teacher/messages.profile.actions.open') }}
                                                    </a>
                                                    <button type="button" class="btn btn-outline-secondary js-copy-link"
                                                        data-input="teacher-custom-link-{{ $index }}"
                                                        data-copied-label="{{ __('courses::teacher/messages.profile.actions.copied') }}">
                                                        {{ __('courses::teacher/messages.profile.actions.copy') }}
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger js-remove-custom-link" data-submit-section="links">
                                                        {{ __('courses::teacher/messages.profile.actions.remove') }}
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-outline-primary teacher-custom-links__add" data-add-custom-link>
                                        {{ __('courses::teacher/messages.profile.actions.add_link') }}
                                    </button>
                                </div>
                            </div>
                        </section>
                    </div>

                        <div class="teacher-section-submit mt-4">
                            <button type="submit" name="profile_section" value="links" class="btn btn-primary" formnovalidate>
                                {{ __('courses::teacher/messages.profile.actions.save_links') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="teacher-profile-tab-panel {{ $activeProfileTab === 'password' ? 'is-active' : '' }}" data-profile-panel="password">
                    <div class="teacher-profile-card teacher-profile-card--narrow">
                        <h4>{{ __('courses::teacher/messages.profile.password.title') }}</h4>
                        <p class="text-muted mb-4">{{ __('courses::teacher/messages.profile.password.description') }}</p>

                        <div class="teacher-profile-note">
                            <i class="fas fa-circle-info"></i>
                            <span>{{ __('courses::teacher/messages.profile.password.hint') }}</span>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-12">
                                <label class="form-label">{{ __('courses::teacher/messages.profile.fields.current_password') }}</label>
                                <input type="password" name="current_password"
                                    class="form-control @error('current_password') is-invalid @enderror"
                                    autocomplete="current-password">
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">{{ __('courses::teacher/messages.profile.fields.password') }}</label>
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    autocomplete="new-password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">{{ __('courses::teacher/messages.profile.fields.password_confirmation') }}</label>
                                <input type="password" name="password_confirmation"
                                    class="form-control @error('password_confirmation') is-invalid @enderror" autocomplete="new-password">
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="teacher-section-submit mt-4">
                            <button type="submit" name="profile_section" value="password" class="btn btn-primary" formnovalidate>
                                {{ __('courses::teacher/messages.profile.actions.save_password') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="teacher-profile-tab-panel {{ $activeProfileTab === 'security' ? 'is-active' : '' }}" data-profile-panel="security">
                    <div class="teacher-profile-card teacher-profile-card--narrow">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <h4>{{ __('courses::teacher/messages.profile.two_factor.title') }}</h4>
                                <p class="text-muted mb-0">{{ __('courses::teacher/messages.profile.two_factor.description') }}</p>
                            </div>
                            <span class="badge {{ $student->two_factor_email_enabled ? 'bg-success' : 'bg-secondary' }}">
                                {{ $student->two_factor_email_enabled ? __('courses::teacher/messages.profile.two_factor.enabled') : __('courses::teacher/messages.profile.two_factor.disabled') }}
                            </span>
                        </div>

                        @if ($student->two_factor_email_enabled && $student->two_factor_email_enabled_at)
                            <div class="teacher-profile-meta mt-3">
                                {{ __('courses::teacher/messages.profile.two_factor.enabled_at', ['date' => $student->two_factor_email_enabled_at->format('d/m/Y H:i:s')]) }}
                            </div>
                        @endif

                        <div class="teacher-profile-actions mt-3">
                            @if ($student->two_factor_email_enabled)
                                <button type="submit" class="btn btn-outline-danger" form="teacher-two-factor-disable-form">
                                    {{ __('courses::teacher/messages.profile.two_factor.disable_button') }}
                                </button>
                            @else
                                <button type="submit" class="btn btn-primary" form="teacher-two-factor-enable-form">
                                    {{ __('courses::teacher/messages.profile.two_factor.enable_button') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

            </form>

            <form id="teacher-two-factor-enable-form" action="{{ route('students.account.two-factor.enable', ['locale' => $teacherLocale]) }}" method="POST" class="d-none">
                @csrf
                <input type="hidden" name="return_route" value="teacher.dashboard.profile">
            </form>

            <form id="teacher-two-factor-disable-form" action="{{ route('students.account.two-factor.disable', ['locale' => $teacherLocale]) }}" method="POST" class="d-none">
                @csrf
                <input type="hidden" name="return_route" value="teacher.dashboard.profile">
            </form>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-profile-shell {
            padding: 1.75rem;
        }

        .teacher-profile-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .teacher-profile-tabs-scroll {
            width: 100%;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding-bottom: 4px;
        }

        .teacher-profile-tabs-scroll::-webkit-scrollbar {
            display: none;
        }

        .teacher-profile-tabs {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
            min-width: max-content;
        }

        .teacher-profile-tab-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            border: 1px solid var(--admin-border);
            background: color-mix(in srgb, var(--admin-card-bg, #121a2c) 84%, transparent);
            color: var(--admin-text);
            border-radius: 999px;
            min-height: 44px;
            padding: 0.7rem 1.1rem;
            font-weight: 700;
            transition: 0.2s ease;
        }

        .teacher-profile-tab-button.is-active {
            background: linear-gradient(135deg, #1f8efa 0%, #33d5c3 100%);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 10px 24px rgba(31, 142, 250, 0.22);
        }

        .teacher-profile-tab-button i {
            font-size: 0.95rem;
        }

        .teacher-profile-tab-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 24px;
            padding: 0.15rem 0.55rem;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 800;
            line-height: 1;
        }

        .teacher-profile-tab-badge.is-enabled {
            background: rgba(27, 197, 109, 0.16);
            color: #7dffb2;
            border: 1px solid rgba(27, 197, 109, 0.28);
        }

        .teacher-profile-tab-badge.is-disabled {
            background: rgba(255, 255, 255, 0.08);
            color: var(--admin-muted-text);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .teacher-profile-tab-button.is-active .teacher-profile-tab-badge.is-disabled {
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
            border-color: rgba(255, 255, 255, 0.18);
        }

        .teacher-profile-tab-panel {
            display: none;
        }

        .teacher-profile-tab-panel.is-active {
            display: block;
        }

        .teacher-profile-card {
            padding: 1.35rem;
            border-radius: 22px;
            background: var(--admin-glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--admin-glass-border);
        }

        .teacher-profile-card--wide {
            padding: 1.5rem;
        }

        .teacher-profile-card--narrow {
            max-width: 760px;
        }

        .teacher-profile-card h4 {
            margin-bottom: 0.35rem;
            font-weight: 800;
        }

        .teacher-profile-current-badge,
        .teacher-profile-badge-banner {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            width: fit-content;
            max-width: 100%;
            margin-top: 0.85rem;
            padding: 0.5rem 0.85rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.03em;
            border: 1px solid transparent;
        }

        .teacher-profile-badge-banner {
            position: relative;
            display: inline-grid;
            grid-template-columns: 48px minmax(0, 1fr);
            align-items: center;
            column-gap: 0.9rem;
            row-gap: 0.12rem;
            width: min(100%, 360px);
            margin: 0 0 1.25rem;
            padding: 0.9rem 1rem;
            border-radius: 18px;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
        }

        .teacher-profile-badge-banner__icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            grid-row: 1 / span 2;
            border-radius: 14px;
            font-size: 1.15rem;
            background: rgba(255, 255, 255, 0.12);
            color: currentColor;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.14);
            position: relative;
            z-index: 1;
        }

        .teacher-profile-badge-banner strong,
        .teacher-profile-badge-banner span {
            display: block;
            position: relative;
            z-index: 1;
            grid-column: 2;
        }

        .teacher-profile-badge-banner strong {
            margin: 0;
            font-size: 0.68rem;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            opacity: 0.72;
            line-height: 1.2;
        }

        .teacher-profile-badge-banner span {
            margin-top: 0;
            font-size: 1.08rem;
            font-weight: 900;
            line-height: 1.25;
            letter-spacing: 0.01em;
        }

        .teacher-profile-current-badge--blue,
        .teacher-profile-badge-banner--blue { background: rgba(59, 130, 246, 0.14); color: #bfdbfe; border-color: rgba(96, 165, 250, 0.24); }
        .teacher-profile-current-badge--gold,
        .teacher-profile-badge-banner--gold { background: rgba(245, 158, 11, 0.16); color: #fde68a; border-color: rgba(251, 191, 36, 0.24); }
        .teacher-profile-current-badge--emerald,
        .teacher-profile-badge-banner--emerald { background: rgba(16, 185, 129, 0.16); color: #a7f3d0; border-color: rgba(52, 211, 153, 0.24); }
        .teacher-profile-current-badge--violet,
        .teacher-profile-badge-banner--violet { background: rgba(139, 92, 246, 0.16); color: #ddd6fe; border-color: rgba(167, 139, 250, 0.24); }
        .teacher-profile-current-badge--rose,
        .teacher-profile-badge-banner--rose { background: rgba(244, 63, 94, 0.16); color: #fecdd3; border-color: rgba(251, 113, 133, 0.24); }
        .teacher-profile-current-badge--slate,
        .teacher-profile-badge-banner--slate { background: rgba(148, 163, 184, 0.16); color: #e2e8f0; border-color: rgba(148, 163, 184, 0.24); }

        .teacher-profile-badge-banner--blue { box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06), 0 10px 24px rgba(59, 130, 246, 0.1); }
        .teacher-profile-badge-banner--gold { box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06), 0 10px 24px rgba(245, 158, 11, 0.12); }
        .teacher-profile-badge-banner--emerald { box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06), 0 10px 24px rgba(16, 185, 129, 0.12); }
        .teacher-profile-badge-banner--violet { box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06), 0 10px 24px rgba(139, 92, 246, 0.12); }
        .teacher-profile-badge-banner--rose { box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06), 0 10px 24px rgba(244, 63, 94, 0.12); }
        .teacher-profile-badge-banner--slate { box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06), 0 10px 24px rgba(100, 116, 139, 0.12); }

        html[data-theme="light"] .teacher-profile-badge-banner {
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        }

        html[data-theme="light"] .teacher-profile-badge-banner__icon {
            background: rgba(255, 255, 255, 0.72);
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
        }

        html[data-theme="light"] .teacher-profile-badge-banner--blue { color: #2563eb; }
        html[data-theme="light"] .teacher-profile-badge-banner--gold { color: #d97706; }
        html[data-theme="light"] .teacher-profile-badge-banner--emerald { color: #059669; }
        html[data-theme="light"] .teacher-profile-badge-banner--violet { color: #7c3aed; }
        html[data-theme="light"] .teacher-profile-badge-banner--rose { color: #e11d48; }
        html[data-theme="light"] .teacher-profile-badge-banner--slate { color: #475569; }

        html[data-theme="light"] .teacher-profile-card {
            background: #ffffff;
            border-color: var(--admin-border);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
        }

        html[data-theme="light"] .teacher-profile-tab-button {
            background: #ffffff;
            border-color: var(--admin-border);
        }

        .teacher-card-header {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .teacher-card-header__actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.65rem;
            flex-wrap: wrap;
        }

        .teacher-card-header .btn {
            white-space: nowrap;
        }

        .teacher-card-header__meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.55rem;
            flex-wrap: wrap;
        }

        .teacher-card-header__meta-label {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--admin-muted-text);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .teacher-card-header__meta-link {
            display: inline-flex;
            align-items: center;
            max-width: 100%;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            background: color-mix(in srgb, var(--admin-card-bg, #121a2c) 82%, transparent);
            border: 1px solid var(--admin-border);
            color: var(--admin-text);
            font-size: 0.9rem;
            text-decoration: none;
        }

        .teacher-card-header__meta-link span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .teacher-card-header__button {
            min-height: 40px;
            padding-inline: 0.95rem;
            border-radius: 999px;
            font-size: 0.92rem;
            font-weight: 700;
        }

        .teacher-profile-note {
            display: flex;
            gap: 0.65rem;
            align-items: flex-start;
            padding: 0.9rem 1rem;
            border-radius: 16px;
            background: color-mix(in srgb, var(--admin-info-bg) 82%, transparent);
            border: 1px solid var(--admin-info-border);
            color: var(--admin-info-text);
        }

        .teacher-profile-note i {
            margin-top: 0.15rem;
        }

        .teacher-profile-avatar {
            display: flex;
            gap: 1rem;
            align-items: center;
            padding: 1rem;
            border-radius: 18px;
            background: color-mix(in srgb, var(--admin-card-bg, #121a2c) 88%, transparent);
            border: 1px solid var(--admin-border);
        }

        .teacher-profile-avatar__preview {
            width: 88px;
            height: 88px;
            border-radius: 22px;
            overflow: hidden;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1f8efa 0%, #33d5c3 100%);
            color: #fff;
            font-size: 2rem;
            font-weight: 800;
            box-shadow: 0 16px 36px rgba(31, 142, 250, 0.24);
        }

        .teacher-profile-avatar__preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .teacher-profile-avatar__content {
            flex: 1;
        }

        .teacher-file-picker {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto auto;
            gap: 0.65rem;
            align-items: stretch;
        }

        .teacher-profile-meta {
            color: var(--admin-muted);
            font-size: 0.92rem;
        }

        .teacher-profile-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .teacher-section-submit {
            display: flex;
            justify-content: flex-end;
        }

        .teacher-links-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
            gap: 1.25rem;
        }

        .teacher-links-group {
            padding: 1.1rem;
            border-radius: 20px;
            background: color-mix(in srgb, var(--admin-card-bg, #121a2c) 82%, transparent);
            border: 1px solid color-mix(in srgb, var(--admin-border) 92%, transparent);
        }

        .teacher-links-group__header {
            margin-bottom: 1rem;
        }

        .teacher-links-group__header h5 {
            margin-bottom: 0.35rem;
            font-size: 1.02rem;
            font-weight: 800;
        }

        .teacher-links-group__header p {
            margin: 0;
            color: var(--admin-muted);
        }

        .teacher-links-stack {
            display: grid;
            gap: 1rem;
        }

        .teacher-link-panel {
            display: grid;
            gap: 0.55rem;
        }

        .teacher-link-socials {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .teacher-link-socials--single {
            grid-template-columns: minmax(0, 1fr);
        }

        .teacher-link-feature {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(280px, 0.9fr);
            gap: 1rem;
            align-items: stretch;
        }

        .teacher-link-feature__form,
        .teacher-link-feature__preview {
            min-width: 0;
        }

        .teacher-link-field {
            display: flex;
            flex-direction: column;
            gap: 0.7rem;
            padding: 1rem;
            border-radius: 18px;
            background: color-mix(in srgb, var(--admin-surface) 86%, transparent);
            border: 1px solid color-mix(in srgb, var(--admin-border) 88%, transparent);
        }

        .teacher-link-input {
            display: block;
        }

        .teacher-link-input .form-control {
            width: 100%;
        }

        .teacher-link-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            align-items: stretch;
        }

        .teacher-link-actions .btn {
            min-height: 42px;
        }

        .teacher-custom-links {
            display: grid;
            gap: 1rem;
        }

        .teacher-custom-links__list {
            display: grid;
            gap: 1rem;
        }

        .teacher-custom-links__add {
            justify-self: flex-start;
        }

        .teacher-custom-link-row {
            display: grid;
            gap: 0.8rem;
            padding: 1rem;
            border-radius: 18px;
            background: color-mix(in srgb, var(--admin-surface) 86%, transparent);
            border: 1px solid color-mix(in srgb, var(--admin-border) 88%, transparent);
        }

        .teacher-custom-link-row__fields {
            display: grid;
            grid-template-columns: minmax(180px, 0.7fr) minmax(0, 1.3fr);
            gap: 1rem;
        }

        .teacher-file-picker .form-control {
            flex: 1;
        }

        .teacher-resource-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin-top: 0.25rem;
        }

        .teacher-resource-card {
            display: flex;
            align-items: stretch;
            gap: 0.9rem;
            padding: 0.95rem 1rem;
            border-radius: 18px;
            background: color-mix(in srgb, var(--admin-surface) 88%, transparent);
            border: 1px solid var(--admin-border);
            min-height: 164px;
            height: 100%;
        }

        .teacher-resource-card__media {
            width: 160px;
            min-width: 160px;
            border-radius: 16px;
            overflow: hidden;
            position: relative;
            background: color-mix(in srgb, var(--admin-surface-2) 82%, transparent);
            border: 1px solid color-mix(in srgb, var(--admin-border) 88%, transparent);
        }

        .teacher-resource-card__media img,
        .teacher-resource-card__media iframe {
            width: 100%;
            height: 100%;
            min-height: 128px;
            border: 0;
            display: block;
            object-fit: cover;
            background: #fff;
        }

        .teacher-resource-card__icon {
            width: 100%;
            height: 100%;
            min-height: 128px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.7rem;
            color: #fff;
            background: linear-gradient(135deg, #2563eb 0%, #38bdf8 100%);
            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.22);
        }

        .teacher-resource-card__icon--video {
            background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
            box-shadow: 0 14px 28px rgba(239, 68, 68, 0.22);
        }

        .teacher-resource-card__icon--identity {
            background: linear-gradient(135deg, #14b8a6 0%, #22c55e 100%);
            box-shadow: 0 14px 28px rgba(20, 184, 166, 0.22);
        }

        .teacher-resource-card__content {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            min-width: 0;
            justify-content: center;
        }

        .teacher-resource-card__content strong {
            font-size: 0.95rem;
            color: var(--admin-text);
        }

        .teacher-resource-card__content span {
            color: var(--admin-muted);
            font-size: 0.88rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }

        html[data-theme="dark"] .teacher-profile-card .cke {
            border-color: var(--admin-border);
            box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.08);
        }

        html[data-theme="dark"] .teacher-profile-card .cke_top,
        html[data-theme="dark"] .teacher-profile-card .cke_bottom {
            background: #162033;
            border-color: var(--admin-border);
            box-shadow: none;
        }

        html[data-theme="dark"] .teacher-profile-card .cke_chrome {
            background: #162033;
            border-color: var(--admin-border);
        }

        html[data-theme="dark"] .teacher-profile-card .cke_toolgroup {
            background: #1a2740;
            border-color: #334155;
            box-shadow: none;
        }

        html[data-theme="dark"] .teacher-profile-card a.cke_button_off:hover,
        html[data-theme="dark"] .teacher-profile-card a.cke_button_off:focus,
        html[data-theme="dark"] .teacher-profile-card a.cke_button_off:active,
        html[data-theme="dark"] .teacher-profile-card .cke_combo_button:hover,
        html[data-theme="dark"] .teacher-profile-card .cke_combo_button:focus {
            background: #22314d;
            border-color: #3b4f70;
        }

        html[data-theme="dark"] .teacher-profile-card .cke_button_icon {
            filter: invert(0.9) hue-rotate(180deg);
        }

        html[data-theme="dark"] .teacher-profile-card .cke_button_label,
        html[data-theme="dark"] .teacher-profile-card .cke_combo_text,
        html[data-theme="dark"] .teacher-profile-card .cke_combo_open,
        html[data-theme="dark"] .teacher-profile-card .cke_toolgroup a,
        html[data-theme="dark"] .teacher-profile-card .cke_path_item,
        html[data-theme="dark"] .teacher-profile-card .cke_path_empty {
            color: #dbe7f5 !important;
        }

        html[data-theme="dark"] .teacher-profile-card .cke_contents {
            border-color: var(--admin-border);
            background: #0b1324;
        }

        @media (max-width: 1199.98px) {
            .teacher-links-layout,
            .teacher-link-feature,
            .teacher-link-socials,
            .teacher-custom-link-row__fields {
                grid-template-columns: 1fr;
            }
        }

        .teacher-profile-submit {
            display: flex;
            flex-wrap: wrap;
            gap: 0.85rem;
            align-items: center;
            justify-content: space-between;
            padding-top: 0.25rem;
        }

        @media (max-width: 767.98px) {
            .teacher-profile-shell {
                padding: 1.1rem;
            }

            .teacher-card-header {
                flex-direction: column;
                align-items: stretch;
            }

            .teacher-card-header__actions {
                justify-content: stretch;
            }

            .teacher-card-header__meta-link {
                width: 100%;
            }

            .teacher-links-layout,
            .teacher-link-feature,
            .teacher-link-socials,
            .teacher-custom-link-row__fields {
                grid-template-columns: 1fr;
            }

            .teacher-profile-avatar,
            .teacher-link-input {
                grid-template-columns: 1fr;
            }

            .teacher-file-picker {
                grid-template-columns: 1fr;
            }

            .teacher-link-actions {
                flex-direction: column;
            }

            .teacher-link-actions .btn {
                width: 100%;
            }

            .teacher-resource-grid {
                grid-template-columns: 1fr;
            }

            .teacher-profile-avatar {
                flex-direction: column;
                align-items: stretch;
            }

            .teacher-profile-submit {
                align-items: stretch;
            }

            .teacher-section-submit {
                justify-content: stretch;
            }

            .teacher-section-submit .btn,
            .teacher-profile-submit .btn {
                width: 100%;
            }
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('backend/plugins/ckeditor/ckeditor.js') }}"></script>
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script>
        (() => {
            const getCkEditorContentCss = (dark) => dark
                ? `
                    html, body {
                        background: #0b1324 !important;
                        color: #e2e8f0 !important;
                    }
                    body {
                        margin: 0;
                        padding: 12px 14px;
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                    }
                    a { color: #93c5fd !important; }
                    table, td, th { border-color: #334155 !important; }
                    blockquote {
                        border-left: 4px solid #334155;
                        color: #cbd5e1;
                        background: rgba(255,255,255,0.03);
                        padding: 0.75rem 1rem;
                    }
                    pre, code {
                        background: #111827;
                        color: #e2e8f0;
                    }
                `
                : `
                    html, body {
                        background: #ffffff !important;
                        color: #111827 !important;
                    }
                    body {
                        margin: 0;
                        padding: 12px 14px;
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                    }
                    a { color: #2563eb !important; }
                    table, td, th { border-color: #dbe4f0 !important; }
                    blockquote {
                        border-left: 4px solid #cbd5e1;
                        color: #334155;
                        background: #f8fafc;
                        padding: 0.75rem 1rem;
                    }
                    pre, code {
                        background: #f8fafc;
                        color: #111827;
                    }
                `;

            const syncCkEditorInstanceTheme = (editor) => {
                if (!editor || editor.status === 'destroyed') {
                    return;
                }

                const dark = document.documentElement.dataset.theme === 'dark';
                const container = editor.container;

                if (container) {
                    if (dark) {
                        container.addClass('cke_admin_dark');
                    } else {
                        container.removeClass('cke_admin_dark');
                    }
                }

                if (editor.document && editor.document.getHead()) {
                    const head = editor.document.getHead();
                    const existingStyle = head.findOne('style[data-admin-cke-theme]');

                    if (existingStyle) {
                        existingStyle.remove();
                    }

                    const style = new CKEDITOR.dom.element('style');
                    style.setAttribute('type', 'text/css');
                    style.setAttribute('data-admin-cke-theme', '1');
                    style.setHtml(getCkEditorContentCss(dark));
                    head.append(style);

                    const body = editor.document.getBody();
                    if (body) {
                        body.setStyle('background', dark ? '#0b1324' : '#ffffff');
                        body.setStyle('color', dark ? '#e2e8f0' : '#111827');
                    }
                }
            };

            const syncAllCkEditorThemes = () => {
                if (typeof CKEDITOR === 'undefined' || !CKEDITOR.instances) {
                    return;
                }

                Object.values(CKEDITOR.instances).forEach(syncCkEditorInstanceTheme);
            };

            const initCkEditors = () => {
                if (typeof CKEDITOR === 'undefined') {
                    return;
                }

                document.querySelectorAll('textarea.js-ckeditor').forEach((textarea) => {
                    if (!textarea.id) {
                        textarea.id = `editor-${Math.random().toString(36).slice(2, 10)}`;
                    }

                    if (textarea.dataset.ckeditorInitialized === '1' || CKEDITOR.instances[textarea.id]) {
                        return;
                    }

                    textarea.dataset.ckeditorInitialized = '1';

                    try {
                        CKEDITOR.replace(textarea.id, {
                            height: 280,
                        });
                    } catch (error) {
                        textarea.dataset.ckeditorInitialized = '0';
                        console.error(error);
                    }
                });

                if (!window.__teacherProfileCkThemeBound) {
                    CKEDITOR.on('instanceReady', (event) => {
                        syncCkEditorInstanceTheme(event.editor);
                    });

                    window.__teacherProfileCkThemeBound = true;
                }

                window.setTimeout(syncAllCkEditorThemes, 0);
            };

            const avatarPreview = document.querySelector('[data-avatar-preview]');

            const syncLinkActions = (inputId) => {
                const input = document.getElementById(inputId);
                if (!input) {
                    return;
                }

                document.querySelectorAll(`.js-link-action[data-input="${inputId}"]`).forEach((link) => {
                    const value = input.value.trim();
                    link.href = value || '#';
                    link.hidden = value === '';
                });
            };

            const getYouTubeId = (value) => {
                const cleaned = (value || '').trim();
                if (!cleaned) {
                    return null;
                }

                const patterns = [
                    /youtube\.com\/watch\?v=([^&]+)/i,
                    /youtu\.be\/([^?&/]+)/i,
                    /youtube\.com\/embed\/([^?&/]+)/i,
                    /youtube\.com\/shorts\/([^?&/]+)/i,
                ];

                for (const pattern of patterns) {
                    const match = cleaned.match(pattern);
                    if (match?.[1]) {
                        return match[1];
                    }
                }

                return null;
            };

            const isImageUrl = (value) => /\.(png|jpe?g|gif|webp|bmp|svg)(\?.*)?$/i.test(value || '');
            const isPdfUrl = (value) => /\.pdf(\?.*)?$/i.test(value || '');

            const extractResourceName = (value, fallback) => {
                const cleaned = (value || '').trim();
                if (!cleaned) {
                    return fallback;
                }

                try {
                    const url = new URL(cleaned, window.location.origin);
                    const path = url.pathname || cleaned;
                    const parts = path.split('/').filter(Boolean);
                    return decodeURIComponent(parts.pop() || fallback);
                } catch (error) {
                    return cleaned;
                }
            };

            const compactStorageValue = (value) => {
                const cleaned = (value || '').trim();
                if (!cleaned) {
                    return '';
                }

                try {
                    const url = new URL(cleaned, window.location.origin);
                    const path = url.pathname || cleaned;

                    if (path.includes('/storage/') || cleaned.includes('/storage/')) {
                        const parts = path.split('/').filter(Boolean);
                        return decodeURIComponent(parts.pop() || cleaned);
                    }

                    return cleaned;
                } catch (error) {
                    if (cleaned.includes('/storage/')) {
                        const parts = cleaned.split('/').filter(Boolean);
                        return decodeURIComponent(parts.pop() || cleaned);
                    }

                    return cleaned;
                }
            };

            const syncCompactDisplay = (inputId, displayInputId) => {
                const input = document.getElementById(inputId);
                const displayInput = document.getElementById(displayInputId);

                if (!input || !displayInput) {
                    return;
                }

                displayInput.value = compactStorageValue(input.value);
            };

            const submitProfileSection = async (trigger, section) => {
                if (!section) {
                    return;
                }

                const form = trigger.closest('form');
                if (!form) {
                    return;
                }

                let hiddenInput = form.querySelector('input[name="profile_section"][data-auto-submit]');
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'profile_section';
                    hiddenInput.dataset.autoSubmit = '1';
                    form.appendChild(hiddenInput);
                }

                hiddenInput.value = section;
                form.noValidate = true;

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        throw new Error(`Profile autosave failed: ${response.status}`);
                    }
                } catch (error) {
                    console.error(error);
                }
            };

            const syncResourceCard = (inputId) => {
                const input = document.getElementById(inputId);
                const card = document.querySelector(`[data-resource-card][data-input="${inputId}"]`);
                if (!input || !card) {
                    return;
                }

                const value = input.value.trim();
                card.hidden = value === '';

                const label = card.querySelector('[data-resource-name]');
                const title = card.dataset.fallbackLabel || card.querySelector('strong')?.textContent?.trim() || inputId;
                if (label) {
                    label.textContent = extractResourceName(value, title);
                }

                const type = card.dataset.resourceType || 'document';
                const image = card.querySelector('[data-resource-image]');
                const pdf = card.querySelector('[data-resource-pdf]');
                const icon = card.querySelector('[data-resource-icon]');

                if (image) {
                    image.hidden = true;
                    image.removeAttribute('src');
                }

                if (pdf) {
                    pdf.hidden = true;
                    pdf.removeAttribute('src');
                }

                if (icon) {
                    icon.hidden = false;
                }

                if (value === '') {
                    return;
                }

                if (type === 'video') {
                    const youtubeId = getYouTubeId(value);
                    if (youtubeId && image) {
                        image.src = `https://img.youtube.com/vi/${youtubeId}/hqdefault.jpg`;
                        image.hidden = false;
                        if (icon) {
                            icon.hidden = true;
                        }
                    }
                    return;
                }

                if (isImageUrl(value) && image) {
                    image.src = value;
                    image.hidden = false;
                    if (icon) {
                        icon.hidden = true;
                    }
                    return;
                }

                if (isPdfUrl(value) && pdf) {
                    pdf.src = `${value}#toolbar=0&navpanes=0&scrollbar=0`;
                    pdf.hidden = false;
                    if (icon) {
                        icon.hidden = true;
                    }
                }
            };

            const syncAvatarPreview = () => {
                if (!avatarPreview) {
                    return;
                }

                const input = document.getElementById('teacher-profile-avatar');
                const image = avatarPreview.querySelector('img');
                const fallback = avatarPreview.querySelector('span');
                const value = input?.value?.trim() || '';

                if (image) {
                    image.src = value || '';
                    image.hidden = value === '';
                }

                if (fallback) {
                    fallback.hidden = value !== '';
                }
            };

            const profileTabButtons = document.querySelectorAll('[data-profile-tab]');
            const profileTabPanels = document.querySelectorAll('[data-profile-panel]');

            const activateProfileTab = (tab) => {
                if (!tab) {
                    return;
                }

                profileTabButtons.forEach((button) => {
                    button.classList.toggle('is-active', button.dataset.profileTab === tab);
                });

                profileTabPanels.forEach((panel) => {
                    panel.classList.toggle('is-active', panel.dataset.profilePanel === tab);
                });
            };

            profileTabButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    activateProfileTab(button.dataset.profileTab);
                });
            });

            const initialActiveTab = document.querySelector('[data-profile-tab].is-active')?.dataset.profileTab
                || document.querySelector('[data-profile-panel].is-active')?.dataset.profilePanel
                || 'profile';

            activateProfileTab(initialActiveTab);

            $('.js-lfm').each(function() {
                const button = $(this);
                const type = button.data('type') || 'file';
                button.filemanager(type);
            });

            document.querySelectorAll('.js-lfm').forEach((button) => {
                if (button.dataset.boundPicker === '1') {
                    return;
                }

                button.dataset.boundPicker = '1';
                const inputId = button.dataset.input;
                const displayInputId = button.dataset.displayInput;
                const input = document.getElementById(inputId);

                if (!input) {
                    return;
                }

                const syncAll = () => {
                    if (displayInputId) {
                        syncCompactDisplay(inputId, displayInputId);
                    }
                    syncLinkActions(inputId);
                    syncResourceCard(inputId);
                    if (button.dataset.previewTarget === 'avatar') {
                        syncAvatarPreview();
                    }
                };

                input.addEventListener('input', syncAll);
                input.addEventListener('change', syncAll);
                button.addEventListener('click', () => {
                    const initialValue = input.value;
                    const pollTimer = window.setInterval(() => {
                        if (input.value !== initialValue) {
                            syncAll();
                            window.clearInterval(pollTimer);
                        }
                    }, 300);

                    window.setTimeout(() => {
                        window.clearInterval(pollTimer);
                    }, 10000);
                });
                syncAll();
            });

            document.querySelectorAll('.js-clear-input').forEach((button) => {
                button.addEventListener('click', () => {
                    const input = document.getElementById(button.dataset.input);
                    if (!input) {
                        return;
                    }

                    input.value = '';
                    syncLinkActions(button.dataset.input);
                    syncResourceCard(button.dataset.input);

                    if (button.dataset.displayInput) {
                        syncCompactDisplay(button.dataset.input, button.dataset.displayInput);
                    }

                    if (button.dataset.previewTarget === 'avatar') {
                        syncAvatarPreview();
                    }

                    if (button.dataset.submitSection) {
                        submitProfileSection(button, button.dataset.submitSection);
                    }
                });
            });

            document.querySelectorAll('.js-link-action').forEach((link) => {
                if (link.dataset.boundLinkAction === '1') {
                    return;
                }

                link.dataset.boundLinkAction = '1';
                const inputId = link.dataset.input;
                const input = document.getElementById(inputId);
                input?.addEventListener('input', () => {
                    syncLinkActions(inputId);
                    syncResourceCard(inputId);
                });
                syncLinkActions(inputId);
                syncResourceCard(inputId);
            });

            document.querySelectorAll('.js-copy-link').forEach((button) => {
                if (button.dataset.boundCopyAction === '1') {
                    return;
                }

                button.dataset.boundCopyAction = '1';
                const defaultLabel = button.textContent.trim();
                const copiedLabel = button.dataset.copiedLabel || defaultLabel;
                const inputId = button.dataset.input;

                button.addEventListener('click', async () => {
                    const input = document.getElementById(inputId);
                    const value = input?.value?.trim() || '';
                    if (!value) {
                        return;
                    }

                    try {
                        await navigator.clipboard.writeText(value);
                        button.textContent = copiedLabel;
                        window.setTimeout(() => {
                            button.textContent = defaultLabel;
                        }, 1400);
                    } catch (error) {
                        input?.focus();
                        input?.select();
                    }
                });
            });

            document.querySelectorAll('.js-copy-static-link').forEach((button) => {
                if (button.dataset.boundStaticCopy === '1') {
                    return;
                }

                button.dataset.boundStaticCopy = '1';
                const defaultLabel = button.textContent.trim();
                const copiedLabel = button.dataset.copiedLabel || defaultLabel;
                const value = button.dataset.copyValue || '';

                button.addEventListener('click', async () => {
                    if (!value.trim()) {
                        return;
                    }

                    try {
                        await navigator.clipboard.writeText(value);
                        button.textContent = copiedLabel;
                        window.setTimeout(() => {
                            button.textContent = defaultLabel;
                        }, 1400);
                    } catch (error) {
                        console.error(error);
                    }
                });
            });

            const customLinksRoot = document.querySelector('[data-custom-links]');

            if (customLinksRoot) {
                const customLinksList = customLinksRoot.querySelector('[data-custom-links-list]');
                const addCustomLinkButton = customLinksRoot.querySelector('[data-add-custom-link]');

                const bindLinkHelpers = (scope) => {
                    scope.querySelectorAll('.js-link-action').forEach((link) => {
                        const inputId = link.dataset.input;
                        const input = document.getElementById(inputId);
                        if (!input || input.dataset.boundLinkAction === '1') {
                            return;
                        }

                        input.dataset.boundLinkAction = '1';
                        input.addEventListener('input', () => {
                            syncLinkActions(inputId);
                        });
                        syncLinkActions(inputId);
                    });

                    scope.querySelectorAll('.js-copy-link').forEach((button) => {
                        if (button.dataset.boundCopyAction === '1') {
                            return;
                        }

                        button.dataset.boundCopyAction = '1';
                        const defaultLabel = button.textContent.trim();
                        const copiedLabel = button.dataset.copiedLabel || defaultLabel;
                        const inputId = button.dataset.input;

                        button.addEventListener('click', async () => {
                            const input = document.getElementById(inputId);
                            const value = input?.value?.trim() || '';
                            if (!value) {
                                return;
                            }

                            try {
                                await navigator.clipboard.writeText(value);
                                button.textContent = copiedLabel;
                                window.setTimeout(() => {
                                    button.textContent = defaultLabel;
                                }, 1400);
                            } catch (error) {
                                input?.focus();
                                input?.select();
                            }
                        });
                    });
                };

                customLinksRoot.addEventListener('click', (event) => {
                    const removeButton = event.target.closest('.js-remove-custom-link');
                    if (!removeButton) {
                        return;
                    }

                    const rows = customLinksList.querySelectorAll('[data-custom-link-row]');
                    const row = removeButton.closest('[data-custom-link-row]');
                    if (!row) {
                        return;
                    }

                    if (rows.length <= 1) {
                        row.querySelectorAll('input').forEach((input) => {
                            input.value = '';
                            if (input.id) {
                                syncLinkActions(input.id);
                            }
                        });
                        if (removeButton.dataset.submitSection) {
                            submitProfileSection(removeButton, removeButton.dataset.submitSection);
                        }
                        return;
                    }

                    row.remove();
                    if (removeButton.dataset.submitSection) {
                        submitProfileSection(removeButton, removeButton.dataset.submitSection);
                    }
                });

                addCustomLinkButton?.addEventListener('click', () => {
                    const nextIndex = Number(customLinksRoot.dataset.nextIndex || '0');
                    customLinksRoot.dataset.nextIndex = String(nextIndex + 1);

                    const wrapper = document.createElement('div');
                    wrapper.className = 'teacher-custom-link-row';
                    wrapper.setAttribute('data-custom-link-row', '');

                    wrapper.innerHTML = `
                        <div class="teacher-custom-link-row__fields">
                            <div>
                                <label class="form-label">{{ $fieldLabel('custom_link_label') }}</label>
                                <input type="text" name="custom_links[${nextIndex}][label]" class="form-control" maxlength="60" placeholder="TikTok">
                            </div>
                            <div>
                                <label class="form-label">{{ $fieldLabel('custom_link_url') }}</label>
                                <input type="url" id="teacher-custom-link-${nextIndex}" name="custom_links[${nextIndex}][url]" class="form-control" placeholder="https://...">
                            </div>
                        </div>
                        <div class="teacher-link-actions">
                            <a href="#" class="btn btn-outline-secondary js-link-action" data-input="teacher-custom-link-${nextIndex}" target="_blank" rel="noopener noreferrer" hidden>{{ __('courses::teacher/messages.profile.actions.open') }}</a>
                            <button type="button" class="btn btn-outline-secondary js-copy-link" data-input="teacher-custom-link-${nextIndex}" data-copied-label="{{ __('courses::teacher/messages.profile.actions.copied') }}">{{ __('courses::teacher/messages.profile.actions.copy') }}</button>
                            <button type="button" class="btn btn-outline-danger js-remove-custom-link" data-submit-section="links">{{ __('courses::teacher/messages.profile.actions.remove') }}</button>
                        </div>
                    `;

                    customLinksList.appendChild(wrapper);
                    bindLinkHelpers(wrapper);
                });

                bindLinkHelpers(customLinksRoot);
            }

            const avatarInput = document.getElementById('teacher-profile-avatar');
            avatarInput?.addEventListener('input', syncAvatarPreview);
            syncAvatarPreview();
            initCkEditors();

            const themeObserver = new MutationObserver(() => {
                syncAllCkEditorThemes();
            });

            themeObserver.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['data-theme'],
            });
        })();
    </script>
@endsection
