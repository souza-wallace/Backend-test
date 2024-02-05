<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class RedirectLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'redirect_id', 'ip_address', 'user_agent', 'referer', 'query_params', 'access_time', 'redirect_code',
    ];

    public static function createLog($id, $code, $data){
        self::create([
            'redirect_id' => $id,
            'redirect_code' => $code,
            'ip_address' => $data['ip_address'],
            'user_agent' => $data['user_agent'],
            'referer' => $data['referer'],
            'query_params' => json_encode($data['query_params_request']),
            'access_time' => Carbon::now()
        ]);

    }

    public function redirect()
    {
        return $this->belongsTo(Redirect::class);
    }
}
