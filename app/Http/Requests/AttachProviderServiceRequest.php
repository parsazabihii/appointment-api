<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class AttachProviderServiceRequest extends FormRequest { public function authorize(): bool{return true;} public function rules(): array{return ['price'=>['required','numeric','min:0','max:9999999999.99'],'is_active'=>['sometimes','boolean']];} }
