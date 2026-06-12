<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Chat\Models\Conversation;
use Modules\Chat\Models\Message;
use Modules\Users\Models\User;

class ChatController extends Controller
{
    public function __construct(private FcmService $fcm) {}

    /**
     * GET /v1/chat/conversations
     * Return all conversations for the patient.
     * Auto-creates one conversation per staff member (doctor + assistants) if none exist yet.
     */
    public function conversations(): JsonResponse
    {
        $patientId = Auth::id();

        // Ensure a conversation exists with every staff member
        User::whereIn('role_id', [1, 3])->each(function (User $staff) use ($patientId) {
            Conversation::firstOrCreate([
                'patient_id' => $patientId,
                'staff_id'   => $staff->getAttribute('id'),
            ]);
        });

        $conversations = Conversation::where('patient_id', $patientId)
            ->with(['staff:id,name,role_id', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $conversations->map(fn($c) => $this->formatConversation($c)),
        ]);
    }

    /**
     * GET /v1/chat/conversations/{conversation}/messages
     * Paginated message history (newest first).
     */
    public function messages(Conversation $conversation): JsonResponse
    {
        abort_if($conversation->patient_id !== Auth::id(), 403);

        $messages = $conversation->messages()
            ->with('sender:id,name,role_id')
            ->orderByDesc('created_at')
            ->paginate(300);

        return response()->json([
            'success' => true,
            'data'    => $messages->map(fn($m) => $this->formatMessage($m)),
            'meta'    => [
                'current_page' => $messages->currentPage(),
                'last_page'    => $messages->lastPage(),
                'per_page'     => $messages->perPage(),
                'total'        => $messages->total(),
            ],
        ]);
    }

    /**
     * POST /v1/chat/conversations/{conversation}/messages
     * Send a message. Multipart form-data:
     *   type: text|image|audio
     *   body: (required when type=text)
     *   file: (required when type=image or audio, max 20 MB)
     */
    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        abort_if($conversation->patient_id !== Auth::id(), 403);

        $request->validate([
            'type' => ['required', 'in:text,image,audio,file'],
            'body' => ['required_if:type,text', 'nullable', 'string', 'max:2000'],
            'file' => [
                'required_unless:type,text',
                'nullable',
                'file',
                'max:20480',
                'mimes:jpg,jpeg,png,gif,webp,mp3,m4a,ogg,wav,pdf,doc,docx,xls,xlsx',
            ],
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $ext      = $request->file('file')->getClientOriginalExtension();
            $filePath = $request->file('file')->storeAs(
                'chat/' . $conversation->id,
                Str::uuid() . '.' . $ext,
                'public'
            );
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => Auth::id(),
            'type'            => $request->input('type'),
            'body'            => $request->input('body'),
            'file_path'       => $filePath,
        ]);

        $conversation->update(['last_message_at' => now()]);

        $preview = match($request->input('type')) {
            'text'  => $request->input('body'),
            'image' => 'Sent an image',
            'audio' => 'Sent a voice message',
            'file'  => 'Sent a file',
            default => 'New message',
        };
        $this->fcm->sendChatMessage($conversation->staff, Auth::user()->name, $preview, $conversation->id);

        return response()->json([
            'success' => true,
            'data'    => $this->formatMessage($message->load('sender:id,name,role_id')),
        ], 201);
    }

    /**
     * POST /v1/chat/conversations/{conversation}/read
     * Mark all incoming messages in this conversation as read.
     */
    public function markRead(Conversation $conversation): JsonResponse
    {
        abort_if($conversation->patient_id !== Auth::id(), 403);

        Message::where('conversation_id', $conversation->id)
            ->whereNot('sender_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    // ─── Private ──────────────────────────────────────────────────────────────

    private function formatConversation(Conversation $c): array
    {
        return [
            'id'              => $c->id,
            'staff'           => [
                'id'   => $c->staff?->id,
                'name' => $c->staff?->name,
                'role' => $c->staff?->role_id === 1 ? 'doctor' : 'assistant',
            ],
            'last_message'    => $c->latestMessage ? $this->formatMessage($c->latestMessage) : null,
            'last_message_at' => $c->last_message_at?->toIso8601String(),
        ];
    }

    private function formatMessage(Message $m): array
    {
        return [
            'id'          => $m->id,
            'sender_id'   => $m->sender_id,
            'sender_name' => $m->sender?->name,
            'is_mine'     => $m->sender_id === Auth::id(),
            'type'        => $m->type,
            'body'        => $m->body,
            'file_url'    => $m->file_url,
            'read_at'     => $m->read_at?->toIso8601String(),
            'created_at'  => $m->created_at->toIso8601String(),
        ];
    }
}
