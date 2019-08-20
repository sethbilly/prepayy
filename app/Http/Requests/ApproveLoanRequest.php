<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveLoanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $canApprove = $this->user() &&
            ($this->user()->isEmployerStaff() || $this->user()->isFinancialInstitutionStaff());

        if ($canApprove) {
            return true;
        }

        abort(403, 'You are not authorized to approve loans');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'status_id' => 'required|numeric',
            'reason' => 'string|max:255'
        ];
    }
}
