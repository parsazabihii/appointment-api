<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreAppointmentRequest extends FormRequest { public function authorize(): bool{return true;} public function rules(): array{return ['provider_id'=>['required','integer','exists:providers,id'],'service_id'=>['required','integer','exists:services,id'],'starts_at'=>['required','date','after:now']];} }
