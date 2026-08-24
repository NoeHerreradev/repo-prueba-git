<?php

namespace App\Models;

use App\Enums\CommissionRole;
use App\Enums\CommissionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['contract_id', 'user_id', 'role', 'percent', 'amount', 'status', 'paid_at'])]
class Commission extends Model
{
    protected function casts(): array
    {
        return [
            'role' => CommissionRole::class,
            'status' => CommissionStatus::class,
            'percent' => 'decimal:2',
            'amount' => 'decimal:2',
            'paid_at' => 'date',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
