<?php

namespace App\Http\Requests;

class UpdateQuizRequest extends StoreQuizRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
