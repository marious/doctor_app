<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Pages\Models\Page;

class PageController extends Controller
{
    private const VALID_SLUGS = [
        'faq',
        'contact_support',
        'terms_conditions',
        'privacy_policy',
        'privacy_security',
        'data_permissions',
    ];

    /**
     * POST /v1/doctor/pages/{slug}
     * Update the content of a static page.
     * Body: { content: string|array }
     */
    public function update(Request $request, string $slug): JsonResponse
    {
        abort_if(! in_array($slug, self::VALID_SLUGS), 404, 'Page not found.');

        $request->validate([
            'content' => ['required'],
        ]);

        $content = $request->input('content');

        Page::updateOrCreate(
            ['slug' => $slug],
            ['content' => is_array($content) ? json_encode($content) : $content]
        );

        return response()->json(['success' => true]);
    }
}
