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
            'person.lastName' => 'required|string|max:255',
            'person.firstName' => 'required|string|max:255',
            'person.birthDate' => 'nullable|date',
            'person.gender' => 'nullable|in:male,female',
            'person.shift' => 'nullable|in:first,second',
            'person.parentFio' => 'required|string|max:255',
            'person.parentTel' => 'required|string|max:20',
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
            
            'person.lastName.required' => 'Фамилия обязательна для заполнения',
            'person.lastName.max' => 'Фамилия не должна превышать 255 символов',
            
            'person.firstName.required' => 'Имя обязательно для заполнения',
            'person.firstName.max' => 'Имя не должно превышать 255 символов',
            
            'person.birthDate.date' => 'Некорректный формат даты рождения',
            
            'person.gender.in' => 'Некорректное значение пола',
            
            'person.shift.in' => 'Некорректное значение смены',
            
            'person.parentFio.required' => 'ФИО родителя обязательно для заполнения',
            'person.parentFio.max' => 'ФИО родителя не должно превышать 255 символов',
            
            'person.parentTel.required' => 'Телефон родителя обязателен для заполнения',
            'person.parentTel.max' => 'Телефон родителя не должен превышать 20 символов',
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
            'person.lastName' => 'Фамилия',
            'person.firstName' => 'Имя',
            'person.birthDate' => 'Дата рождения',
            'person.gender' => 'Пол',
            'person.shift' => 'Смена',
            'person.parentFio' => 'ФИО родителя',
            'person.parentTel' => 'Телефон родителя',
        ];
    }
} 