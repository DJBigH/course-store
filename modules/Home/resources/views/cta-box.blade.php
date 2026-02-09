<section class="cta-page">
    <div class="cta-container">
        <span class="cta-badge">🚀 {{ __('home::common.badge') }}</span>

        <h1>
            {{ __('home::common.title_1') }} <span> {{ __('home::common.title_2') }}</span>
        </h1>

        <p class="cta-desc">
            {{ __('home::common.description') }}
        </p>

        <div class="cta-actions">
            <a href="{{ route('contacts.home') }}" class="cta-btn primary">
                🚀 {{ __('home::common.btn_consult') }}
            </a>
            <a href="#" class="cta-btn outline">
                📞 {{ __('home::common.btn_call') }} {{ setting('phone', '012345678') }}
            </a>
        </div>

        <div class="cta-features">
            <div class="feature">
                <i class="fas fa-check-circle"></i>
                <span>{{ __('home::common.feature_1') }}</span>
            </div>
            <div class="feature">
                <i class="fas fa-check-circle"></i>
                <span>{{ __('home::common.feature_2') }}</span>
            </div>
            <div class="feature">
                <i class="fas fa-check-circle"></i>
                <span>{{ __('home::common.feature_3') }}</span>
            </div>
        </div>
    </div>
</section>
