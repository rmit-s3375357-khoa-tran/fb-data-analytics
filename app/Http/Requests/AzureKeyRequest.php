<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class AzureKeyRequest extends Request
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
            'key1' => 'required|alpha_num|size:32',
            'key2' => 'required|alpha_num|size:32'
        ];
    }
}
