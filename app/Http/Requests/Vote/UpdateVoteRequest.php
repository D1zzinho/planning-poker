<?php

namespace App\Http\Requests\Vote;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('changeVote', $this->vote);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'points' => 'required|numeric|min:1|max:13'
        ];
    }
}
