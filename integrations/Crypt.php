<?php

declare(strict_types=1);

namespace Integrations;

use RuntimeException;

use function Rcalicdan\ConfigLoader\env;

class Crypt
{
    private static ?string $key = null;
    private const string CIPHER = 'aes-256-gcm';

    /**
     * Retrieve and validate the 32-byte encryption key from the environment.
     */
    private static function getKey(): string
    {
        if (self::$key === null) {
            $key = env('APP_KEY', '');

            if (str_starts_with($key, 'base64:')) {
                $key = base64_decode(substr($key, 7));
            }

            if (\strlen($key) !== 32) {
                throw new RuntimeException('The APP_KEY must be a 32-character string or a base64-encoded 32-byte key.');
            }

            self::$key = $key;
        }

        return self::$key;
    }

    /**
     * Encrypt the given value.
     */
    public static function encrypt(mixed $value): string
    {
        $serialized = serialize($value);
        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = random_bytes($ivLength);
        
        $ciphertext = openssl_encrypt(
            $serialized,
            self::CIPHER,
            self::getKey(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($ciphertext === false) {
            throw new RuntimeException('Encryption failed.');
        }

        return base64_encode($iv . $tag . $ciphertext);
    }

    /**
     * Decrypt the given payload. Returns null on failure or tampering.
     */
    public static function decrypt(string $payload): mixed
    {
        $decoded = base64_decode($payload);
        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $tagLength = 16; 

        if (\strlen($decoded) < $ivLength + $tagLength) {
            return null;
        }

        $iv = substr($decoded, 0, $ivLength);
        $tag = substr($decoded, $ivLength, $tagLength);
        $ciphertext = substr($decoded, $ivLength + $tagLength);

        $decrypted = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            self::getKey(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($decrypted === false) {
            return null;
        }

        return unserialize($decrypted);
    }
}