@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="account-page account-certificates-page py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 mb-4">
                    <div class="account-sidebar">
                        @include('students::clients.menu')
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="account-content account-certificates-content">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body p-4">
                                <h2 class="fw-semibold mb-2">
                                    {{ __('students::clients/account.my_certificates.title') }}
                                </h2>
                                <p class="text-muted mb-0">
                                    {{ __('students::clients/account.my_certificates.desc') }}
                                </p>
                            </div>
                        </div>

                        @if ($certificates->isEmpty())
                            <div class="card shadow-sm border-0 py-5 text-center">
                                <div class="card-body">
                                    <div class="mb-4">
                                        <i class="fa-solid fa-award text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ __('students::clients/account.my_certificates.empty') }}</h4>
                                    <p class="text-muted mb-0">{{ __('students::clients/account.my_certificates.empty_desc') }}</p>
                                </div>
                            </div>
                        @else
                            <div class="row g-4">
                                @foreach ($certificates as $certificate)
                                    <div class="col-md-6">
                                        <article class="card h-100 shadow-sm border-0 certificate-card overflow-hidden">
                                            <div class="certificate-card__glow"></div>
                                            <div class="card-body p-4 position-relative">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="badge bg-success-soft text-success px-3 py-2 rounded-pill fw-bold">
                                                        <i class="fa-solid fa-check-circle me-1"></i>
                                                        {{ __('students::clients/account.my_certificates.issued_at') }}
                                                    </span>
                                                    <span class="text-muted small fw-medium">
                                                        {{ optional($certificate->issued_at)->format('d/m/Y') }}
                                                    </span>
                                                </div>
                                                
                                                <h4 class="fw-bold mb-1 text-dark">{{ $certificate->course_name_snapshot }}</h4>
                                                <p class="text-muted small mb-3">
                                                    <i class="fa-solid fa-chalkboard-user me-1"></i>
                                                    {{ $certificate->teacher_name_snapshot }}
                                                </p>
                                                
                                                <div class="certificate-info-box mb-4">
                                                    <div class="small text-muted mb-1">{{ __('students::clients/account.my_certificates.code') }}</div>
                                                    <div class="fw-bold text-primary font-monospace">{{ $certificate->code }}</div>
                                                </div>
                                                
                                                <div class="d-grid gap-2 d-sm-flex">
                                                    <a href="{{ route('students.account.certificates.show', ['locale' => app()->getLocale(), 'id' => $certificate->id]) }}" 
                                                       class="btn btn-outline-primary flex-fill">
                                                        <i class="fa-solid fa-eye me-1"></i>
                                                        {{ __('students::clients/account.my_certificates.view') }}
                                                    </a>
                                                    <a href="{{ route('students.account.certificates.show', ['locale' => app()->getLocale(), 'id' => $certificate->id, 'print' => 1]) }}" 
                                                       class="btn btn-primary flex-fill" target="_blank" rel="noopener">
                                                        <i class="fa-solid fa-file-pdf me-1"></i>
                                                        {{ __('students::clients/account.my_certificates.print') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </article>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4">
                                {{ $certificates->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
        
        .certificate-card {
            transition: all 0.3s ease;
            border-radius: 1rem;
        }
        
        .certificate-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0,0,0,0.1) !important;
        }

        .certificate-card__glow {
            position: absolute;
            top: 0;
            right: 0;
            width: 150px;
            height: 150px;
            background: radial-gradient(circle at top right, rgba(var(--bs-primary-rgb), 0.05), transparent 70%);
            pointer-events: none;
        }

        .certificate-info-box {
            background-color: #f8fafc;
            padding: 1rem;
            border-radius: 0.75rem;
            border: 1px dashed #e2e8f0;
        }

        .font-monospace {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            letter-spacing: 0.05em;
        }

        /* Dark Mode Fixes */
        html[data-theme="dark"] .account-certificates-content h2,
        html[data-theme="dark"] .account-certificates-content h4 {
            color: #f8fafc !important;
        }

        html[data-theme="dark"] .card {
            background-color: #1e293b;
            border: 1px solid #334155 !important;
        }

        html[data-theme="dark"] .certificate-info-box {
            background-color: #0f172a;
            border-color: #334155;
        }

        html[data-theme="dark"] .text-dark {
            color: #f8fafc !important;
        }

        html[data-theme="dark"] .text-muted {
            color: #94a3b8 !important;
        }

        html[data-theme="dark"] .text-primary {
            color: #60a5fa !important;
        }
    </style>
@endsection
