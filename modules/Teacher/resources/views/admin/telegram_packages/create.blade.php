@extends('layouts.backend')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h5 class="mb-1">{{ __('teacher::admin.titles.create_telegram_package') }}</h5>
                        </div>
                        <a href="{{ route('teacher.telegram-packages.index') }}" class="btn btn-light border rounded-3">
                            <i class="fa-solid fa-arrow-left me-2"></i> {{ __('teacher::admin.actions.back') }}
                        </a>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger border-0 rounded-4">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('teacher.telegram-packages.store') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">{{ __('teacher::admin.fields.telegram_name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">{{ __('teacher::admin.fields.telegram_name_en') }}</label>
                                <input type="text" class="form-control" name="name_en" value="{{ old('name_en') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">{{ __('teacher::admin.fields.telegram_name_ja') }}</label>
                                <input type="text" class="form-control" name="name_ja" value="{{ old('name_ja') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">{{ __('teacher::admin.fields.telegram_name_ko') }}</label>
                                <input type="text" class="form-control" name="name_ko" value="{{ old('name_ko') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">{{ __('teacher::admin.fields.telegram_name_zh') }}</label>
                                <input type="text" class="form-control" name="name_zh" value="{{ old('name_zh') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">{{ __('teacher::admin.fields.telegram_price') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control price-format" name="price" value="{{ old('price', 0) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">{{ __('teacher::admin.fields.telegram_sale_price') }}</label>
                                <input type="text" class="form-control price-format" name="sale_price" value="{{ old('sale_price') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">{{ __('teacher::admin.fields.telegram_duration_value') }} <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="duration_value" value="{{ old('duration_value', 1) }}" required min="1">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">{{ __('teacher::admin.fields.telegram_duration_unit') }} <span class="text-danger">*</span></label>
                                <select class="form-select" name="duration_unit" required>
                                    @foreach ($units as $key => $label)
                                        <option value="{{ $key }}" {{ old('duration_unit') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">{{ __('teacher::admin.fields.telegram_description') }}</label>
                                <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">{{ __('teacher::admin.fields.telegram_description_en') }}</label>
                                <textarea class="form-control" name="description_en" rows="3">{{ old('description_en') }}</textarea>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">{{ __('teacher::admin.fields.telegram_description_ja') }}</label>
                                <textarea class="form-control" name="description_ja" rows="3">{{ old('description_ja') }}</textarea>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">{{ __('teacher::admin.fields.telegram_description_ko') }}</label>
                                <textarea class="form-control" name="description_ko" rows="3">{{ old('description_ko') }}</textarea>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">{{ __('teacher::admin.fields.telegram_description_zh') }}</label>
                                <textarea class="form-control" name="description_zh" rows="3">{{ old('description_zh') }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">{{ __('teacher::admin.fields.telegram_sort_order') }}</label>
                                <input type="number" class="form-control" name="sort_order" value="{{ old('sort_order', 0) }}">
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                    <label class="form-check-label fw-bold" for="is_active">{{ __('teacher::admin.fields.telegram_is_active') }}</label>
                                </div>
                            </div>

                            <div class="col-12 text-end pt-3">
                                <button type="submit" class="btn btn-success px-4 py-2 rounded-3">
                                    <i class="fa-solid fa-save me-2"></i> {{ __('teacher::admin.actions.save_telegram_package') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        html[data-theme="dark"] .card { background-color: #1e293b; color: #f1f5f9; }
        html[data-theme="dark"] .form-control, html[data-theme="dark"] .form-select {
            background-color: #0f172a; border-color: #334155; color: #f1f5f9;
        }
        html[data-theme="dark"] .btn-light { background-color: #334155; border-color: #475569; color: #f1f5f9; }
    </style>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            function formatNumber(n) {
                return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }

            $('.price-format').on('input', function() {
                var input = $(this).val();
                $(this).val(formatNumber(input));
            });

            // Format on load if there's a value
            $('.price-format').each(function() {
                var val = $(this).val();
                if (val) {
                    $(this).val(formatNumber(val.toString()));
                }
            });

            // Strip commas before submit
            $('form').on('submit', function() {
                $('.price-format').each(function() {
                    var val = $(this).val().replace(/,/g, '');
                    $(this).val(val);
                });
            });
        });
    </script>
@endsection
