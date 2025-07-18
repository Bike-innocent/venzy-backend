<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingGroup extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }
}
