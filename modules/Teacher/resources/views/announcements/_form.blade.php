@php
    $selectedPackageIds = collect(old('package_ids', $selectedPackageIds ?? []))->map(fn ($id) => (int) $id)->all();
@endphp

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h5 class="mb-3">Noi dung thong bao</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tieu de (VI)</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $announcement->title) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Icon Font Awesome</label>
                        <input type="text" name="icon" class="form-control" value="{{ old('icon', $announcement->icon ?: 'fas fa-bullhorn') }}" placeholder="fas fa-bullhorn">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Noi dung (VI)</label>
                        <textarea name="message" rows="4" class="form-control" required>{{ old('message', $announcement->message) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tieu de (EN)</label>
                        <input type="text" name="title_en" class="form-control" value="{{ old('title_en', $announcement->title_en) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tieu de (KO)</label>
                        <input type="text" name="title_ko" class="form-control" value="{{ old('title_ko', $announcement->title_ko) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tieu de (JA)</label>
                        <input type="text" name="title_ja" class="form-control" value="{{ old('title_ja', $announcement->title_ja) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tieu de (ZH)</label>
                        <input type="text" name="title_zh" class="form-control" value="{{ old('title_zh', $announcement->title_zh) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Noi dung (EN)</label>
                        <textarea name="message_en" rows="3" class="form-control">{{ old('message_en', $announcement->message_en) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Noi dung (KO)</label>
                        <textarea name="message_ko" rows="3" class="form-control">{{ old('message_ko', $announcement->message_ko) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Noi dung (JA)</label>
                        <textarea name="message_ja" rows="3" class="form-control">{{ old('message_ja', $announcement->message_ja) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Noi dung (ZH)</label>
                        <textarea name="message_zh" rows="3" class="form-control">{{ old('message_zh', $announcement->message_zh) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="mb-3">Hanh dong va pham vi</h5>
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label">Link hanh dong</label>
                        <input type="text" name="action_url" class="form-control" value="{{ old('action_url', $announcement->action_url) }}" placeholder="{{ route('teacher.dashboard.package.upgrade') }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Nhan nut (VI)</label>
                        <input type="text" name="action_label" class="form-control" value="{{ old('action_label', $announcement->action_label) }}" placeholder="Xem chi tiet">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nhan nut (EN)</label>
                        <input type="text" name="action_label_en" class="form-control" value="{{ old('action_label_en', $announcement->action_label_en) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nhan nut (KO)</label>
                        <input type="text" name="action_label_ko" class="form-control" value="{{ old('action_label_ko', $announcement->action_label_ko) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nhan nut (JA)</label>
                        <input type="text" name="action_label_ja" class="form-control" value="{{ old('action_label_ja', $announcement->action_label_ja) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nhan nut (ZH)</label>
                        <input type="text" name="action_label_zh" class="form-control" value="{{ old('action_label_zh', $announcement->action_label_zh) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Ap dung cho goi</label>
                        <div class="row g-2">
                            @foreach ($packages as $package)
                                <div class="col-md-6 col-xl-4">
                                    <label class="border rounded-3 p-3 w-100 h-100">
                                        <div class="form-check m-0">
                                            <input class="form-check-input" type="checkbox" name="package_ids[]" value="{{ $package->id }}"
                                                @checked(in_array((int) $package->id, $selectedPackageIds, true))>
                                            <span class="form-check-label">
                                                <strong>{{ $package->name }}</strong>
                                                <span class="d-block text-muted small mt-1">{{ strtoupper($package->code) }} · {{ money($package->price) }}</span>
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-text">Neu khong tick goi nao, thong bao se ap dung cho tat ca teacher.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="mb-3">Trang thai</h5>
                <div class="mb-3">
                    <label class="form-label">Bat dau hien thi</label>
                    <input type="datetime-local" name="starts_at" class="form-control"
                        value="{{ old('starts_at', optional($announcement->starts_at)->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Ket thuc hien thi</label>
                    <input type="datetime-local" name="ends_at" class="form-control"
                        value="{{ old('ends_at', optional($announcement->ends_at)->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="status" value="1"
                        @checked(old('status', $announcement->status ?? true))>
                    <label class="form-check-label">Dang hoat dong</label>
                </div>
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="is_pinned" value="1"
                        @checked(old('is_pinned', $announcement->is_pinned ?? false))>
                    <label class="form-check-label">Ghim len tren</label>
                </div>

                <button class="btn btn-primary w-100">{{ $announcement->exists ? 'Cap nhat thong bao' : 'Tao thong bao' }}</button>
            </div>
        </div>
    </div>
</div>
