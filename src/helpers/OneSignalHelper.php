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
            self::$appId  = getenv('ONESIGNAL_APP_ID') ?: "1993fdbb-0de7-4f06-8b61-89e471b48160";
            self::$apiKey = getenv('ONESIGNAL_API_KEY') ?: "os_v2_app_dgj73oyn45hqnc3brhshdnebmc3t3bqa7ozukwvxkq42gim4dlqsl235ccb6ufbkn5a5jim3bzjiljf7odusd3yvbjpawtthlbfrylq";
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

        // Jika player ID kosong, hentikan
        if (empty($playerIds)) return false;
        if (is_string($playerIds)) $playerIds = [$playerIds];

        // Filter player ID yang valid
        $playerIds = array_filter($playerIds, fn($p) => !empty($p));
        if (empty($playerIds)) return false;

        $type = $data['type'] ?? 'general';

        // ✨ Bersihkan pesan dari HTML / karakter aneh
        $message = str_replace(['<br>', '<br/>', '<br />'], "\n", $message);
        $message = preg_replace('/<p[^>]*>(.*?)<\/p>/is', "$1\n\n", $message);
        $message = preg_replace('/<(ul|ol)[^>]*>/', "\n<$1>", $message);

        // Format ordered list
        if (preg_match_all('/<ol[^>]*>(.*?)<\/ol>/is', $message, $matches)) {
            foreach ($matches[1] as $block) {
                $counter = 1;
                $newBlock = preg_replace_callback('/<li[^>]*>(.*?)<\/li>/is', function ($m) use (&$counter) {
                    return $counter++ . ". " . trim(strip_tags($m[1])) . "\n";
                }, $block);
                $message = str_replace($block, "\n" . $newBlock, $message);
            }
        }

        // Format unordered list
        $message = preg_replace('/<ul[^>]*>(.*?)<\/ul>/is', "\n$1", $message);
        $message = preg_replace('/<li[^>]*>(.*?)<\/li>/is', "• $1\n", $message);

        // Bersihkan tag HTML dan entitas
        $message = html_entity_decode(strip_tags($message));
        $message = preg_replace("/\n{3,}/", "\n\n", $message);
        $message = trim($message);

        // Pastikan minimal 1 karakter
        if ($message === '') $message = '(No content)';

        $payload = [
            'app_id' => self::$appId,
            'include_player_ids' => array_values($playerIds),
            'contents' => ["en" => $message],
            'headings' => ["en" => $title ?? ucfirst($type) . " Notification"],
            'data' => (object)$data, // harus objek, bukan array kosong
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
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $result = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log("❌ OneSignal CURL error: " . $curlError);
            return false;
        }

        // Decode hasil JSON agar tidak jadi string escaped
        $resultData = json_decode($result, true);

        // Log hasil
        error_log("✅ OneSignal response: " . json_encode($resultData, JSON_UNESCAPED_UNICODE));

        return $resultData;
    }


}
