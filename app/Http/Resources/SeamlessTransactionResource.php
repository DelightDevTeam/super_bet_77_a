<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeamlessTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        for(i=0; i < count($this->data); i++){
            return $this->data[i]->id;
        }
        return [
            "id" => $this->id,
            "from_date" => $this->from_date,
            "to_date" => $this->to_date,
            "product" => $this->product_name,
            "total_count" => $this->total_count,
            "total_bet_amount" => $this->total_bet_amount,
            "total_transaction_amount" => $this->total_payout_amount - $this->total_bet_amount,
        ];
    }
}
