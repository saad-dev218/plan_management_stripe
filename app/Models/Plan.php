<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'price', 'stripe_product_id'];

    public function features()
    {
        return $this->belongsToMany(Feature::class, 'feature_plan');
    }
}
