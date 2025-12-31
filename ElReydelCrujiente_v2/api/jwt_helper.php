<?php
// api/jwt_helper.php

class JWT {
    private static $secret_key = 'S3cr3tK3y!ChangeMeInProduction'; // Change this!
    private static $algo = 'HS256';

    public static function encode($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => self::$algo]);
        $payload['iat'] = time();
        $payload['exp'] = time() + (60 * 60 * 24); // 24 hours
        $payload = json_encode($payload);

        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payload);

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function decode($jwt) {
        $tokenParts = explode('.', $jwt);
        if (count($tokenParts) != 3) return null;

        $header = base64_decode($tokenParts[0]);
        $payload = base64_decode($tokenParts[1]);
        $signature_provided = $tokenParts[2];

        // Verify Signature
        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payload);
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        if ($base64UrlSignature === $signature_provided) {
            $data = json_decode($payload, true);
            if ($data['exp'] < time()) return null; // Expired
            return $data;
        }
        return null;
    }

    private static function base64UrlEncode($text) {
        return str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($text)
        );
    }
}
?>
