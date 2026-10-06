<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest; use Illuminate\Validation\Rule;
class FakeWebhookRequest extends FormRequest { public function authorize(): bool{return true;} public function rules(): array{return ['event_id'=>['required','string','max:120'],'type'=>['required',Rule::in(['payment.succeeded','payment.failed'])],'external_reference'=>['required','string','max:255'],'appointment_id'=>['nullable','integer']];} }
