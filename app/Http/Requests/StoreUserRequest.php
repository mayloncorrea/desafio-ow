<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUserRequest extends FormRequest
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
        $date = new \DateTime();
        $date->modify('- 18 years');

        return [
            'name' => 'required|string|min:3',
            'email' => 'required|email|min:6|unique:users',
            'birthday' => "required|date|before:{$date->format('Y-m-d')}",
        ];
    }

    public function messages()
    {
        return [
            'birthday.before' => 'O usuário deve ter mais de 18 anos.'
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
