<?php

namespace App\Services;

/**
 * Minimale TOTP-Implementierung (RFC 6238) ohne externe Abhängigkeiten.
 */
class TotpService
{
    private const DIGITS   = 6;
    private const INTERVAL = 30;
    private const WINDOW   = 1; // ±1 Schritt Toleranz

    /** Zufälliges Base32-Secret (16 Zeichen = 80 Bit) generieren. */
    public static function generateSecret(): string
    {
        $chars  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 16; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }

    /** TOTP-Code für einen bestimmten Zeitslot berechnen. */
    public static function getCode(string $secret, int $timestamp = 0): string
    {
        $time = $timestamp ?: time();
        $slot = (int) floor($time / self::INTERVAL);
        return self::hotp($secret, $slot);
    }

    /** Code verifizieren (mit Zeitfenster). */
    public static function verify(string $secret, string $code): bool
    {
        $code = preg_replace('/\s/', '', $code);
        $slot = (int) floor(time() / self::INTERVAL);
        for ($i = -self::WINDOW; $i <= self::WINDOW; $i++) {
            if (hash_equals(self::hotp($secret, $slot + $i), $code)) {
                return true;
            }
        }
        return false;
    }

    /** otpauth:// URL für QR-Code-Generator. */
    public static function getOtpAuthUrl(string $secret, string $email, string $issuer = 'ZeitManager'): string
    {
        return 'otpauth://totp/'
            . rawurlencode($issuer . ':' . $email)
            . '?secret=' . $secret
            . '&issuer=' . rawurlencode($issuer)
            . '&algorithm=SHA1&digits=6&period=30';
    }

    /** QR-Code als Bild-URL (via api.qrserver.com). */
    public static function getQrCodeUrl(string $secret, string $email, string $issuer = 'ZeitManager'): string
    {
        $otp = self::getOtpAuthUrl($secret, $email, $issuer);
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . rawurlencode($otp);
    }

    /** 6 zufällige Backup-Codes generieren. */
    public static function generateBackupCodes(int $count = 6): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }
        return $codes;
    }

    // ── HOTP ──────────────────────────────────────────────────────────────

    private static function hotp(string $secret, int $counter): string
    {
        $key     = self::base32Decode($secret);
        $msg     = pack('N*', 0) . pack('N*', $counter);
        $hash    = hash_hmac('sha1', $msg, $key, true);
        $offset  = ord($hash[19]) & 0x0F;
        $code    = (
            ((ord($hash[$offset])     & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) <<  8) |
             (ord($hash[$offset + 3]) & 0xFF)
        ) % (10 ** self::DIGITS);

        return str_pad((string) $code, self::DIGITS, '0', STR_PAD_LEFT);
    }

    private static function base32Decode(string $base32): string
    {
        $chars  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32 = strtoupper($base32);
        $bits   = '';
        foreach (str_split($base32) as $c) {
            $pos   = strpos($chars, $c);
            if ($pos === false) continue;
            $bits .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        $bytes = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) < 8) break;
            $bytes .= chr(bindec($byte));
        }
        return $bytes;
    }
}
