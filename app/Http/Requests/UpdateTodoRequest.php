<?php

namespace App\Http\Requests;

use App\Enum\Status;
use App\Enum\Priority;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTodoRequest extends FormRequest
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
            'title'         => ['sometimes', 'string', 'max:1024'],
            'description'   => ['sometimes', 'string'],
            'priority'      => ['sometimes', new Enum(Priority::class)],
            'status'        => ['sometimes', new Enum(Status::class)],
            'deadline'      => ['sometimes', 'nullable', 'after:now']
        ];
    }
}
