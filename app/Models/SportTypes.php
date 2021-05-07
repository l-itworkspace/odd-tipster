<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SportTypes extends Model
{
    use HasFactory;

    const TYPE_SPORT    = 1;
    const TYPE_CATEGORY = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'active',
        'provider_slug',
        'parent_id',
        'type'
    ];
}
