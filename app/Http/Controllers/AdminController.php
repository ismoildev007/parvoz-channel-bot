<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\ContestSetting;
use App\Models\Prize;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

class AdminController extends Controller
{
    protected $telegram;

    public function __construct()
    {
        $this->telegram = new Api('7638629069:AAHXEEL0410voDh_hcP7vO1fuIhLXM2A05U');
    }
    public function index()
    {
        $contests = ContestSetting::with('channels')->get();
        return view('admin.index', compact('contests'));
    }

    public function createContest(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        ContestSetting::create($request->only(['name', 'description', 'start_date', 'end_date']));
        return redirect()->route('admin.index')->with('success', 'Konkurs yaratildi.');
    }

    public function editContest(ContestSetting $contest)
    {
        $channels = Channel::all();
        return view('admin.edit', compact('contest', 'channels'));
    }

    public function updateContest(Request $request, ContestSetting $contest)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,finished',
        ]);

        $contest->update($request->only(['name', 'description', 'start_date', 'end_date', 'status']));
        return redirect()->route('admin.contest.edit', $contest->id)->with('success', 'Konkurs yangilandi.');
    }

    public function addChannel(Request $request, ContestSetting $contest)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'invite_link' => 'required|url',
        ]);

        try {
            // Invite linkdan chat_id ni ajratib olish
            $inviteLink = $request->invite_link;
            // URL dan kanal nomini yoki ID ni olish
            $chatId = $this->extractChatIdFromInviteLink($inviteLink);

            if (!$chatId) {
                throw new \Exception('Kanal havolasidan chat_id ni aniqlab bo‘lmadi.');
            }

            // Telegram API orqali chat_id ni tasdiqlash
            $chat = $this->telegram->getChat(['chat_id' => $chatId]);
            $telegramId = $chat->getId();
            $channel = Channel::create([
                'name' => $request->name,
                'invite_link' => $request->invite_link,
                'telegram_id' => $telegramId,
            ]);
            $contest->channels()->attach($channel->id);
            return redirect()->route('admin.contest.edit', $contest->id)->with('success', 'Kanal qo\'shildi.');
        } catch (\Exception $e) {
            Log::error("Kanal qo‘shishda xato: {$e->getMessage()}");
            return redirect()->route('admin.contest.edit', $contest->id)->with('success', 'Kanal qo\'shildi.');
        }
    }
    /**
     * Invite linkdan chat_id ni ajratib olish
     */
    protected function extractChatIdFromInviteLink($inviteLink)
    {
        // Misol: https://t.me/+abcdef123456789 yoki https://t.me/channel_name
        if (preg_match('/t\.me\/\+([a-zA-Z0-9_-]+)/', $inviteLink, $matches)) {
            // Private kanal uchun +abcdef123456789 formatidan chat_id olish
            return $this->resolvePrivateChatId($matches[1]);
        } elseif (preg_match('/t\.me\/([a-zA-Z0-9_]+)/', $inviteLink, $matches)) {
            // Public kanal uchun @channel_name formatidan chat_id olish
            return '@' . $matches[1];
        }

        return null;
    }

    /**
     * Private kanal uchun chat_id ni aniqlash
     */
    protected function resolvePrivateChatId($inviteCode)
    {
        try {
            // Botni kanalga qo‘shish va chat_id ni olish
            $result = $this->telegram->joinChat(['chat_id' => '+' . $inviteCode]);
            return $result->getId();
        } catch (\Exception $e) {
            Log::error("Private kanal chat_id ni aniqlashda xato: {$e->getMessage()}");
            return null;
        }
    }

    public function removeChannel(ContestSetting $contest, Channel $channel)
    {
        $contest->channels()->detach($channel->id);
        return redirect()->route('admin.contest.edit', $contest->id)->with('success', 'Kanal o\'chirildi.');
    }

    public function statistics(ContestSetting $contest)
    {
        $leaderboard = User::withCount('referrals')
            ->orderBy('points', 'desc')
            ->orderBy('referrals_count', 'desc')
            ->take(10)
            ->get();

        return view('admin.statistics', compact('contest', 'leaderboard'));
    }

    public function addPrize(Request $request, ContestSetting $contest)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|integer|min:1',
        ]);

        Prize::create([
            'contest_setting_id' => $contest->id,
            'name' => $request->name,
            'position' => $request->position,
        ]);

        return redirect()->route('admin.contest.edit', $contest->id)->with('success', 'Sovrin qo‘shildi.');
    }

    public function removePrize(ContestSetting $contest, Prize $prize)
    {
        $prize->delete();
        return redirect()->route('admin.contest.edit', $contest->id)->with('success', 'Sovrin o‘chirildi.');
    }

    public function winners(ContestSetting $contest)
    {
        $winners = User::withCount('referrals')
            ->orderBy('points', 'desc')
            ->orderBy('referrals_count', 'desc')
            ->take($contest->prizes()->count())
            ->get();

        $prizes = $contest->prizes()->orderBy('position')->get();

        return view('admin.winners', compact('contest', 'winners', 'prizes'));
    }
}
