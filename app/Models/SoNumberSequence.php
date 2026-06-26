<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoNumberSequence extends Model
{
    protected $table = 'so_number_sequence';

    protected $fillable = ['year', 'last_number'];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'last_number' => 'integer',
        ];
    }
}
