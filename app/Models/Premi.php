<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Premi extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    // Mutator for minimal_rate
    public function setMinimalRateAttribute($value)
    {
        $this->attributes['minimal_rate'] = $value == 0 ? null : $value;
        $this->updateHasCustomFormula();
    }

    // Mutator for maksimal_rate
    public function setMaksimalRateAttribute($value)
    {
        $this->attributes['maksimal_rate'] = $value == 0 ? null : $value;
        $this->updateHasCustomFormula();
    }

    private function updateHasCustomFormula()
    {
        $minimalRate = $this->attributes['minimal_rate'] ?? null;
        $maksimalRate = $this->attributes['maksimal_rate'] ?? null;

        $this->attributes['has_custom_formula'] = !is_null($minimalRate) || !is_null($maksimalRate);
    }

    /**
     * Get all of the pengurang_gajis for the Premi
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pengurang_gajis(): HasMany
    {
        return $this->hasMany(PengurangGaji::class, 'premi_id', 'id');
    }
}
