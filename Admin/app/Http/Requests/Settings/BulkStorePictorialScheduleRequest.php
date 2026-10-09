<?php

namespace App\Http\Requests\Settings;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;

class BulkStorePictorialScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission(
            'manage-pictorial-schedules'
        );
    }

    public function rules(): array
    {
        return [
            'date_from' => [
                'required',
                'date',
            ],

            'date_to' => [
                'required',
                'date',
                'after_or_equal:date_from',
            ],

            'daily_start_time' => [
                'required',
                'date_format:H:i',
            ],

            'daily_end_time' => [
                'required',
                'date_format:H:i',
                'after:daily_start_time',
            ],

            'slot_duration_minutes' => [
                'required',
                'integer',
                'min:5',
                'max:480',
            ],

            'no_of_slots' => [
                'required',
                'integer',
                'min:1',
                'max:500',
            ],

            'include_saturday' => [
                'required',
                'boolean',
            ],

            'include_sunday' => [
                'required',
                'boolean',
            ],

            'is_delayed' => [
                'required',
                'boolean',
            ],

            'college_id' => [
                'nullable',
                'integer',
                'exists:colleges,id',
            ],

            'allowed_college_ids' => [
                'nullable',
                'array',
            ],

            'allowed_college_ids.*' => [
                'integer',
                'distinct',
                'exists:colleges,id',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $isDelayed = $this->boolean('is_delayed');

            if (
                !$isDelayed &&
                !$this->filled('college_id')
            ) {
                $validator->errors()->add(
                    'college_id',
                    'Select a college for regular pictorial schedules.'
                );
            }

            if (
                $isDelayed &&
                empty($this->input('allowed_college_ids', []))
            ) {
                $validator->errors()->add(
                    'allowed_college_ids',
                    'Select at least one college for delayed pictorial schedules.'
                );
            }
        });
    }

    protected function failedValidation($validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'The provided bulk schedule information is invalid.',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' =>
                    'You do not have permission to manage pictorial schedules.',
            ], 403)
        );
    }
}