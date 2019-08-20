<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddEmployerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->isApplicationOwner();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $isUpdate = $this->route()->hasParameter('employer');

        return [
            'name' => $isUpdate ? 'required' : 'required|unique:employers,name',
            'address' => 'string',
            'owner' => 'bail|array|required',
            'owner.firstname' => 'required',
            'owner.lastname' => 'required',
            'owner.email' => 'required|email',
            'owner.contact_number' => 'required',
            'generate_password' => ''
        ];
    }

    public function messages()
    {
        return [
            'owner.firstname.required' => 'Please add administrators first name',
            'owner.lastname.required' => 'Please add administrators last name',
            'owner.email.required' => 'Please add administrators email address',
            'owner.contact_number.required' => 'Please add administrators contact number',
        ];
    }
}
