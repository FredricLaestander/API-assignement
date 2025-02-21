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
                'model' => 'mistral',
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

            $session_id = $request->session_id;

            if ($session_id === null) {
                $session_id = Str::uuid4();
            }

            ChatHistory::where("session_id", $session_id);

            $response = Http::post('http://localhost:11434/api/generate', [
                'model' => 'mistral',
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
}
