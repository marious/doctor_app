<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UploadAvatarRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UploadAvatarController extends Controller
{
    public function uploadAvatar(UploadAvatarRequest $request)
    {
        $model = $request->user();

        try {
            $media = $model
                ->addMedia($request->file('avatar'))
                ->usingName(Str::slug(pathinfo($request->file('avatar')->getClientOriginalName(), PATHINFO_FILENAME)))
                ->toMediaCollection('avatar');
            return response()->json([
                'success' => true,
                'message' => __('Image uploaded successfully.'),
                'avatar' => [
                    'url' => $media->getUrl(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to upload image'),
            ], 500);
        }
    }
}
