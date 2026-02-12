{{-- COUPON --}}
<label class="form-label fw-semibold">
    <i class="bi bi-ticket-perforated me-1 text-info"></i>
    {{ __('students::clients/checkout.coupons.title') }}
</label>
<div>
    <form action="" class="coupon-form mb-3 {{ !$order->coupon ? '' : 'd-none' }}"
        data-msg-coupon-apply-success="{{ __('students::clients/messages.verify_coupons.coupon_apply_success') }}"
        data-msg-coupon-remove-success="{{ __('students::clients/messages.verify_coupons.coupon_remove_success') }}"
        data-msg-coupon-remove-failed="{{ __('students::clients/messages.verify_coupons.coupon_remove_failed') }}"
        data-msg-coupon-required="{{ __('students::clients/messages.verify_coupons.coupon_required') }}">
        <fieldset>
            <div class="input-group">
                <input type="text" class="form-control text-uppercase" name="coupon_code"
                    placeholder="{{ __('students::clients/checkout.coupons.placeholder') }}" autocomplete="off">
                <button type="submit" class="btn btn-outline-primary apply-coupon">
                    {{ __('students::clients/checkout.coupons.apply') }}
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
