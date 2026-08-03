<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'station_id',
        'slug',
    ];

    public function lgas()
    {
        return $this->hasMany(Lga::class);
    }

    /**
     * @deprecated GIG Logistics integration was retired; the gig_logistics
     * table is empty. Kept as an alias for lgas() so any remaining callers
     * resolve to real district data instead of silently returning nothing.
     */
    public function gigLogistics()
    {
        return $this->lgas();
    }

    /**
     * @deprecated Alias for lgas() — see gigLogistics() above.
     */
    public function cities()
    {
        return $this->lgas();
    }

    /**
     * Resolve a state-level URL slug (e.g. "akwa-ibom") back to the full name
     * stored on adverts.state (e.g. "Akwa Ibom"). Needed because that column
     * holds the raw display name — multi-word states can never match their
     * own hyphenated slug via a plain string comparison.
     */
    public static function nameForSlug(string $slug): ?string
    {
        return static::where('slug', $slug)->value('name');
    }
}
