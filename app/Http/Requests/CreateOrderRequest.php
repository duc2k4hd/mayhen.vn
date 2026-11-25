<?php

namespace App\Http\Requests;

use App\Services\PlanAccessService;
use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && ! app(PlanAccessService::class)->isGuestUser($user);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'exists:plans,id'],
            'invitation_id' => ['nullable', 'exists:invitations,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'min:8', 'max:20'],
            'payment_method' => ['required', 'in:vietqr,sepay,bank_transfer'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_phone.required' => 'Vui lòng nhập số điện thoại hoặc Zalo để nhận hỗ trợ kích hoạt khi cần.',
            'customer_phone.min' => 'Số điện thoại phải có ít nhất 8 chữ số.',
            'payment_method.in' => 'Phương thức thanh toán không hợp lệ.',
        ];
    }
}
