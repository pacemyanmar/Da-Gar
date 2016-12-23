<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'qnum' => 'required|alpha_num|unique_with:questions,section,sort,' . $this->route('question'),
            'question' => 'required',
            'raw_ans' => 'required',
            'section' => 'required',
            'sort' => 'required',
        ];
    }
}
