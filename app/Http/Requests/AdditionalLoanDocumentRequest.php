<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdditionalLoanDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $canPeformOp = $this->user() &&
            ($this->user()->isEmployerStaff() || $this->user()->isFinancialInstitutionStaff());

        if ($canPeformOp) {
            return true;
        }

        abort(403);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'request' => 'required'
        ];
    }
}
