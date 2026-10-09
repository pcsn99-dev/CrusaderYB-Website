<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePictorialScheduleRequest extends FormRequest
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
            'date' => [
                'required',
                'date',
            ],

            'start_time' => [
                'required',
                'date_format:H:i',
            ],

            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],

            'no_of_slots' => [
                'required',
                'integer',
                'min:1',
                'max:500',
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

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $isDelayed = $this->boolean('is_delayed');

                if (
                    !$isDelayed &&
                    !$this->filled('college_id')
                ) {
                    $validator->errors()->add(
                        'college_id',
                        'Select a college for a regular pictorial schedule.'
                    );
                }

                if (
                    $isDelayed &&
                    empty($this->input('allowed_college_ids', []))
                ) {
                    $validator->errors()->add(
                        'allowed_college_ids',
                        'Select at least one college for a delayed pictorial schedule.'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'end_time.after' =>
                'The end time must be later than the start time.',
        ];
    }

    protected function failedValidation(
        Validator $validator
    ): void {
        throw new HttpResponseException(
            response()->json([
                'message' => 'The provided schedule information is invalid.',
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