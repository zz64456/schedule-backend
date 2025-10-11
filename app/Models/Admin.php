<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class Admin extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'username',
        'password',
        'name',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * 自動將密碼進行 Hash
     */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * 驗證密碼
     */
    public function checkPassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }
}
