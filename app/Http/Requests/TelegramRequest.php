<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class TelegramRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'message.message_id' => 'required|integer',
            'message.from.id' => 'required|integer',
            'message.text' => 'required|string',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $response = response()->json([
            'success' => false,
            'errors' => $errors->messages(),
        ], 403);
        Log::debug($errors->messages());

        throw new HttpResponseException($response);
    }
}
