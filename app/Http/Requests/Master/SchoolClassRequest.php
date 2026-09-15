<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class SchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('classes.manage');
    }

    public function rules(): array
    {
        $classId = $this->route('class')?->id;

        return [
            'major_id' => ['required', 'exists:majors,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'name' => [
                'required', 
                'string', 
                'max:50', 
                \Illuminate\Validation\Rule::unique('school_classes')->where(function ($query) {
                    return $query->where('academic_year_id', $this->academic_year_id);
                })->ignore($classId)
            ],
            'grade' => ['required', 'in:X,XI,XII'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'is_active' => ['boolean'],
        ];
    }
}
