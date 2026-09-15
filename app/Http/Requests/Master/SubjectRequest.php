<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class SubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('subjects.manage');
    }

    public function rules(): array
    {
        $subjectId = $this->route('subject')?->id;

        return [
            'code' => ['required', 'string', 'max:20', 'unique:subjects,code,'.$subjectId],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }
}
