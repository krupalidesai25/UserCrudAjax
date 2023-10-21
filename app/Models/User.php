<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $appends = ['profile_path'];
    public $disk_name = 'profile_pics';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'contact_no',
        'category_id',
        'profile_pic'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    // protected $hidden = [
    //     'password',
    //     'remember_token',
    // ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    //     'password' => 'hashed',
    // ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function hobbies()
    {
        return $this->belongsToMany(Hobby::class, 'user_hobbies')->withTimestamps()->withPivot('id');
    }

    public function getProfilePathAttribute()
    {
        return ($this->profile_pic && Storage::disk($this->disk_name)->exists($this->profile_pic)) ? Storage::disk($this->disk_name)->url($this->profile_pic) : asset('storage/assets/img/default/user.png');
    }

}
