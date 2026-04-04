<footer class="app-shell-footer py-4 mt-auto">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between small">
            <div class="text-muted">
                Copyright &copy; {{ date('Y') }} by
                <a href="{{ route('home', ['locale' => app()->getLocale()]) }}">{{ env('APP_NAME') }}</a>.
                All rights reserved
            </div>
        </div>
    </div>
</footer>
