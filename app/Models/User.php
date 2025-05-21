<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'mobile_phone',
        'line_id',
        'avatar',
        'name',
        'gender',
        'birth_date',
        'created_by',
        'updated_by',
        'shop_brand_id',
        'line_auth_code',
        'country_calling_code',
        'last_visited_at',
        'current_points',
        'status',
        'latest_payment_record_id',
        'latest_point_record_id',
        'permission',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_visited_at' => 'datetime',
        'birth_date' => 'date',
        'current_points' => 'integer',
        'status' => 'integer',
        'permission' => 'integer',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    public function hasPermission($permissionCode)
    {
        return $this->permissions()->where('permission_code', $permissionCode)->exists();
    }

    public function findForPassport($username)
    {
        return $this->where('mobile_phone', $username)->orWhere('email', $username)->first();
    }

    public function validateForPassportPasswordGrant($password)
    {
        return Hash::check($password, $this->password);
    }
}