<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ExportTransactionsRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'user_id' => 'required|integer',
            'filter' => 'required_without:date|in:all,last_thirty_days',
            'date' => 'required_without:filter|date_format:m/y'
        ];
    }

    public function messages()
    {
        return [
            'date.date_format' => 'O campo data não corresponde ao formato m/y. Ex.: 06/22'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Erros de validação',
            'data'      => $validator->errors()
        ]));
    }
}
