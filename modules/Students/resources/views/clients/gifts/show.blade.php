@extends('layouts.client')

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4 p-lg-5">
                            <span class="badge bg-primary-subtle text-primary mb-3">{{ __('teacher::gifts.student.badge') }}</span>
                            <h1 class="h2 fw-bold mb-3">{{ __('teacher::gifts.student.show_title') }}</h1>
                            <p class="text-muted mb-4">{{ __('teacher::gifts.student.show_description') }}</p>

                            <div class="border rounded-4 p-4 mb-4">
                                <div class="small text-muted mb-2">
                                    {{ __('teacher::gifts.student.from_teacher', ['teacher' => $gift->teacher?->name ?? 'Teacher']) }}
                                </div>
                                <div class="h4 fw-bold mb-2">{{ $gift->course?->name_locale ?: $gift->course?->name }}</div>
                                <div class="text-muted mb-2">
                                    {{ __('teacher::gifts.student.reason_label', ['reason' => __('teacher::gifts.reasons.' . $gift->reason)]) }}
                                </div>
                                @if ($gift->note)
                                    <div class="small text-muted">{{ $gift->note }}</div>
                                @endif
                            </div>

                            <div class="alert alert-info mb-4">
                                {{ __('teacher::gifts.student.accept_hint') }}
                            </div>

                            <form method="POST" action="{{ route('students.account.gifts.accept', ['locale' => app()->getLocale(), 'token' => $gift->token]) }}">
                                @csrf
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('teacher::gifts.student.accept_action') }}
                                    </button>
                                    <a href="{{ route('students.account.gifts.index', ['locale' => app()->getLocale()]) }}"
                                        class="btn btn-outline-secondary">
                                        {{ __('teacher::gifts.student.back_to_gifts') }}
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
