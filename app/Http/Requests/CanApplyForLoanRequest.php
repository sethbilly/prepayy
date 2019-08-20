<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CanApplyForLoanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $canApply = $this->user() && $this->user()->isBorrower();

        if ($canApply) {
            return true;
        }
        abort(403, 'Only borrower accounts can apply for loans');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
