<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreAvailabilityRequest extends FormRequest { public function authorize(): bool{return true;} public function rules(): array{return ['day_of_week'=>['required','integer','between:0,6'],'start_time'=>['required','date_format:H:i'],'end_time'=>['required','date_format:H:i','after:start_time']];} }
