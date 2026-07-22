<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class PetSpecies extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image',
        'background_color',
        'sort_order',
        'show_on_home',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'show_on_home' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}
