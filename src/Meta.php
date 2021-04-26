<?php

namespace DanieleFavi\Meta;

use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
    
    /**
     * List of fields that can be automatically filled.
     *
     * @var array
     */
    protected $fillable = [
        'key', 'value'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'value' => 'json'
    ];
}
