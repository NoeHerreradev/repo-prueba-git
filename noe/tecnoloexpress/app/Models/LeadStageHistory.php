<?php

namespace App\Models;

use App\Enums\LeadStage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['lead_id', 'from_stage', 'to_stage', 'user_id', 'changed_at'])]
class LeadStageHistory extends Model
{
    protected function casts(): array
    {
        return [
            'from_stage' => LeadStage::class,
            'to_stage' => LeadStage::class,
            'changed_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
