<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
    ];

    /* -----------------------------------------------------
     |  Relationships
     | ----------------------------------------------------- */

    /**
     * Services belonging to this category
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /* -----------------------------------------------------
     |  Model Events
     | ----------------------------------------------------- */

    protected static function booted(): void
    {
        // Auto-generate slug on create if not provided
        static::creating(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = static::generateUniqueSlug($category->name);
            }
        });

        // Update slug if name changes (optional but useful)
        static::saving(function (Category $category) {
            if ($category->isDirty('name') && ! $category->isDirty('slug')) {
                $category->slug = static::generateUniqueSlug(
                    $category->name,
                    $category->id
                );
            }
        });
    }

    /**
     * Generate a unique slug
     */
    public static function generateUniqueSlug(string $name, ?int $exceptId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /* -----------------------------------------------------
     |  Scopes
     | ----------------------------------------------------- */

    /**
     * Find category by slug
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }
}
