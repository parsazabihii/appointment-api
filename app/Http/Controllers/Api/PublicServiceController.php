<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; use App\Models\Service; use App\Support\ApiPresenter; use Illuminate\Http\JsonResponse;
class PublicServiceController extends Controller { public function index(): JsonResponse { return response()->json(['data'=>Service::where('is_active',true)->orderBy('name')->get()->map(fn($s)=>ApiPresenter::service($s))->values()]); } }
