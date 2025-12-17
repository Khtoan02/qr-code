<?php
require_once __DIR__ . '/../config.php';

class GeminiService {
    /**
     * Chỉnh sửa ảnh sử dụng Gemini 2.5 Flash Image
     */
    public static function editImage($imageBase64, $mimeType, $prompt) {
        if (empty(GEMINI_API_KEY)) {
            throw new Exception("Gemini API key chưa được cấu hình");
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-image:generateContent?key=" . GEMINI_API_KEY;

        $data = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'inline_data' => [
                                'data' => $imageBase64,
                                'mime_type' => $mimeType
                            ]
                        ],
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("CURL Error: " . $error);
        }

        if ($httpCode !== 200) {
            throw new Exception("API Error: HTTP " . $httpCode);
        }

        $result = json_decode($response, true);

        if (isset($result['candidates']) && is_array($result['candidates'])) {
            foreach ($result['candidates'] as $candidate) {
                if (isset($candidate['content']['parts'])) {
                    foreach ($candidate['content']['parts'] as $part) {
                        if (isset($part['inline_data']['data'])) {
                            $mime = $part['inline_data']['mime_type'] ?? 'image/png';
                            return "data:" . $mime . ";base64," . $part['inline_data']['data'];
                        }
                    }
                }
            }
        }

        throw new Exception("Không thể tạo ảnh từ Gemini");
    }
}
?>

