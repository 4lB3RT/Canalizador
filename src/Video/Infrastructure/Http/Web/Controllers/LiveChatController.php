<?php

namespace Canalizador\Video\Infrastructure\Http\Web\Controllers;

use Canalizador\Video\Infrastructure\Agents\AudioTranscriptor;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LiveChatController extends Controller
{
    public function index(): View
    {
        return view('livechat');
    }

    public function send(Request $request)
    {
        $message = $request->input('message');
        session(['last_message' => $message]);
        return response()->json(['status' => 'ok']);
    }

    public function stream(AudioTranscriptor $audioTranscriptor): StreamedResponse
    {
        $message = session('last_message', '');
        session()->forget('last_message');
        return response()->stream(function () use ($audioTranscriptor, $message) {
            if ($message !== '') {
                echo 'data: ' . json_encode(['message' => $audioTranscriptor->execute($message)->asStream()->text]) . "\n\n";
                ob_flush();
                flush();
            }
        }, 200, [
            'Content-Type'  => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection'    => 'keep-alive',
        ]);
    }
}
