<?php
namespace App\Exceptions;
use Exception; use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;
class WebhookException extends Exception { public function __construct(string $message, private int $status=400){parent::__construct($message);} public function render(Request $request): JsonResponse { return response()->json(['message'=>$this->getMessage()],$this->status); } }
