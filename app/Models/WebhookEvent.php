<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WebhookEvent extends Model { protected $fillable=['event_id','provider','type','payload','processed_at']; protected function casts(): array { return ['payload'=>'array','processed_at'=>'immutable_datetime']; } }
