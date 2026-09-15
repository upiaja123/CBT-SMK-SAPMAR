<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class AcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('academic_years.manage');
    }

    public function rules(): array
    {
        $academicYearId = $this->route('academic_year')?->id;

        return [
            'name' => [
                'required', 
                'string', 
                'max:50', 
                \Illuminate\Validation\Rule::unique('academic_years')->ignore($academicYearId)
            ],
            'year_start' => ['required', 'integer', 'min:2020', 'max:2100'],
            'year_end' => ['required', 'integer', 'gte:year_start'],
            'is_active' => ['boolean'],
        ];
    }
}
