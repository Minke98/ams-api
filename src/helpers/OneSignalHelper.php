<?php

class OneSignalHelper
{
    private static $appId;
    private static $apiKey;

    /**
     * Pastikan API Key dan App ID selalu tersedia (bisa dari env atau fallback)
     */
    protected static function initKeys()
    {
        if (!self::$appId || !self::$apiKey) {
            self::$appId  = getenv('ONESIGNAL_APP_ID') ?: "a5e5e5a3-0f7b-47d1-81ce-588d1a89f49f";
            self::$apiKey = getenv('ONESIGNAL_API_KEY') ?: "os_v2_app_uxs6liyppnd5daoolcgrvcput7ytp6ppejseqgm6jdg6xndq3rzumwgmkvds32m2y3umz5vvnctbnyykhbvxevcg3t33iof2m26qcby";
        }
    }

    /**
     * Kirim notifikasi ke OneSignal
     *
     * @param array|string $playerIds
     * @param string $message
     * @param string|null $title
     * @param array $data
     * @return bool|string
     */
    public static function sendNotification($playerIds, string $message, string $title = null, array $data = [])
    {
        self::initKeys();

        if (empty($playerIds)) return false;
        if (is_string($playerIds)) $playerIds = [$playerIds];

        $type = $data['type'] ?? 'general';

        /*
        |--------------------------------------------------------------------------
        | 🧹 Bersihkan dan format ulang HTML untuk pesan
        |--------------------------------------------------------------------------
        */
        $message = str_replace(['<br>', '<br/>', '<br />'], "\n", $message);
        $message = preg_replace('/<p[^>]*>(.*?)<\/p>/is', "$1\n\n", $message);
        $message = preg_replace('/<(ul|ol)[^>]*>/', "\n<$1>", $message);

        // Ordered list (1., 2., 3.)
        if (preg_match_all('/<ol[^>]*>(.*?)<\/ol>/is', $message, $matches)) {
            foreach ($matches[1] as $block) {
                $counter = 1;
                $newBlock = preg_replace_callback('/<li[^>]*>(.*?)<\/li>/is', function ($m) use (&$counter) {
                    return $counter++ . ". " . trim($m[1]) . "\n";
                }, $block);
                $message = str_replace($block, "\n" . $newBlock, $message);
            }
        }

        // Unordered list (• item)
        $message = preg_replace('/<ul[^>]*>(.*?)<\/ul>/is', "\n$1", $message);
        $message = preg_replace('/<li[^>]*>(.*?)<\/li>/is', "• $1\n", $message);
        $message = html_entity_decode(strip_tags($message));
        $message = preg_replace("/\n{3,}/", "\n\n", $message);
        $message = trim($message);

        /*
        |--------------------------------------------------------------------------
        | 🚀 Kirim ke OneSignal
        |--------------------------------------------------------------------------
        */
        $payload = [
            'app_id' => self::$appId,
            'include_player_ids' => array_values($playerIds),
            'contents' => ["en" => $message],
            'headings' => ["en" => $title ?? ucfirst($type) . " Notification"],
            'data' => $data,
            'android_group' => $type,
            'android_group_message' => ["en" => "You have \$[notif_count] new {$type} notifications"],
            'thread_id' => $type,
        ];

        $ch = curl_init("https://onesignal.com/api/v1/notifications");
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json; charset=utf-8',
                'Authorization: Basic ' . self::$apiKey
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $result = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log("❌ OneSignal CURL error: " . $curlError);
            return false;
        }

        // Log hasil untuk debugging
        error_log("✅ OneSignal response: " . $result);
        return $result;
    }
}
