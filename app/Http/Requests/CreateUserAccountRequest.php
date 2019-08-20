<?php

namespace App\Http\Requests;

use App\Entities\Role;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()
            ->ability([Role::ROLE_ACCOUNT_OWNER, Role::ROLE_APP_OWNER], [
                'add-user',
                'edit-user'
            ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'bail|required|email',
            'contact_number' => 'string',
            'generate_password' => '',
            // App owners and account owners cannot modify their roles
            'roles' => 'bail|required|array',
            'roles.*' => 'required|numeric',
            'approval_level_id' => 'bail|numeric'
        ];
    }

    public function messages()
    {
        return [
            'firstname.required' => 'Please add the first name for the user',
            'lastname.required' => 'Please add the last name for the user',
            'email.required' => 'Please add an email address for the user',
            'roles.required' => 'Please add roles for the user',
            'roles.*required' => 'Please add roles for the user',
            'approval_level_id' => 'Please select the user\'s approval level'
        ];
    }
}
