<?php
namespace App\Http\Requests;
use App\Enums\UserRole; use Illuminate\Foundation\Http\FormRequest; use Illuminate\Validation\Rule; use Illuminate\Validation\Rules\Password;
class RegisterRequest extends FormRequest { public function authorize(): bool{return true;} public function rules(): array{return ['name'=>['required','string','max:120'],'email'=>['required','email','max:255','unique:users,email'],'password'=>['required','confirmed',Password::min(8)->letters()->numbers()],'role'=>['required',Rule::in([UserRole::Customer->value,UserRole::Provider->value])],'timezone'=>['nullable','required_if:role,provider','timezone']];} }
