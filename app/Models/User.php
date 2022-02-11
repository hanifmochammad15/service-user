<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'user_data';

    public $timestamps = false;

    protected $fillable = [
        'username',
        'email',
        'password',
        'nama_lengkap',
        'jabatan',
        'telepon',
        'uker_main',
        'uker_branch',
        'uker',
        'level_id',
        'active_status',
        'ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getLevelId($username)
    {
        $list_level = DB::table('user_data')
            ->join('user_level', 'user_data.level_id', '=', 'user_level.level_id')
            ->select('user_level.level_id', 'user_level.desc')
            ->where('username',$username)
            ->groupBy('user_level.level_id')
            ->get();
        return $list_level;
    }

}
