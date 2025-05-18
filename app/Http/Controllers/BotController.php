<?php

namespace App\Http\Controllers;

use Telegram\Bot\Api;
use App\Models\User;
use App\Models\Channel;
use App\Models\ChannelMember;
use App\Models\ContestSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BotController extends Controller
{
    protected $telegram;

    public function __construct()
    {
        $this->telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
    }

    public function handleWebhook(Request $request)
    {
        try {
            $update = $this->telegram->getWebhookUpdate();

            // Callback queryni qayta ishlash
            if ($update->getCallbackQuery()) {
                $this->handleCallbackQuery($update->getCallbackQuery());
                return response()->json(['status' => 'ok']);
            }

            $message = $update->getMessage();
            if (!$message) {
                return response()->json(['status' => 'ok']);
            }

            $chatId = $message->getChat()->getId();
            $text = $message->getText();
            $user = $message->getFrom();

            // Foydalanuvchini ro'yxatdan o'tkazish
            $dbUser = User::firstOrCreate(
                ['telegram_id' => $user->getId()],
                [
                    'first_name' => $user->getFirstName(),
                    'referral_link' => $this->generateReferralLink($user->getId())
                ]
            );

            // Referal ID ni saqlash
            if (strpos($text, '/start ref_') === 0) {
                $referrerTelegramId = str_replace('/start ref_', '', $text);
                $referrer = User::where('telegram_id', $referrerTelegramId)->first();
                if ($referrer && $referrer->telegram_id != $dbUser->telegram_id) {
                    $dbUser->referred_by = $referrer->id; // users.id ga ishora qiladi
                    $dbUser->save();
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "Siz {$referrer->first_name} tomonidan taklif qilindingiz! Ro'yxatdan o'tishni davom ettiring.",
                    ]);
                } else {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "Noto'g'ri referal havola. Iltimos, oddiy /start buyrug'ini ishlatib ro'yxatdan o'ting.",
                    ]);
                }
            }

            // Contact ma'lumotlarini qabul qilish
            if ($message->getContact()) {
                $phoneNumber = $message->getContact()->getPhoneNumber();
                if (!$dbUser->phone_number) {
                    $dbUser->phone_number = $phoneNumber;
                    $dbUser->save();
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => 'Telefon raqamingiz muvaffaqiyatli saqlandi! Endi kanal(lar)ga aʼzo boʼling.',
                    ]);
                    $this->sendChannelList($chatId, $dbUser);
                } else {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => 'Sizning telefon raqamingiz allaqachon saqlangan! Kanal(lar)ga aʼzo boʼling.',
                    ]);
                    $this->sendChannelList($chatId, $dbUser);
                }
                return response()->json(['status' => 'ok']);
            }

            // Buyruqlarni qayta ishlash
            if ($text === '/start' || strpos($text, '/start ref_') === 0) {
                // Konkurs faol ekanligini tekshirish
                $activeContest = ContestSetting::where('status', 'active')
                    ->where('end_date', '>=', now())
                    ->first();
                if (!$activeContest) {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => 'Hozirda faol konkurs mavjud emas.',
                    ]);
                    return response()->json(['status' => 'ok']);
                }

                // Telefon raqami mavjudligini tekshirish
                if (!$dbUser->phone_number) {
                    $this->requestPhoneNumber($chatId);
                } else {
                    $this->sendChannelList($chatId, $dbUser);
                }
            } elseif ($text === '/stats') {
                $this->sendStats($chatId, $dbUser);
            } elseif ($text === '/register') {
                if ($dbUser->phone_number) {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => 'Siz allaqachon roʼyxatdan oʼtgansiz! Kanal(lar)ga aʼzo boʼling.',
                    ]);
                    $this->sendChannelList($chatId, $dbUser);
                } else {
                    $this->requestPhoneNumber($chatId);
                }
            }

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error("Webhook error: {$e->getMessage()}");
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Xatolik yuz berdi. Iltimos, qaytadan urinib koʼring.',
            ]);
            return response()->json(['status' => 'error']);
        }
    }

        protected function handleCallbackQuery($callbackQuery)
        {
            $chatId = $callbackQuery->getMessage()->getChat()->getId();
            $userId = $callbackQuery->getFrom()->getId();
            $data = $callbackQuery->getData();

            if ($data === 'check_membership') {
                $user = User::where('telegram_id', $userId)->first();
                if (!$user->phone_number) {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => 'Iltimos, avval telefon raqamingizni ulashing.',
                    ]);
                    $this->requestPhoneNumber($chatId);
                    $this->telegram->answerCallbackQuery([
                        'callback_query_id' => $callbackQuery->getId(),
                    ]);
                    return;
                }

                // Faol konkursni olish
                $activeContest = ContestSetting::where('status', 'active')
                    ->where('end_date', '>=', now())
                    ->first();

                if (!$activeContest) {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => 'Hozirda faol konkurs mavjud emas.',
                    ]);
                    $this->telegram->answerCallbackQuery([
                        'callback_query_id' => $callbackQuery->getId(),
                    ]);
                    return;
                }

                // Faol konkursga bog'liq kanallarni olish
                $channels = $activeContest->channels;
                if ($channels->isEmpty()) {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => 'Hozirda aʼzo boʼlish kerak boʼlgan kanal mavjud emas.',
                    ]);
                    $this->telegram->answerCallbackQuery([
                        'callback_query_id' => $callbackQuery->getId(),
                    ]);
                    return;
                }

                $allChannelsJoined = true;
                $newMembership = false;

                foreach ($channels as $channel) {
                    try {
                        $member = $this->telegram->getChatMember([
                            'chat_id' => $channel->telegram_id,
                            'user_id' => $userId,
                        ]);

                        if (in_array($member['status'], ['member', 'administrator', 'creator'])) {
                            // Kanal a'zoligini saqlash
                            $channelMember = ChannelMember::firstOrCreate([
                                'user_id' => $user->id,
                                'channel_id' => $channel->id,
                            ]);
                            if ($channelMember->wasRecentlyCreated) {
                                $newMembership = true; // Yangi a'zolik qo'shildi
                            }
                        } else {
                            $allChannelsJoined = false;
                        }
                    } catch (\Exception $e) {
                        Log::error("Error checking membership for user {$userId} in channel {$channel->telegram_id}: {$e->getMessage()}");
                        $allChannelsJoined = false;
                    }
                }

                if ($allChannelsJoined) {
                    // Barcha kanallarga a'zo bo'lganini tekshirish
                    $channelCount = $channels->count();
                    $userChannelCount = ChannelMember::where('user_id', $user->id)
                        ->whereIn('channel_id', $channels->pluck('id'))
                        ->count();

                    // Agar foydalanuvchi barcha kanallarga a'zo bo'lgan bo'lsa va yangi a'zolik qo'shilgan bo'lsa
                    if ($userChannelCount >= $channelCount && $newMembership && $user->referred_by) {
                        $referrer = User::find($user->referred_by);
                        if ($referrer) {
                            $referrer->increment('points');
                            $this->telegram->sendMessage([
                                'chat_id' => $referrer->telegram_id,
                                'text' => "Sizning referalingiz {$user->first_name} barcha kanallarga qo'shildi! +1 ball.",
                            ]);
                        }
                    }

                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "Tabriklaymiz! Siz barcha kanal(lar)ga a'zo bo'ldingiz.\nSizning referal havolangiz: {$user->referral_link}",
                    ]);
                } else {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "Iltimos, barcha kanal(lar)ga a'zo bo'ling va qayta urinib ko'ring.",
                    ]);
                    $this->sendChannelList($chatId, $user);
                }

                $this->telegram->answerCallbackQuery([
                    'callback_query_id' => $callbackQuery->getId(),
                ]);
            }
        }

    protected function sendChannelList($chatId, $user)
    {
        // Faol konkursni olish
        $activeContest = ContestSetting::where('status', 'active')
            ->where('end_date', '>=', now())
            ->first();

        if (!$activeContest) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Hozirda faol konkurs mavjud emas.',
            ]);
            return;
        }

        // Faol konkursga bog'liq kanallarni olish
        $channels = $activeContest->channels;
        if ($channels->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Hozirda aʼzo boʼlish kerak boʼlgan kanal mavjud emas.',
            ]);
            return;
        }

        $keyboard = [];
        foreach ($channels as $channel) {
            $keyboard[] = [['text' => $channel->name, 'url' => $channel->invite_link]];
        }
        $keyboard[] = [['text' => "A'zo bo'ldim", 'callback_data' => 'check_membership']];

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "Konkursda ishtirok etish uchun quyidagi kanal(lar)ga aʼzo boʼling:",
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
        ]);
    }

    protected function requestPhoneNumber($chatId)
    {
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Iltimos, telefon raqamingizni ulashish uchun quyidagi tugmani bosing.',
            'reply_markup' => json_encode([
                'keyboard' => [[
                    ['text' => 'Telefon raqamini ulashish', 'request_contact' => true]
                ]],
                'one_time_keyboard' => true,
                'resize_keyboard' => true,
            ]),
        ]);
    }

    protected function generateReferralLink($userId)
    {
        return "https://t.me/" . env('TELEGRAM_BOT_USERNAME') . "?start=ref_{$userId}";
    }

    protected function sendStats($chatId, $user)
    {
        $referrals = User::where('referred_by', $user->id)->take(10)->get();
        $referralsCount = User::where('referred_by', $user->id)->count();

        $text = "Sizning statistikangiz:\n";
        $text .= "Umumiy ball: {$user->points}\n";
        $text .= "Qo'shgan foydalanuvchilar soni: {$referralsCount}\n";
        $text .= "Referallar:\n";
        foreach ($referrals as $ref) {
            $text .= "- {$ref->first_name} (Ball: 1)\n";
        }
        if ($referralsCount > 10) {
            $text .= "... va yana " . ($referralsCount - 10) . " ta referal.";
        }

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
        ]);
    }
}
