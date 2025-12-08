<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class AesEncryptionHelper
{


    /**
     * Encrypt the given plaintext.
     */
    public static function encrypt(string $plainText): string
    {
        // $key = env('AES_SECRET_KEY');
        $key = 'm2$J1n!W8vT9q@Z4xC1rF5dK6g#L0yP7';

        // Generate random IV (16 bytes for AES)
        $iv = random_bytes(16);

        // Encrypt using AES-256-CBC
        $cipherText = openssl_encrypt(
            $plainText,
            'aes-256-cbc',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        // Prepend IV to the ciphertext
        $ivCipher = $iv . $cipherText;

        // Return Base64 encoded string
        return base64_encode($ivCipher);
    }

    /**
     * Decrypt the given ciphertext.
     */
    public static function decrypt(string $cipherText): string
    {
        // $key = env('AES_SECRET_KEY');
          $key = 'm2$J1n!W8vT9q@Z4xC1rF5dK6g#L0yP7';

        // Decode Base64
        $ivCipher = base64_decode($cipherText);

        // Extract IV (first 16 bytes)
        $iv = substr($ivCipher, 0, 16);

        // Extract actual ciphertext
        $actualCipher = substr($ivCipher, 16);

        // Decrypt
        $plainText = openssl_decrypt(
            $actualCipher,
            'aes-256-cbc',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        return $plainText;
    }
}
