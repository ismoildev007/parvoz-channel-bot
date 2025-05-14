<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\ContestSetting;
use App\Models\Prize;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
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
            'telegram_id' => 'required|string',
            'name' => 'required|string',
            'invite_link' => 'required|url',
        ]);

        $channel = Channel::create($request->only(['telegram_id', 'name', 'invite_link']));
        $contest->channels()->attach($channel->id);
        return redirect()->route('admin.contest.edit', $contest->id)->with('success', 'Kanal qo\'shildi.');
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
