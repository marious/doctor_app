<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Pages\Models\Page;

class AppInfoController extends Controller
{
    /**
     * GET /v1/app-info
     * Returns app version and all static page content from the database.
     */
    public function index(): JsonResponse
    {
        $pages = Page::all()->keyBy('slug');

        return response()->json([
            'status'  => true,
            'message' => 'App information retrieved successfully',
            'data'    => [
                'version'          => 'Version 1.0.0 (' . date('Y') . ')',
                'faq'              => $pages->get('faq')?->content ?? [],
                'contact_support'  => $pages->get('contact_support')?->content ?? [],
                'terms_conditions' => $pages->get('terms_conditions')?->content ?? '',
                'privacy_policy'   => $pages->get('privacy_policy')?->content ?? '',
                'privacy_security' => $pages->get('privacy_security')?->content ?? '',
                'data_permissions' => $pages->get('data_permissions')?->content ?? '',
            ],
        ]);
    }
}
