@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Code</label>
        <input type="text" class="form-control" name="code" value="{{ old('code', $package->code ?? '') }}" required>
    </div>
    <div class="col-md-8">
        <label class="form-label">Ten goi</label>
        <input type="text" class="form-control" name="name" value="{{ old('name', $package->name ?? '') }}" required>
    </div>
    <div class="col-12">
        <label class="form-label">Mo ta</label>
        <textarea class="form-control" name="description" rows="4">{{ old('description', $package->description ?? '') }}</textarea>
    </div>
    <div class="col-md-4">
        <label class="form-label">Gia</label>
        <input type="number" class="form-control" name="price" min="0" step="0.01"
            value="{{ old('price', $package->price ?? 0) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Billing cycle</label>
        <select name="billing_cycle" class="form-select">
            @foreach (['one_time' => 'One time', 'monthly' => 'Monthly', 'yearly' => 'Yearly'] as $value => $label)
                <option value="{{ $value }}" {{ old('billing_cycle', $package->billing_cycle ?? 'one_time') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Course limit</label>
        <input type="number" class="form-control" name="course_limit" min="1"
            value="{{ old('course_limit', $package->course_limit ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Commission rate</label>
        <input type="number" class="form-control" name="commission_rate" min="0" max="100" step="0.01"
            value="{{ old('commission_rate', $package->commission_rate ?? 50) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Support level</label>
        <input type="text" class="form-control" name="support_level"
            value="{{ old('support_level', $package->support_level ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Sort order</label>
        <input type="number" class="form-control" name="sort_order" min="0"
            value="{{ old('sort_order', $package->sort_order ?? 0) }}">
    </div>
    <div class="col-md-6">
        <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" name="priority_review" value="1"
                {{ old('priority_review', $package->priority_review ?? false) ? 'checked' : '' }}>
            <label class="form-check-label">Priority review</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" name="status" value="1"
                {{ old('status', $package->status ?? true) ? 'checked' : '' }}>
            <label class="form-check-label">Dang bat</label>
        </div>
    </div>
</div>
