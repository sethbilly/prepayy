<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddRoleRequest extends FormRequest
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
            'role' => 'bail|required|array',
            'role.display_name' => 'required|max:255',
            'role.description' => 'max:255',
            'permissions' => 'bail|required|array',
            'permissions.*' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'role.display_name.required' => 'Please add a name for the role',
            'permissions.required' => 'Please add permissions for the role',
            'permissions.*.required' => 'Please add permissions for the role',
        ];
    }
}
