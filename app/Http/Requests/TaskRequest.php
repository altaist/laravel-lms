<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'nullable|date',
            'status' => 'required|string|in:pending,in_progress,completed',
            'priority' => 'required|integer|between:1,5',
            'user_id' => 'required|exists:users,id',
            'project_id' => 'nullable|exists:projects,id'
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            // Делаем все поля необязательными при обновлении
            return collect($rules)->mapWithKeys(function ($rule, $field) {
                return [$field => str_replace('required|', '', $rule)];
            })->toArray();
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Название задачи обязательно для заполнения',
            'title.max' => 'Название задачи не должно превышать 255 символов',
            'description.required' => 'Описание задачи обязательно для заполнения',
            'due_date.date' => 'Некорректный формат даты',
            'status.required' => 'Статус задачи обязателен для заполнения',
            'status.in' => 'Недопустимый статус задачи',
            'priority.required' => 'Приоритет задачи обязателен для заполнения',
            'priority.between' => 'Приоритет должен быть от 1 до 5',
            'user_id.required' => 'Необходимо указать исполнителя задачи',
            'user_id.exists' => 'Указанный пользователь не существует',
            'project_id.exists' => 'Указанный проект не существует'
        ];
    }
} 