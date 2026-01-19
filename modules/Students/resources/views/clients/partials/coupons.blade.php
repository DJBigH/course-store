{{-- COUPON --}}
<div class="mb-3">
    <label class="form-label fw-semibold">
        <i class="bi bi-ticket-perforated me-1 text-info"></i>
        Mã giảm giá
    </label>

    <form action="" class="coupon-form">
        <fieldset>
            <div class="input-group">
            <input type="text" class="form-control text-uppercase" name="coupon_code" placeholder="Nhập mã giảm giá..."
                autocomplete="off">
            <button type="submit" class="btn btn-outline-primary apply-coupon">
                Áp dụng
            </button>
        </div>
        {{-- message --}}
        <small class="error text-danger d-block mt-1"></small>
        </fieldset>
    </form>
</div>
