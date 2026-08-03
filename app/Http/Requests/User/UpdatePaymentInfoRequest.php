<?php

namespace App\Http\Requests\User;

use App\Rules\UgandanPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->isMethod('GET')) {
            return [];
        }

        return [
            'payout_method'       => 'required|in:bank,mobile_money',
            'bank_name'           => 'required_if:payout_method,bank',
            'paystack_bank_code'  => 'required_if:payout_method,bank',
            'account_number'      => 'required_if:payout_method,bank',
            'account_name'        => 'required_if:payout_method,bank',
            'mobile_network'      => 'required_if:payout_method,mobile_money|in:MTN,AIRTEL',
            'mobile_money_number' => ['required_if:payout_method,mobile_money', new UgandanPhoneNumber()],
        ];
    }
}
