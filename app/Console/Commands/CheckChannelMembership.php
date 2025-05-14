<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Api;
use App\Models\User;
use App\Models\Channel;
use App\Models\ChannelMember;

class CheckChannelMembership extends Command
{
    protected $signature = 'check:channel-membership';
    protected $description = 'Check channel membership and update points';

    public function handle()
    {
        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
        $channels = Channel::all();

        foreach ($channels as $channel) {
            $members = ChannelMember::where('channel_id', $channel->id)->get();
            foreach ($members as $member) {
                $user = User::find($member->user_id);
                $isMember = $telegram->getChatMember([
                    'chat_id' => $channel->channel_id,
                    'user_id' => $user->telegram_id
                ]);

                if ($isMember['status'] === 'left' && $member->status === 'active') {
                    $member->status = 'left';
                    $member->save();

                    // Ballni ayirish
                    $referrer = User::find($user->referred_by);
                    if ($referrer) {
                        $referrer->points -= 1;
                        $referrer->save();

                        // Xabar yuborish
                        $telegram->sendMessage([
                            'chat_id' => $referrer->telegram_id,
                            'text' => "Siz qo'shgan {$user->first_name} kanaldan chiqib ketdi. Sizdan -1 ball ayirildi."
                        ]);
                    }
                }
            }
        }

        $this->info('Channel membership checked and points updated.');
    }
}
