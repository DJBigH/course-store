@extends('layouts.client')

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-9">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4 p-lg-5">
                            <span class="badge bg-primary-subtle text-primary mb-3">{{ __('teacher::gifts.student.badge') }}</span>
                            <h1 class="h2 fw-bold mb-3">{{ __('teacher::gifts.student.index_title') }}</h1>
                            <p class="text-muted mb-4">{{ __('teacher::gifts.student.index_description') }}</p>

                            <div class="row g-3">
                                @foreach ($gifts as $gift)
                                    <div class="col-md-6">
                                        <div class="border rounded-4 p-3 h-100">
                                            <div class="small text-muted mb-2">
                                                {{ __('teacher::gifts.student.from_teacher', ['teacher' => $gift->teacher?->name ?? 'Teacher']) }}
                                            </div>
                                            <div class="fw-bold mb-2">{{ $gift->course?->name_locale ?: $gift->course?->name }}</div>
                                            <div class="small text-muted mb-3">
                                                {{ __('teacher::gifts.student.reason_label', ['reason' => __('teacher::gifts.reasons.' . $gift->reason)]) }}
                                            </div>
                                            <a href="{{ route('students.account.gifts.show', ['locale' => app()->getLocale(), 'token' => $gift->token]) }}"
                                                class="btn btn-primary w-100">
                                                {{ __('teacher::gifts.student.open_gift') }}
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
