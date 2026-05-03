<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Client extends Model
{
    protected $fillable = [
        'name',
        'code',
    ];

     public function ExcelForm(){
        return $this->hasMany(ExcelForm::class);
    }
}
