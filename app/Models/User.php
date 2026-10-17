<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
class User extends Authenticatable {
 use HasFactory, Notifiable;
 protected $fillable=['name','email','password','role','company_id'];
 protected $hidden=['password','remember_token'];
 protected function casts(): array { return ['email_verified_at'=>'datetime','password'=>'hashed']; }
 public function company(): BelongsTo { return $this->belongsTo(Company::class); }
 public function shortUrls(): HasMany { return $this->hasMany(ShortUrl::class,'created_by'); }
 public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
 public function isAdmin(): bool { return $this->role === 'admin'; }
 public function isMember(): bool { return $this->role === 'member'; }
}
