<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Modules\Chat\Models\Conversation;
use Modules\Chat\Models\Message;
use Modules\Users\Models\User;

class DummyChatController extends Controller
{
    /**
     * POST /v1/{patient}/dummy-chat
     * Seeds a realistic conversation between the patient and the clinic doctor.
     */
    public function __invoke(User $patient): JsonResponse
    {
        abort_if($patient->role_id !== 2, 404);

        $doctor = User::where('role_id', 1)->firstOrFail();

        $conversation = Conversation::firstOrCreate([
            'patient_id' => $patient->id,
            'staff_id'   => $doctor->id,
        ]);

        // Clear old dummy messages so we can re-seed cleanly
        $conversation->messages()->delete();

        $now = Carbon::now();

        $messages = [
            [
                'sender'     => 'doctor',
                'type'       => 'text',
                'body'       => 'Hello! How are you feeling today?',
                'created_at' => $now->copy()->subMinutes(60),
            ],
            [
                'sender'     => 'patient',
                'type'       => 'text',
                'body'       => 'Hi doctor, I have been having some lower back pain since yesterday.',
                'created_at' => $now->copy()->subMinutes(58),
            ],
            [
                'sender'     => 'doctor',
                'type'       => 'text',
                'body'       => 'I see. Is the pain constant or does it come and go?',
                'created_at' => $now->copy()->subMinutes(56),
            ],
            [
                'sender'     => 'patient',
                'type'       => 'text',
                'body'       => 'It comes and goes, mostly when I stand for a long time.',
                'created_at' => $now->copy()->subMinutes(54),
            ],
            [
                'sender'     => 'doctor',
                'type'       => 'text',
                'body'       => 'That is common during this stage. Make sure to rest and avoid standing for more than 20 minutes at a time.',
                'created_at' => $now->copy()->subMinutes(52),
            ],
            [
                'sender'     => 'patient',
                'type'       => 'text',
                'body'       => 'Okay, I will. Also I wanted to share the ultrasound result I got today.',
                'created_at' => $now->copy()->subMinutes(30),
            ],
            [
                'sender'     => 'patient',
                'type'       => 'image',
                'body'       => null,
                'file_path'  => null, // no real file in dummy data
                'created_at' => $now->copy()->subMinutes(29),
            ],
            [
                'sender'     => 'doctor',
                'type'       => 'text',
                'body'       => 'Thank you for sharing. The results look normal. Baby is in a good position.',
                'created_at' => $now->copy()->subMinutes(20),
            ],
            [
                'sender'     => 'patient',
                'type'       => 'text',
                'body'       => 'That is such a relief! Should I come in for a follow-up?',
                'created_at' => $now->copy()->subMinutes(18),
            ],
            [
                'sender'     => 'doctor',
                'type'       => 'text',
                'body'       => 'Yes, please book an appointment for next week. We will do a full check-up then.',
                'created_at' => $now->copy()->subMinutes(15),
            ],
            [
                'sender'     => 'patient',
                'type'       => 'text',
                'body'       => 'Perfect, I will book it now. Thank you doctor!',
                'created_at' => $now->copy()->subMinutes(5),
            ],
            [
                'sender'     => 'doctor',
                'type'       => 'text',
                'body'       => 'You are welcome. Take care and stay rested.',
                'created_at' => $now->copy()->subMinutes(3),
            ],
        ];

        foreach ($messages as $msg) {
            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id'       => $msg['sender'] === 'doctor' ? $doctor->id : $patient->id,
                'type'            => $msg['type'],
                'body'            => $msg['body'] ?? null,
                'file_path'       => $msg['file_path'] ?? null,
                'read_at'         => $msg['sender'] === 'doctor' ? $msg['created_at'] : null,
                'created_at'      => $msg['created_at'],
                'updated_at'      => $msg['created_at'],
            ]);
        }

        $conversation->update(['last_message_at' => $now->copy()->subMinutes(3)]);

        // Return same shape as GET /chat/conversations/{id}/messages
        $allMessages = $conversation->messages()
            ->with('sender:id,name,role_id')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success'         => true,
            'conversation_id' => $conversation->id,
            'data'            => $allMessages->map(fn($m) => [
                'id'          => $m->id,
                'sender_id'   => $m->sender_id,
                'sender_name' => $m->sender?->name,
                'is_patient'  => $m->sender_id === $patient->id,
                'type'        => $m->type,
                'body'        => $m->body,
                'file_url'    => $m->file_url,
                'read_at'     => $m->read_at?->toIso8601String(),
                'created_at'  => $m->created_at->toIso8601String(),
            ]),
        ]);
    }
}
