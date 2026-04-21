<?php

namespace App\Http\Requests;

class UpdateOrderRequest extends StoreOrderRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'status' => 'nullable|in:quote,ordered,shipping,completed,cancelled',
            'shipped_date' => 'nullable|date',
        ]);
    }
}
