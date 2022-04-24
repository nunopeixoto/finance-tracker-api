<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'description' => $this->description,
            'category' => $this->category,
            'subCategory' => $this->subCategory,
            'type' => $this->debit ?  'debit' : 'credit',
            'amount' => $this->debit ? (float) number_format($this->debit, 2) : (float) number_format($this->credit, 2),
            'note' => $this->note
        ];
    }
}
