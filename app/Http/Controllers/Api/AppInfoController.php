<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AppInfoController extends Controller
{
    /**
     * Get all general app information, HTML pages, and support details.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'App information retrieved successfully',
            'data' => [
                'version' => 'Version 1.0.0 (' . date('Y') . ')',
                // Usually HTML / Rich Text
                'privacy_policy' => '<h4>Privacy Policy</h4><p>This is a dummy privacy policy. Your data is safe with us.</p><ul><li>We collect email.</li><li>We collect usage data.</li></ul>',
                'terms_conditions' => '<h4>Terms & Conditions</h4><p>Welcome to our application. By using this app, you agree to...</p><p><b>1. Usage:</b> Do not misuse.</p>',
                'data_permissions' => '<h5>Data Permissions</h5><p>We require the following permissions:</p><ul><li><b>Camera:</b> To upload your avatar.</li><li><b>Storage:</b> To save documents.</li></ul>',
                'privacy_security' => '<h5>Privacy and Security</h5><p>We use industry-standard encryption to protect your records.</p>',
                
                // FAQ can be HTML or structured array
                'faq' => [
                    [
                        'question' => 'How do I reset my password?',
                        'answer' => '<p>Go to the profile settings and click on <b>Change Password</b>.</p>'
                    ],
                    [
                        'question' => 'How can I contact a doctor?',
                        'answer' => '<p>You can use the <b>Book Appointment</b> feature from the home screen.</p>'
                    ]
                ],

                // Help and Contact are usually structured data, but can contain HTML text
                'help_support' => '<p>If you have any issues, please refer to our FAQ or contact our support team directly. We are here to help!</p>',
                
                'contact_support' => [
                    'email' => 'support@doctorapp.test',
                    'phone' => '+1234567890',
                    'address' => '123 Health Ave, Medical City, MC 1001'
                ]
            ]
        ]);
    }
}
