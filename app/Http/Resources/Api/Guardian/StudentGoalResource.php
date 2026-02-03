<?php

namespace App\Http\Resources\Api\Guardian;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentGoalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'target_value' => $this->target_value,
            'current_value' => $this->current_value,
            'target_date' => $this->target_date?->format('Y-m-d'),
            'progress' => $this->progress,
            'status' => $this->status,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
