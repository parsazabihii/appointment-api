<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreServiceRequest extends FormRequest { public function authorize(): bool{return true;} public function rules(): array{return ['name'=>['required','string','max:160'],'description'=>['nullable','string','max:3000'],'duration_minutes'=>['required','integer','min:5','max:480'],'is_active'=>['sometimes','boolean']];} }
