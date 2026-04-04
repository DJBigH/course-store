@extends('layouts.teacher')

@section('content')
    @php
        $teacherLocale = session('locale', app()->getLocale());
        $avatarValue = old('image', $teacher?->image);
        $portfolioValue = old('portfolio_url', $application?->portfolio_url);
        $introVideoValue = old('intro_video_url', $application?->intro_video_url);
        $facebookValue = old('facebook_url', $application?->facebook_url);
        $youtubeValue = old('youtube_url', $application?->youtube_url);
        $linkedinValue = old('linkedin_url', $application?->linkedin_url);
        $cvValue = old('cv_file', $application?->cv_file);
        $identityValue = old('identity_file', $application?->identity_file);
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
        $fieldLabel = static function (string $key): string {
            return __("teacher::dashboard.profile.fields.$key");
        };
    @endphp
    <div class="teacher-page-shell">
        <div class="teacher-panel teacher-profile-shell">
            <div class="teacher-section-title">
                <div>
                    <h3 class="fw-bold mb-2">{{ __('teacher::dashboard.profile.title') }}</h3>
                    <p class="text-muted mb-0">{{ __('teacher::dashboard.profile.description') }}</p>
                </div>
                <span class="teacher-status-badge">
                    {{ $student->two_factor_email_enabled ? __('teacher::dashboard.profile.two_factor.enabled') : __('teacher::dashboard.profile.two_factor.disabled') }}
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
                <div class="alert alert-danger mb-3">{{ __('teacher::dashboard.common.validation_summary') }}</div>
            @endif

            <form method="POST" action="{{ route('teacher.dashboard.profile.update') }}" class="teacher-profile-form">
                @csrf

                <div class="row g-4">
                    <div class="col-xl-7">
                        <div class="teacher-profile-card">
                            <h4>{{ __('teacher::dashboard.profile.basic.title') }}</h4>
                            <p class="text-muted mb-4">{{ __('teacher::dashboard.profile.basic.description') }}</p>

                            <div class="teacher-profile-avatar mb-4">
                                <div class="teacher-profile-avatar__preview" data-avatar-preview>
                                    <img src="{{ $avatarValue }}" alt="{{ __('teacher::dashboard.profile.fields.avatar') }}"
                                        @if (empty($avatarValue)) hidden @endif>
                                    <span @if (!empty($avatarValue)) hidden @endif>{{ $avatarFallback }}</span>
                                </div>
                                <div class="teacher-profile-avatar__content">
                                    <label class="form-label">{{ __('teacher::dashboard.profile.fields.avatar') }} *</label>
                                    <div class="teacher-file-picker">
                                        <input type="text" id="teacher-profile-avatar" name="image"
                                            class="form-control @error('image') is-invalid @enderror"
                                            value="{{ $avatarValue }}"
                                        placeholder="{{ __('teacher::dashboard.profile.fields.avatar_placeholder') }}" required>
                                        <button type="button" class="btn btn-outline-secondary js-lfm"
                                            data-input="teacher-profile-avatar" data-preview-target="avatar"
                                            data-type="image">
                                            {{ __('teacher::dashboard.profile.actions.choose_image') }}
                                        </button>
                                        <button type="button" class="btn btn-outline-danger js-clear-input"
                                            data-input="teacher-profile-avatar" data-preview-target="avatar">
                                            {{ __('teacher::dashboard.profile.actions.remove') }}
                                        </button>
                                    </div>
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('teacher::dashboard.profile.fields.name') }} *</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $student->name) }}" maxlength="225" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('teacher::dashboard.profile.fields.email') }}</label>
                                    <input type="email" class="form-control" value="{{ $student->email }}" readonly>
                                    <div class="form-text">{{ __('teacher::dashboard.profile.basic.email_hint') }}</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('teacher::dashboard.profile.fields.phone') }} *</label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                        value="{{ old('phone', $student->phone) }}" inputmode="tel" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('teacher::dashboard.profile.fields.package') }}</label>
                                    <input type="text" class="form-control"
                                        value="{{ $teacher?->application?->package?->name_locale ?: $teacher?->application?->package?->name ?: __('teacher::dashboard.profile.basic.no_package') }}"
                                        readonly>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">{{ __('teacher::dashboard.profile.fields.address') }}</label>
                                    <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                                        value="{{ old('address', $student->address) }}" maxlength="255">
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="teacher-section-submit mt-4">
                                <button type="submit" name="profile_section" value="account" class="btn btn-primary">
                                    Luu thong tin tai khoan
                                </button>
                            </div>
                        </div>

                        <div class="teacher-profile-card teacher-profile-card--wide mt-4">
                            <h4>{{ __('teacher::dashboard.profile.professional.title') }}</h4>
                            <p class="text-muted mb-4">{{ __('teacher::dashboard.profile.professional.description') }}</p>

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
                                    <textarea name="bio" rows="5" class="form-control ckeditor @error('bio') is-invalid @enderror" 
                                        maxlength="5000">{{ old('bio', $application?->bio) }}</textarea>
                                    @error('bio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="teacher-section-submit mt-4">
                                <button type="submit" name="profile_section" value="professional" class="btn btn-primary">
                                    Luu thong tin nghe nghiep
                                </button>
                            </div>
                        </div>

                    </div>

                    <div class="col-xl-5">
                        <div class="teacher-profile-card">
                            <h4>{{ __('teacher::dashboard.profile.password.title') }}</h4>
                            <p class="text-muted mb-4">{{ __('teacher::dashboard.profile.password.description') }}</p>

                            <div class="teacher-profile-note">
                                <i class="fas fa-circle-info"></i>
                                <span>{{ __('teacher::dashboard.profile.password.hint') }}</span>
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-12">
                                    <label class="form-label">{{ __('teacher::dashboard.profile.fields.current_password') }}</label>
                                    <input type="password" name="current_password"
                                        class="form-control @error('current_password') is-invalid @enderror"
                                        autocomplete="current-password">
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">{{ __('teacher::dashboard.profile.fields.password') }}</label>
                                    <input type="password" name="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        autocomplete="new-password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">{{ __('teacher::dashboard.profile.fields.password_confirmation') }}</label>
                                    <input type="password" name="password_confirmation"
                                        class="form-control @error('password_confirmation') is-invalid @enderror" autocomplete="new-password">
                                    @error('password_confirmation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="teacher-section-submit mt-4">
                                <button type="submit" name="profile_section" value="password" class="btn btn-primary">
                                    Luu mat khau
                                </button>
                            </div>
                        </div>

                        <div class="teacher-profile-card mt-4">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h4>{{ __('teacher::dashboard.profile.two_factor.title') }}</h4>
                                    <p class="text-muted mb-0">{{ __('teacher::dashboard.profile.two_factor.description') }}</p>
                                </div>
                                <span class="badge {{ $student->two_factor_email_enabled ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $student->two_factor_email_enabled ? __('teacher::dashboard.profile.two_factor.enabled') : __('teacher::dashboard.profile.two_factor.disabled') }}
                                </span>
                            </div>

                            @if ($student->two_factor_email_enabled && $student->two_factor_email_enabled_at)
                                <div class="teacher-profile-meta mt-3">
                                    {{ __('teacher::dashboard.profile.two_factor.enabled_at', ['date' => $student->two_factor_email_enabled_at->format('d/m/Y H:i:s')]) }}
                                </div>
                            @endif

                            <div class="teacher-profile-actions mt-3">
                                @if ($student->two_factor_email_enabled)
                                    <form action="{{ route('students.account.two-factor.disable', ['locale' => $teacherLocale]) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="return_route" value="teacher.dashboard.profile">
                                        <button type="submit" class="btn btn-outline-danger">
                                            {{ __('teacher::dashboard.profile.two_factor.disable_button') }}
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('students.account.two-factor.enable', ['locale' => $teacherLocale]) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="return_route" value="teacher.dashboard.profile">
                                        <button type="submit" class="btn btn-primary">
                                            {{ __('teacher::dashboard.profile.two_factor.enable_button') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="teacher-profile-card teacher-profile-card--links">
                    <h4>{{ __('teacher::dashboard.profile.links.title') }}</h4>
                    <p class="text-muted mb-4">{{ __('teacher::dashboard.profile.links.description') }}</p>

                    <div class="teacher-links-layout">
                        <section class="teacher-links-group">
                            <div class="teacher-links-group__header">
                                <h5>{{ __('teacher::dashboard.profile.links.public_title') }}</h5>
                                <p>{{ __('teacher::dashboard.profile.links.public_description') }}</p>
                            </div>

                            <div class="teacher-links-stack">
                                <div class="teacher-link-panel">
                                    <label class="form-label">{{ $fieldLabel('portfolio_url') }}</label>
                                    <div class="teacher-link-field">
                                        <div class="teacher-link-input">
                                            <input type="url" id="teacher-profile-portfolio" name="portfolio_url" class="form-control @error('portfolio_url') is-invalid @enderror" value="{{ $portfolioValue }}">
                                        </div>
                                        <div class="teacher-link-actions">
                                            <a href="{{ $portfolioValue }}" class="btn btn-outline-secondary js-link-action" data-input="teacher-profile-portfolio" target="_blank" rel="noopener noreferrer" @if (empty($portfolioValue)) hidden @endif>{{ __('teacher::dashboard.profile.actions.preview') }}</a>
                                            <button type="button" class="btn btn-outline-secondary js-copy-link" data-input="teacher-profile-portfolio" data-copied-label="{{ __('teacher::dashboard.profile.actions.copied') }}">{{ __('teacher::dashboard.profile.actions.copy') }}</button>
                                            <button type="button" class="btn btn-outline-danger js-clear-input" data-input="teacher-profile-portfolio">{{ __('teacher::dashboard.profile.actions.remove') }}</button>
                                        </div>
                                    </div>
                                    @error('portfolio_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="teacher-link-feature">
                                    <div class="teacher-link-feature__form">
                                        <label class="form-label">{{ $fieldLabel('intro_video_url') }}</label>
                                        <div class="teacher-link-field">
                                            <div class="teacher-link-input">
                                                <input type="url" id="teacher-profile-intro-video" name="intro_video_url" class="form-control @error('intro_video_url') is-invalid @enderror" value="{{ $introVideoValue }}">
                                            </div>
                                            <div class="teacher-link-actions">
                                                <button type="button" class="btn btn-outline-secondary js-lfm" data-input="teacher-profile-intro-video" data-type="video">{{ __('teacher::dashboard.profile.actions.choose_video') }}</button>
                                                <a href="{{ $introVideoValue }}" class="btn btn-outline-secondary js-link-action" data-input="teacher-profile-intro-video" target="_blank" rel="noopener noreferrer" @if (empty($introVideoValue)) hidden @endif>{{ __('teacher::dashboard.profile.actions.watch') }}</a>
                                                <button type="button" class="btn btn-outline-secondary js-copy-link" data-input="teacher-profile-intro-video" data-copied-label="{{ __('teacher::dashboard.profile.actions.copied') }}">{{ __('teacher::dashboard.profile.actions.copy') }}</button>
                                                <button type="button" class="btn btn-outline-danger js-clear-input" data-input="teacher-profile-intro-video">{{ __('teacher::dashboard.profile.actions.remove') }}</button>
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

                                <div class="teacher-link-socials">
                                    <div class="teacher-link-panel">
                                        <label class="form-label">{{ $fieldLabel('facebook_url') }}</label>
                                        <div class="teacher-link-field">
                                            <div class="teacher-link-input">
                                                <input type="url" id="teacher-profile-facebook" name="facebook_url" class="form-control @error('facebook_url') is-invalid @enderror" value="{{ $facebookValue }}">
                                            </div>
                                            <div class="teacher-link-actions">
                                                <a href="{{ $facebookValue }}" class="btn btn-outline-secondary js-link-action" data-input="teacher-profile-facebook" target="_blank" rel="noopener noreferrer" @if (empty($facebookValue)) hidden @endif>{{ __('teacher::dashboard.profile.actions.open') }}</a>
                                                <button type="button" class="btn btn-outline-secondary js-copy-link" data-input="teacher-profile-facebook" data-copied-label="{{ __('teacher::dashboard.profile.actions.copied') }}">{{ __('teacher::dashboard.profile.actions.copy') }}</button>
                                                <button type="button" class="btn btn-outline-danger js-clear-input" data-input="teacher-profile-facebook">{{ __('teacher::dashboard.profile.actions.remove') }}</button>
                                            </div>
                                        </div>
                                        @error('facebook_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="teacher-link-panel">
                                        <label class="form-label">{{ $fieldLabel('youtube_url') }}</label>
                                        <div class="teacher-link-field">
                                            <div class="teacher-link-input">
                                                <input type="url" id="teacher-profile-youtube" name="youtube_url" class="form-control @error('youtube_url') is-invalid @enderror" value="{{ $youtubeValue }}">
                                            </div>
                                            <div class="teacher-link-actions">
                                                <a href="{{ $youtubeValue }}" class="btn btn-outline-secondary js-link-action" data-input="teacher-profile-youtube" target="_blank" rel="noopener noreferrer" @if (empty($youtubeValue)) hidden @endif>{{ __('teacher::dashboard.profile.actions.open') }}</a>
                                                <button type="button" class="btn btn-outline-secondary js-copy-link" data-input="teacher-profile-youtube" data-copied-label="{{ __('teacher::dashboard.profile.actions.copied') }}">{{ __('teacher::dashboard.profile.actions.copy') }}</button>
                                                <button type="button" class="btn btn-outline-danger js-clear-input" data-input="teacher-profile-youtube">{{ __('teacher::dashboard.profile.actions.remove') }}</button>
                                            </div>
                                        </div>
                                        @error('youtube_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="teacher-link-panel">
                                        <label class="form-label">{{ $fieldLabel('linkedin_url') }}</label>
                                        <div class="teacher-link-field">
                                            <div class="teacher-link-input">
                                                <input type="url" id="teacher-profile-linkedin" name="linkedin_url" class="form-control @error('linkedin_url') is-invalid @enderror" value="{{ $linkedinValue }}">
                                            </div>
                                            <div class="teacher-link-actions">
                                                <a href="{{ $linkedinValue }}" class="btn btn-outline-secondary js-link-action" data-input="teacher-profile-linkedin" target="_blank" rel="noopener noreferrer" @if (empty($linkedinValue)) hidden @endif>{{ __('teacher::dashboard.profile.actions.open') }}</a>
                                                <button type="button" class="btn btn-outline-secondary js-copy-link" data-input="teacher-profile-linkedin" data-copied-label="{{ __('teacher::dashboard.profile.actions.copied') }}">{{ __('teacher::dashboard.profile.actions.copy') }}</button>
                                                <button type="button" class="btn btn-outline-danger js-clear-input" data-input="teacher-profile-linkedin">{{ __('teacher::dashboard.profile.actions.remove') }}</button>
                                            </div>
                                        </div>
                                        @error('linkedin_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="teacher-links-group">
                            <div class="teacher-links-group__header">
                                <h5>{{ __('teacher::dashboard.profile.links.verification_title') }}</h5>
                                <p>{{ __('teacher::dashboard.profile.links.verification_description') }}</p>
                            </div>

                            <div class="teacher-links-stack">
                                <div class="teacher-link-feature">
                                    <div class="teacher-link-feature__form">
                                        <label class="form-label">{{ $fieldLabel('cv_file') }}</label>
                                        <div class="teacher-link-field">
                                            <div class="teacher-link-input">
                                                <input type="text" id="teacher-profile-cv" name="cv_file" class="form-control @error('cv_file') is-invalid @enderror" value="{{ $cvValue }}">
                                            </div>
                                            <div class="teacher-link-actions">
                                                <button type="button" class="btn btn-outline-secondary js-lfm" data-input="teacher-profile-cv" data-type="file">{{ __('teacher::dashboard.profile.actions.choose_file') }}</button>
                                                <a href="{{ $cvValue }}" class="btn btn-outline-secondary js-link-action" data-input="teacher-profile-cv" target="_blank" rel="noopener noreferrer" @if (empty($cvValue)) hidden @endif>{{ __('teacher::dashboard.profile.actions.preview') }}</a>
                                                <button type="button" class="btn btn-outline-secondary js-copy-link" data-input="teacher-profile-cv" data-copied-label="{{ __('teacher::dashboard.profile.actions.copied') }}">{{ __('teacher::dashboard.profile.actions.copy') }}</button>
                                                <button type="button" class="btn btn-outline-danger js-clear-input" data-input="teacher-profile-cv">{{ __('teacher::dashboard.profile.actions.remove') }}</button>
                                            </div>
                                        </div>
                                        @error('cv_file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="teacher-link-feature__preview">
                                        <div class="teacher-resource-card" data-resource-card data-input="teacher-profile-cv" data-resource-type="document" data-fallback-label="{{ $fieldLabel('cv_file') }}" @if (empty($cvValue)) hidden @endif>
                                            <div class="teacher-resource-card__media">
                                                <img data-resource-image alt="{{ $fieldLabel('cv_file') }}" hidden>
                                                <iframe data-resource-pdf title="{{ $fieldLabel('cv_file') }}" hidden></iframe>
                                                <div class="teacher-resource-card__icon" data-resource-icon><i class="fas fa-file-lines"></i></div>
                                            </div>
                                            <div class="teacher-resource-card__content">
                                                <strong>{{ $fieldLabel('cv_file') }}</strong>
                                                <span data-resource-name>{{ $extractLabel($cvValue, $fieldLabel('cv_file')) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="teacher-link-feature">
                                    <div class="teacher-link-feature__form">
                                        <label class="form-label">{{ $fieldLabel('identity_file') }}</label>
                                        <div class="teacher-link-field">
                                            <div class="teacher-link-input">
                                                <input type="text" id="teacher-profile-identity" name="identity_file" class="form-control @error('identity_file') is-invalid @enderror" value="{{ $identityValue }}">
                                            </div>
                                            <div class="teacher-link-actions">
                                                <button type="button" class="btn btn-outline-secondary js-lfm" data-input="teacher-profile-identity" data-type="file">{{ __('teacher::dashboard.profile.actions.choose_file') }}</button>
                                                <a href="{{ $identityValue }}" class="btn btn-outline-secondary js-link-action" data-input="teacher-profile-identity" target="_blank" rel="noopener noreferrer" @if (empty($identityValue)) hidden @endif>{{ __('teacher::dashboard.profile.actions.preview') }}</a>
                                                <button type="button" class="btn btn-outline-secondary js-copy-link" data-input="teacher-profile-identity" data-copied-label="{{ __('teacher::dashboard.profile.actions.copied') }}">{{ __('teacher::dashboard.profile.actions.copy') }}</button>
                                                <button type="button" class="btn btn-outline-danger js-clear-input" data-input="teacher-profile-identity">{{ __('teacher::dashboard.profile.actions.remove') }}</button>
                                            </div>
                                        </div>
                                        @error('identity_file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="teacher-link-feature__preview">
                                        <div class="teacher-resource-card" data-resource-card data-input="teacher-profile-identity" data-resource-type="document" data-fallback-label="{{ $fieldLabel('identity_file') }}" @if (empty($identityValue)) hidden @endif>
                                            <div class="teacher-resource-card__media">
                                                <img data-resource-image alt="{{ $fieldLabel('identity_file') }}" hidden>
                                                <iframe data-resource-pdf title="{{ $fieldLabel('identity_file') }}" hidden></iframe>
                                                <div class="teacher-resource-card__icon teacher-resource-card__icon--identity" data-resource-icon><i class="fas fa-id-card"></i></div>
                                            </div>
                                            <div class="teacher-resource-card__content">
                                                <strong>{{ $fieldLabel('identity_file') }}</strong>
                                                <span data-resource-name>{{ $extractLabel($identityValue, $fieldLabel('identity_file')) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="teacher-section-submit mt-4">
                        <button type="submit" name="profile_section" value="links" class="btn btn-primary">
                            Luu lien ket ho so
                        </button>
                    </div>
                </div>

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

        .teacher-profile-card {
            padding: 1.35rem;
            border-radius: 22px;
            background: var(--admin-subtle-bg);
            border: 1px solid var(--admin-border);
        }

        .teacher-profile-card--wide {
            padding: 1.5rem;
        }

        .teacher-profile-card h4 {
            margin-bottom: 0.35rem;
            font-weight: 800;
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

        @media (max-width: 1199.98px) {
            .teacher-links-layout,
            .teacher-link-feature,
            .teacher-link-socials {
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

            .teacher-links-layout,
            .teacher-link-feature,
            .teacher-link-socials {
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
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script>
        (() => {
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

            $('.js-lfm').each(function() {
                const button = $(this);
                const type = button.data('type') || 'file';
                button.filemanager(type);
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

                    if (button.dataset.previewTarget === 'avatar') {
                        syncAvatarPreview();
                    }
                });
            });

            document.querySelectorAll('.js-link-action').forEach((link) => {
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

            const avatarInput = document.getElementById('teacher-profile-avatar');
            avatarInput?.addEventListener('input', syncAvatarPreview);
            syncAvatarPreview();
        })();
    </script>
@endsection
