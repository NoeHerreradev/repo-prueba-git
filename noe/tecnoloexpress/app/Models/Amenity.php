<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'icon'])]
class Amenity extends Model
{
    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class);
    }
}
