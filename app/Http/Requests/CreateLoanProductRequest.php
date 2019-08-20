<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateLoanProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'loan_type_id' => 'required|numeric',
            'min_amount' => 'bail|required|numeric',
            'max_amount' => 'bail|required|numeric',
            'interest_per_year' => 'required|numeric',
            'description' => 'string|max:250',
            'image' => 'image'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Please add a name for the loan',
            'min_amount.required' => 'Please add the minimum amount for the loan',
            'max_amount.required' => 'Please add the maximum amount for the loan',
            'interest_per_year' => 'Please add the interest per year for the loan',
        ];
    }
}
