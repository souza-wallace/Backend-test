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

        static::created(function ($redirect) {
            $redirect->update(['code' => Hashids::encode($redirect->id, 10)]);
        });
    }

    public function redirectLogs()
    {
        return $this->hasMany(RedirectLog::class);
    }
}
