<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class RedirectLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'redirect_id', 'ip_address', 'user_agent', 'referer', 'query_params', 'access_time',
    ];

    public static function createLog($id, $data){
        self::create([
            'redirect_id' => $id,
            'ip_address' => $data['ip_address'],
            'user_agent' => $data['user_agent'],
            'referer' => $data['referer'],
            'query_params' => $data['query_params'],
            'access_time' => Carbon::now()
        ]);

    }

    public function redirect()
    {
        return $this->belongsTo(Redirect::class);
    }
}
