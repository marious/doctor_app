<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UploadMedicalReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Modules\Core\Resources\MediaResource;

class UploadMedicalReportController extends Controller
{
    public function uploadReport(UploadMedicalReportRequest $request): JsonResponse
    {
        $user = $request->user();
        $type = $request->validated('type');

        try {
            $media = $user
                ->addMedia($request->file('file'))
                ->usingName(Str::slug(pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME)))
                ->toMediaCollection($type);

            return response()->json([
                'status' => true,
                'message' => __('Report uploaded successfully.'),
                'data' => MediaResource::make($media),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => __('Failed to upload report'),
            ], 500);
        }
    }
}
