<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAdditionalDocumentRequest extends FormRequest
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
            'response' => $this->has('files') ? 'sometimes|string' : 'required',
            'files' => $this->has('response') ? 'sometimes|required|array' : 'bail|required|array',
            // Max file size is 2MB = 2048KB
            'files.*' => 'sometimes|max:2048'
        ];
    }

    public function messages()
    {
        return [
            'response.required' => 'Please add a comment',
            'files.required' => 'Please upload a file/s'
        ];
    }
}
