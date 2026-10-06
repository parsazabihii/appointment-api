<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; use App\Http\Requests\StoreServiceRequest; use App\Models\Service; use App\Support\ApiPresenter; use Illuminate\Http\JsonResponse;
class AdminServiceController extends Controller { public function store(StoreServiceRequest $request): JsonResponse {$s=Service::create($request->validated());return response()->json(['data'=>ApiPresenter::service($s)],201);} public function update(StoreServiceRequest $request,Service $service): JsonResponse {$service->update($request->validated());return response()->json(['data'=>ApiPresenter::service($service->fresh())]);} public function destroy(Service $service): JsonResponse {$service->update(['is_active'=>false]);return response()->json(['message'=>'Service deactivated.']);} }
