<?php

namespace App\Http\Controllers;

use Telegram\Bot\Api;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChannelController extends Controller
{
    protected $telegram;

    public function __construct()
    {
        $this->telegram = new Api('7638629069:AAHXEEL0410voDh_hcP7vO1fuIhLXM2A05U');
    }

    public function create()
    {
        return view('channels.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'invite_link' => 'required|url',
        ]);

        try {
            // Invite link orqali chat_id olish
            $chat = $this->telegram->getChat(['chat_id' => $request->invite_link]);
            $telegramId = $chat->getId();

            Channel::create([
                'name' => $request->name,
                'invite_link' => $request->invite_link,
                'telegram_id' => $telegramId,
            ]);

            return redirect()->route('channels.create')->with('success', 'Kanal muvaffaqiyatli qo‘shildi!');
        } catch (\Exception $e) {
            Log::error("Kanal qo‘shishda xato: {$e->getMessage()}");
            return redirect()->route('channels.create')->with('error', 'Kanal qo‘shishda xato yuz berdi. Iltimos, qaytadan urinib ko‘ring.');
        }
    }
}
