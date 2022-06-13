<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(Request $request): array
    {
        Log::debug($request);
        $callBackQueryPrefix = '';
        if ($request->has('callback_query')) {
            $callBackQueryPrefix = 'callback_query.';
        }
        return [
            $callBackQueryPrefix . 'message.message_id' => 'required|integer',
            $callBackQueryPrefix. 'message.chat.id' => 'required|integer',
            $callBackQueryPrefix . 'message.text' => 'required|string',
        ];
    }

    /**
     * @param Validator $validator
     * @return void
     */
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
