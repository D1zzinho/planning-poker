<?php

namespace App\Http\Requests\Estimation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CloseEstimationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('closeEstimation', $this->estimation);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'points' => [
                'required',
                'numeric',
                'min:1',
                'max:13',
                Rule::in([1, 2, 3, 5, 8, 13])
            ]
        ];
    }

    /**
     * @return array Custom error messages for close estimation request.
     */
    public function messages(): array
    {
        return array_merge(
            ['points.in' => 'Incorrect story point value.'],
            parent::messages()
        );
    }
}
