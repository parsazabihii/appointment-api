<?php
namespace App\Models;
use App\Enums\PaymentProvider; use App\Enums\PaymentStatus; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Payment extends Model { protected $fillable=['appointment_id','provider','amount','currency','status','external_reference','paid_at','refunded_at']; protected function casts(): array { return ['amount'=>'decimal:2','status'=>PaymentStatus::class,'provider'=>PaymentProvider::class,'paid_at'=>'immutable_datetime','refunded_at'=>'immutable_datetime']; } public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); } }
