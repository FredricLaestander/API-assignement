<?php

namespace App\Http\Controllers;

use App\Models\ChatHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        try {
            $request->validate([
                "message" => "required",
            ]);

            $response = Http::post('http://localhost:11434/api/generate', [
                'model' => 'tinyllama',
                'prompt' => $request->message,
                'stream' => false
            ])->json();

            return response()->json(["response" => $response["response"]]);
        } catch (\Exception $e) {
            dump($e);

            return response()->json([
                "message" => "Something went wrong... try again in a few minutes."
            ]);
        }
    }

    public function chat_auth(Request $request)
    {
        try {
            $request->validate([
                "message" => "required",
                "session_id" => "nullable|uuid",
            ]);

            $user = $request->user();
            $session_id = $request->session_id;

            if ($session_id === null) {
                $session_id = Str::uuid();
            }

            $history = ChatHistory::where([
                "session_id" => $session_id,
                "user_id" => $user->id
            ])
                ->latest()
                ->get()
                ->map(fn($chat) => [
                    ['role' => 'user', 'content' => $chat->user_message],
                    ['role' => 'assistant', 'content' => $chat->bot_response],
                ])
                ->flatten(1)
                ->toArray();

            $messages = array_merge($history, [
                ['role' => 'user', 'content' => $request->message]
            ]);

            $response = Http::post('http://localhost:11434/api/chat', [
                'model' => 'tinyllama',
                'messages' => $messages,
                'stream' => false,
            ])->json();

            $assistant_response = $response["message"]["content"];

            ChatHistory::create([
                "user_id" => $user->id,
                "session_id" => $session_id,
                "user_message" => $request->message,
                "bot_response" => $assistant_response,
            ]);

            return response()->json([
                "response" => $assistant_response,
                "session_id" => $session_id
            ]);
        } catch (\Exception $e) {
            dump($e);

            return response()->json([
                "message" => "Something went wrong... try again in a few minutes."
            ]);
        }
    }
}
