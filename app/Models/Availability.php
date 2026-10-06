<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Availability extends Model { protected $fillable=['provider_id','day_of_week','start_time','end_time']; protected function casts(): array { return ['day_of_week'=>'integer']; } public function provider(): BelongsTo { return $this->belongsTo(Provider::class); } }
