<?php

namespace App\Shared\Libraries;

use Config\Services;

class TelegramLibrary
{
    protected $token;
    protected $chatId;
    protected $apiUrl = 'https://api.telegram.org/bot';

    public function __construct()
    {
        $this->token = env('TELEGRAM_BOT_TOKEN');
        $this->chatId = env('TELEGRAM_CHAT_ID');
    }

    /**
     * Send message to Telegram
     * 
     * @param string $message
     * @param string $parseMode 'HTML' or 'Markdown'
     * @return bool
     */
    public function sendMessage(string $message, string $parseMode = 'HTML'): bool
    {
        if (empty($this->token) || empty($this->chatId)) {
            log_message('error', 'Telegram credentials not set in .env');
            return false;
        }

        try {
            $client = Services::curlrequest();
            $url = $this->apiUrl . $this->token . '/sendMessage';

            $response = $client->request('POST', $url, [
                'form_params' => [
                    'chat_id' => $this->chatId,
                    'text' => $message,
                    'parse_mode' => $parseMode,
                    'disable_web_page_preview' => true
                ],
                'timeout' => 10,
                'http_errors' => false
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                log_message('error', 'Telegram API Error Status: ' . $statusCode . ' Body: ' . $response->getBody());
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            log_message('error', 'Telegram Exception: ' . $e->getMessage());
            return false;
        }
    }
}
