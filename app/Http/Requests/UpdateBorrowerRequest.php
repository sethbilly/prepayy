<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBorrowerRequest extends FormRequest
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
        // On the loan application submission page, 2 submit buttons are used
        // The draft button has value 0 and the submit application button has value 1
        $isSubmit = (bool) $this->get('submit', 0);

        return [
            'user' => $isSubmit ? 'required|array' : 'sometimes|required|array',
            'user.firstname' => $isSubmit ? 'required' : 'string',
            'user.lastname' => $isSubmit ? 'required' : 'string',
            'user.othernames' => 'string',
            'user.contact_number' => $isSubmit ? 'required' : 'string',
            'user.dob' => $isSubmit ? 'required' : 'string',
            'user.country_id' => $isSubmit ? 'required' : 'bail|numeric',
            // 'address.residential' => $isSubmit ? 'required' : 'sometimes',
            'user.ssnit' => $isSubmit ? 'required' : 'string',
            // Employer verification
            'employer' => $isSubmit ? 'required|array' : 'bail|sometimes|array',
            'employer.id' => $isSubmit ? 'required' : 'numeric',
            'employer.contract_type' => $isSubmit ? 'required' : 'string',
            'employer.position' => $isSubmit ? 'required' : 'string',
            'employer.department' => 'string',
            'employer.salary' => $isSubmit ? 'required|numeric' : 'numeric',
            // Identification card verification
            'id_card' => $isSubmit ? 'required|array' : 'bail|sometimes|required|array',
            'id_card.type' => $isSubmit ? 'required' : 'string',
            'id_card.number' => $isSubmit ? 'required' : 'string',
            'id_card.issue_date' => $isSubmit ? 'required' : 'string',
            'id_card.expiry_date' => $isSubmit ? 'required' : 'string',
            // Guarantor verification
            'guarantor' => $isSubmit ? 'required|array' : 'sometimes|required|array',
            'guarantor.name' => $isSubmit ? 'required' : 'string',
            'guarantor.relationship' => $isSubmit ? 'required' : 'string',
            'guarantor.contact_number' => $isSubmit ? 'required' : 'string',
            'guarantor.years_known' => $isSubmit ? 'required' : 'string',
            'guarantor.employer' => $isSubmit ? 'required' : 'string',
            'guarantor.position' => $isSubmit ? 'required' : 'string'
        ];
    }

    public function messages()
    {
        return [
            // User custom messages
            'user.firstname.required' => 'Please enter your first name',
            'user.lastname.required' => 'Please enter your last name',
            'user.contact_number.required' => 'Please enter your contact number',
            'user.dob.required' => 'Please enter your date of birth',
            'user.country_id.required' => 'Please select a country from the list',
            'user.ssnit.required' => 'Please enter your SSNIT number',
            // Employer verification
            'employer.id.required' => 'Please select an employer',
            'employer.contract_type' => 'Please select your contract type with employer',
            'employer.position' => 'Your position at your current Employment is required',
            'employer.salary' => 'Your monthly salary is required',
            // Identification card verification
            'id_card.required' => 'Please add an id card',
            'id_card.type.required' => 'Please select the id card type you want to use',
            'id_card.number.required' => 'Please add id card number',
            'id_card.issue_date.required' => 'Please add the date the id card was issued',
            'id_card.expiry_date.required' => 'Please add an expiry date for id card',
            // Guarantor verification
            'guarantor.name.required' => 'Please add guarantors name',
            'guarantor.relationship.required' => 'Please specify your relationship with guarantor',
            'guarantor.contact_number.required' => 'Please add guarantors contact number',
            'guarantor.years_known.required' => 'Please add number of years you have known your guarantor',
            'guarantor.employer.required' => 'Please add a name for guarantor\'s employer',
            'guarantor.position.required' => 'Please add a position for your guarantor\'s current company',
        ];
    }
}
