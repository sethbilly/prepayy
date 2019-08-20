<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitForPartnerApprovalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $canSubmit = $this->user() && $this->user()->isBorrower();

        if ($canSubmit) {
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
            'submission_token' => 'required'
        ];
    }

    public function messages()
    {
        return [
          'submission_token.required' => 'Please enter your token below'
        ];
    }
}
