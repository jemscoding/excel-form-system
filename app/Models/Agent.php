<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $fillable = [
        'name',
    ];

    public function ExcelForm(){
        return $this->hasMany(ExcelForm::class);
    }
}
