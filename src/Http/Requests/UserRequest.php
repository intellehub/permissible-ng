<?php

namespace Shahnewaz\PermissibleNg\Http\Requests;

use App\User;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        $userId = $this->request->get('id');

        $rules = [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,'.$userId,
        ];

        if(!$userId){
            $rules['password'] = 'required|min:6|confirmed';
            $rules['role'] = 'required|integer';
        } else {
            $rules['role'] = 'nullable|sometimes|integer';
        }
        
        return $rules;
    }
}
