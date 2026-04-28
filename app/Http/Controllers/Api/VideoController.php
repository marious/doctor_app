<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreVideoRequest;
use App\Http\Requests\Api\UpdateVideoRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Videos\Models\Video;
use Modules\Videos\Resources\VideoResource;

class VideoController extends Controller
{
    /**
     * List videos, optionally filtered by target audience.
     */
    public function index(Request $request): JsonResponse
    {
        $audience = $request->query('audience');

        $videos = Video::byNewest()
            ->when($audience, fn($q) => $q->forAudience($audience))
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => VideoResource::collection($videos),
            'meta'    => [
                'current_page' => $videos->currentPage(),
                'last_page'    => $videos->lastPage(),
                'total'        => $videos->total(),
            ],
        ]);
    }

    /**
     * Show a single video.
     */
    public function show(Video $video): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => new VideoResource($video->load('author')),
        ]);
    }

    /**
     * Create a new video (doctor/admin only).
     */
    public function store(StoreVideoRequest $request): JsonResponse
    {
        $video = Video::create([
            ...$request->validated(),
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Video added successfully.'),
            'data'    => new VideoResource($video->load('author')),
        ], 201);
    }

    /**
     * Update an existing video (doctor/admin only).
     */
    public function update(UpdateVideoRequest $request, Video $video): JsonResponse
    {
        $video->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => __('Video updated successfully.'),
            'data'    => new VideoResource($video->load('author')),
        ]);
    }

    /**
     * Delete a video (doctor/admin only).
     */
    public function destroy(Video $video): JsonResponse
    {
        $video->delete();

        return response()->json([
            'success' => true,
            'message' => __('Video deleted successfully.'),
        ]);
    }
}
