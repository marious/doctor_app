<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Chat\Models\Conversation;
use Modules\Chat\Models\Message;

class DoctorChatController extends Controller
{
    public function __construct(private FcmService $fcm) {}

    /**
     * GET /v1/doctor/chat/conversations
     * GET /v1/assistant/chat/conversations
     * List conversations assigned to the authenticated staff member.
     */
    public function conversations(): JsonResponse
    {
        $conversations = Conversation::where('staff_id', Auth::id())
            ->with(['patient:id,name', 'latestMessage'])
            ->withCount(['messages as unread_count' => fn($q) => $q
                ->whereNot('sender_id', Auth::id())
                ->whereNull('read_at')
            ])
            ->orderByDesc('last_message_at')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $conversations->map(fn($c) => $this->formatConversation($c)),
        ]);
    }

    /**
     * GET /v1/doctor/chat/conversations/{conversation}/messages
     * Paginated message history (newest first).
     */
    public function messages(Conversation $conversation): JsonResponse
    {
        abort_if($conversation->staff_id !== Auth::id(), 403);

        $messages = $conversation->messages()
            ->with('sender:id,name,role_id')
            ->orderByDesc('created_at')
            ->paginate(30);

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
     * POST /v1/doctor/chat/conversations/{conversation}/messages
     * Send a message to the patient.
     */
    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        abort_if($conversation->staff_id !== Auth::id(), 403);

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
        $this->fcm->sendChatMessage($conversation->patient, Auth::user()->name, $preview, $conversation->id);

        return response()->json([
            'success' => true,
            'data'    => $this->formatMessage($message->load('sender:id,name,role_id')),
        ], 201);
    }

    /**
     * POST /v1/doctor/chat/conversations/{conversation}/read
     * Mark all patient messages in this conversation as read.
     */
    public function markRead(Conversation $conversation): JsonResponse
    {
        abort_if($conversation->staff_id !== Auth::id(), 403);

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
            'patient'         => [
                'id'   => $c->patient?->id,
                'name' => $c->patient?->name,
            ],
            'last_message'    => $c->latestMessage ? $this->formatMessage($c->latestMessage) : null,
            'last_message_at' => $c->last_message_at?->toIso8601String(),
            'unread_count'    => $c->unread_count,
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
