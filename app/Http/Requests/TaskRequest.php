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
            'name' => 'required|string|max:255',
            'topic_id' => 'required|exists:topics,id',
            'type_id' => 'required|exists:types,id',
            'content' => 'required|json',
            'due_day' => 'nullable|date'
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
            'name.required' => 'Название задачи обязательно для заполнения',
            'name.max' => 'Название задачи не должно превышать 255 символов',
            'topic_id.required' => 'Необходимо указать тему задачи',
            'topic_id.exists' => 'Указанная тема не существует',
            'type_id.required' => 'Необходимо указать тип задачи',
            'type_id.exists' => 'Указанный тип не существует',
            'content.required' => 'Содержание задачи обязательно для заполнения',
            'content.json' => 'Некорректный формат содержания',
            'due_day.date' => 'Некорректный формат даты'
        ];
    }
} 