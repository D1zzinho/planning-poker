<?php

namespace App\Http\Requests\Estimation;

use App\Models\Estimation;
use Illuminate\Foundation\Http\FormRequest;

class StoreEstimationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('startEstimation', Estimation::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'game_id' => 'required|numeric',
            'task' => 'required|min:3|max:30'
        ];
    }
}
