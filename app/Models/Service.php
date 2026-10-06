<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsToMany; use Illuminate\Database\Eloquent\Relations\HasMany;
class Service extends Model { protected $fillable=['name','description','duration_minutes','is_active']; protected function casts(): array { return ['is_active'=>'boolean','duration_minutes'=>'integer']; } public function providers(): BelongsToMany { return $this->belongsToMany(Provider::class)->withPivot(['price','is_active'])->withTimestamps(); } public function appointments(): HasMany { return $this->hasMany(Appointment::class); } }
