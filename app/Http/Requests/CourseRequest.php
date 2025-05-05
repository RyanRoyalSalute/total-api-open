<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Adjust based on your authorization logic if needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'created_by' => 'nullable|integer',
            'updated_by' => 'nullable|integer',
            'shop_brand_id' => 'required|exists:shop_brands,id',
            'course_name' => 'required|string|max:255',
            'course_description' => 'nullable|string',
            'course_images' => 'nullable|array|max:5',
            'course_images.*' => 'string|max:255',
            'course_tab' => 'nullable|array',
            'course_tab.*' => 'string|max:255',
            'course_colors' => 'nullable|array|max:2',
            'course_colors.*' => 'string|max:7',
            'course_dates' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    if (empty($value)) return;

                    // Check if all are weekdays (1-7) or all are dates (Y-m-d)
                    $isWeekdayType = true;
                    $isDateType = true;

                    foreach ($value as $date) {
                        $isWeekday = is_numeric($date) && (int)$date >= 1 && (int)$date <= 7;
                        $isDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && \DateTime::createFromFormat('Y-m-d', $date) !== false;

                        if (!$isWeekday) $isWeekdayType = false;
                        if (!$isDate) $isDateType = false;

                        // If neither type matches, fail immediately
                        if (!$isWeekday && !$isDate) {
                            $fail("The $attribute contains an invalid value: '$date'. Each value must be either a weekday integer (1-7) or a date in Y-m-d format.");
                            return;
                        }
                    }

                    // Fail if it's a mix of both types
                    if (!$isWeekdayType && !$isDateType) {
                        $fail("The $attribute must contain either all weekday integers (1-7) or all dates in Y-m-d format, not a mix of both.");
                    }
                },
            ],
            'course_dates.*' => 'string', // Basic type check
            'course_times' => 'nullable|array',
            'course_times.*' => 'string',
            'course_price_id' => 'nullable|exists:course_prices,id',
            'material_id' => 'nullable|exists:products,id',
            'on_sale' => 'boolean',
            'sort' => 'integer|min:0',
            'pinned' => 'boolean',
            'sub_store_ids' => 'nullable|array',
            'sub_store_ids.*' => 'exists:sub_store,id',
            'teacher_ids' => 'nullable|array',
            'teacher_ids.*' => 'exists:teachers,id',
            'classroom_ids' => 'nullable|array',
            'classroom_ids.*' => 'exists:classrooms,id',
        ];

        // For update, make some fields optional
        if ($this->method() === 'PUT' || $this->method() === 'PATCH') {
            $rules['shop_brand_id'] = 'sometimes|exists:shop_brands,id';
            $rules['course_name'] = 'sometimes|string|max:255';
            $rules['sort'] = 'sometimes|integer|min:0';
            $rules['pinned'] = 'sometimes|boolean';
        }

        return $rules;
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'course_dates' => 'The course dates must contain either all weekday integers (1-7) or all dates in Y-m-d format.',
            'course_dates.*' => 'Each course date must be a string.',
        ];
    }
}