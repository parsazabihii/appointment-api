<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreBlockedTimeRequest extends FormRequest { public function authorize(): bool{return true;} public function rules(): array{return ['starts_at'=>['required','date'],'ends_at'=>['required','date','after:starts_at'],'reason'=>['nullable','string','max:500']];} }
