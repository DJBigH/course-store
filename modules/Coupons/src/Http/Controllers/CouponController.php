<?php

namespace Modules\Coupons\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Modules\Coupons\src\Http\Requests\CouponRequest;
use Modules\Students\src\Repositories\CouponsRepositoryInterface;

class CouponController extends Controller
{
    protected $couponRepository;

    public function __construct(CouponsRepositoryInterface $couponRepository)
    {
        $this->couponRepository = $couponRepository;
    }
    public function index()
    {
        $pageTitle = 'Quản lý mã giảm giá';
        return view('coupons::lists', compact('pageTitle'));
    }

    public function data()
    {
        $coupons = $this->couponRepository->getAllCoupons();
        return datatables()->of($coupons)
            ->addColumn('discount_type', function ($coupon) {
                return $coupon->discount_type === 'percent'
                    ? '<span class="badge bg-info">%</span>'
                    : '<span class="badge bg-success">Tiền</span>';
            })
            ->addColumn('discount_value', function ($coupon) {
                if ($coupon->discount_type === 'value') {
                    return '- ' . money($coupon->discount_value);
                }

                if ($coupon->discount_type === 'percent') {
                    return '- ' . $coupon->discount_value . ' %';
                }

                return '';
            })
            ->addColumn('count', function ($coupon) {
                return $coupon->count ?? 'Không giới hạn';
            })
            ->addColumn('time', function ($coupon) {
                if (!$coupon->start_date || !$coupon->end_date) {
                    return '<span class="text-muted">Chưa thiết lập</span>';
                }
                return Carbon::parse($coupon->start_date)->format('d/m/Y')
                    . ' → '
                    . Carbon::parse($coupon->end_date)->format('d/m/Y');
            })
            ->addColumn('total_condition', function ($coupon) {
                return money($coupon->total_condition) ?? 'Chưa thiết lập';
            })
            ->addColumn('edit', function ($coupon) {
                return '<a href="' . route('coupons.edit', $coupon->id) . '" class="btn btn-sm btn-warning">Sửa</a>';
            })
            ->addColumn('delete', function ($coupon) {
                return '<a href="' . route('coupons.delete', $coupon->id) . '" class="btn btn-danger delete-action">Xóa</a>';
            })
            ->rawColumns(['edit', 'delete', 'discount_type', 'discount_value', 'time'])
            ->make(true);
    }

    public function create()
    {
        $pageTitle = 'Thêm mã giảm giá';
        return view('coupons::create', compact('pageTitle'));
    }

    public function store(CouponRequest $request)
    {
        $coupons = $request->except(['_token']);
        $coupon = $this->couponRepository->create($coupons);
        if (!$coupon) {
            abort(404);
        }
        return redirect()->route('coupons.index')->with('msg', __('coupons::messages.create.success'));
    }

    public function edit($id)
    {
        $pageTitle = 'Chỉnh sửa mã giảm giá';
        $coupon = $this->couponRepository->find($id);
        return view('coupons::edit', compact('pageTitle', 'coupon'));
    }

    public function update(CouponRequest $request, $id)
    {
        $coupons = $request->except(['_token']);
        $this->couponRepository->update($id, $coupons);
        return redirect()->route('coupons.index')->with('msg', __('coupons::messages.update.success'));
    }

    public function delete($id)
    {
        $coupon = $this->couponRepository->find($id);
        if (!$coupon) {
            abort(404);
        }
        $this->couponRepository->delete($id);
        return back()->with('msg', __('coupons::messages.delete.success'));
    }
}
