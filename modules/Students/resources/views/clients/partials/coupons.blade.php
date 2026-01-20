{{-- COUPON --}}
<label class="form-label fw-semibold">
    <i class="bi bi-ticket-perforated me-1 text-info"></i>
    Mã giảm giá
</label>
<div>
    <form action="" class="coupon-form mb-3 {{ !$order->coupon ? '' : 'd-none' }}">
        <fieldset>
            <div class="input-group">
                <input type="text" class="form-control text-uppercase" name="coupon_code"
                    placeholder="Nhập mã giảm giá..." autocomplete="off">
                <button type="submit" class="btn btn-outline-primary apply-coupon">
                    Áp dụng
                </button>
            </div>
            <small class="error text-danger d-block mt-1"></small>
        </fieldset>
    </form>
</div>

<div class="coupon-usage btn-group mb-2 {{ !$order->coupon ? 'd-none' : '' }}">
    <button class="btn btn-success btn-sm coupon-value" type="button">{{ $order->coupon }}</button>
    <button class="btn btn-danger btn-sm js-remove-coupon" type="button">&times;</button>
</div>
