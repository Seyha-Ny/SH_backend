<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around the Telegram Bot API so any part of the app can send
 * alerts without duplicating the token/chat-id lookups and error handling.
 *
 * All methods are no-ops when the bot token is not configured, so this is
 * safe to call in production even if Telegram was never set up.
 */
class TelegramService
{
    public function sendToAdminChat(string $text): bool
    {
        return $this->send(config('services.telegram.chat_id'), $text);
    }

    public function sendToChat(?string $chatId, string $text): bool
    {
        return $this->send($chatId, $text);
    }

    public function send(?string $chatId, string $text): bool
    {
        $token = config('services.telegram.bot_token');

        if (! $token || ! $chatId) {
            return false;
        }

        try {
            Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => (string) $chatId,
                'text' => $text,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Telegram notification failed.', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
