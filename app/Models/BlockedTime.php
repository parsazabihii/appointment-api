<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class BlockedTime extends Model { protected $fillable=['provider_id','starts_at','ends_at','reason']; protected function casts(): array { return ['starts_at'=>'immutable_datetime','ends_at'=>'immutable_datetime']; } public function provider(): BelongsTo { return $this->belongsTo(Provider::class); } }
