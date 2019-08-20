<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddApprovalLevelRequest extends FormRequest
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
        $isUpdate = $this->route()->hasParameter('level');

        return [
            'name' => $isUpdate ? 'required' : 'required|unique:approval_levels,id'
        ];
    }

    /**
     * Custom error message
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Please add approval level name',
        ];
    }
}
