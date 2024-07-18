<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "payment_type" => $this->payment_type->name,
            "account_name" => $this->account_name,
            "account_no" => $this->account_no,
            "amount" => $this->amount,
            "status" => $this->status === 0 ? "Pending" : $this->status === 1 ? "Success" : "Reject",
            "message" => $this->message,    
            "datetime" => $this->created_at->format("Y-m-d H:i:s")
        ];
    }
}
