<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->post('user_id');
        return [
            'name' => 'nullable|string|max:255',
            'email' => $userId 
                ? 'nullable|email|max:255'
                : 'nullable|email|max:255|unique:users,email',
            'teamId' => 'nullable|exists:teams,id',
            'person.last_name' => 'required|string|max:255',
            'person.first_name' => 'required|string|max:255',
            'person.birth_date' => 'nullable|date',
            'person.gender' => 'nullable|in:male,female',
            'person.shift' => 'nullable|in:first,second,other',
            'person.parent_fio' => 'required|string|max:255',
            'person.parent_tel' => 'required|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Имя пользователя обязательно для заполнения',
            'name.max' => 'Имя пользователя не должно превышать 255 символов',
            
            'email.required' => 'Email обязателен для заполнения',
            'email.email' => 'Введите корректный email адрес',
            'email.max' => 'Email не должен превышать 255 символов',
            'email.unique' => 'Пользователь с таким email уже существует',
            
            'teamId.exists' => 'Выбранная группа не существует',
            
            'person.last_name.required' => 'Фамилия обязательна для заполнения',
            'person.last_name.max' => 'Фамилия не должна превышать 255 символов',
            
            'person.first_name.required' => 'Имя обязательно для заполнения',
            'person.first_name.max' => 'Имя не должно превышать 255 символов',
            
            'person.birth_date.date' => 'Некорректный формат даты рождения',
            
            'person.gender.in' => 'Некорректное значение пола',
            
            'person.shift.in' => 'Некорректное значение смены',
            
            'person.parent_fio.required' => 'ФИО родителя обязательно для заполнения',
            'person.parent_fio.max' => 'ФИО родителя не должно превышать 255 символов',
            
            'person.parent_tel.required' => 'Телефон родителя обязателен для заполнения',
            'person.parent_tel.max' => 'Телефон родителя не должен превышать 20 символов',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Имя пользователя',
            'email' => 'Email',
            'teamId' => 'Группа',
            'person.last_name' => 'Фамилия',
            'person.first_name' => 'Имя',
            'person.birth_date' => 'Дата рождения',
            'person.gender' => 'Пол',
            'person.shift' => 'Смена',
            'person.parent_fio' => 'ФИО родителя',
            'person.parent_tel' => 'Телефон родителя',
        ];
    }
} 