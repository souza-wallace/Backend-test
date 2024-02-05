<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Redirect extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['code'];
    protected $primaryKey = 'code';
    protected $dates = ['deleted_at'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($redirect) {
            if (!$redirect->id) {
                $redirect->id = static::max('id') + 1;
            }
            $redirect->code = Hashids::encode($redirect->id, rand(0, 10));
        });
    }

    // public function logs()
    // {
    //     return $this->hasMany(RedirectLog::class);
    //     return $this->hasMany(RedirectLog::class, 'code', 'code');

    // }
}
