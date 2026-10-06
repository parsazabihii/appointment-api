<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; use App\Http\Requests\FakeWebhookRequest; use App\Services\PaymentService; use Illuminate\Http\JsonResponse;
class PaymentWebhookController extends Controller { public function fake(FakeWebhookRequest $request,PaymentService $payments): JsonResponse {$result=$payments->handleFakeWebhook($request->validated(),(string)$request->header('X-Webhook-Secret'));return response()->json(['data'=>$result]);} }
