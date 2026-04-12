@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="account-page py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 mb-4">
                    <div class="account-sidebar">
                        @include('students::clients.menu')
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="account-content">
                        <h2 class="mb-3">Chung chi cua toi</h2>
                        <p class="text-muted mb-4">Xem cac chung chi ma giang vien da cap cho ban va su dung nut In / Luu PDF de tai ve.</p>

                        @if ($certificates->isEmpty())
                            <div class="account-dashboard-block">
                                <h4 class="mb-2">Chua co chung chi nao</h4>
                                <p class="mb-0 text-muted">Khi giang vien cap chung chi, ban se thay danh sach hien tai day.</p>
                            </div>
                        @else
                            <div class="row g-3">
                                @foreach ($certificates as $certificate)
                                    <div class="col-md-6">
                                        <article class="account-dashboard-block certificate-card">
                                            <div class="certificate-card__head">
                                                <span class="certificate-card__badge">Da cap</span>
                                                <strong>{{ optional($certificate->issued_at)->format('d/m/Y') }}</strong>
                                            </div>
                                            <h4>{{ $certificate->course_name_snapshot }}</h4>
                                            <p>{{ $certificate->teacher_name_snapshot }}</p>
                                            <div class="certificate-card__meta">
                                                <span>Ma chung chi</span>
                                                <strong>{{ $certificate->code }}</strong>
                                            </div>
                                            <div class="certificate-card__actions">
                                                <a href="{{ route('students.account.certificates.show', ['locale' => app()->getLocale(), 'id' => $certificate->id]) }}" class="btn btn-outline-primary">
                                                    Xem chung chi
                                                </a>
                                                <a href="{{ route('students.account.certificates.show', ['locale' => app()->getLocale(), 'id' => $certificate->id, 'print' => 1]) }}" class="btn btn-primary" target="_blank" rel="noopener">
                                                    In / Luu PDF
                                                </a>
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
@endsection

@section('stylesheets')
    <style data-account-page-style>
        .certificate-card {
            height: 100%;
        }

        .certificate-card__head,
        .certificate-card__actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .certificate-card__badge {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.7rem;
            border-radius: 999px;
            background: rgba(22, 163, 74, 0.12);
            color: #16a34a;
            font-size: 0.75rem;
            font-weight: 800;
        }

        .certificate-card h4 {
            margin: 1rem 0 0.35rem;
        }

        .certificate-card p,
        .certificate-card__meta span {
            color: #64748b;
        }

        .certificate-card__meta {
            display: grid;
            gap: 0.15rem;
            margin: 0.85rem 0 1rem;
        }
    </style>
@endsection
