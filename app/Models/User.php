<?php

namespace App\Models;

class User extends Model {

    public function logoutActions() {
        session([
            'mark-login' => false,
            'vox-welcome' => null,
            'login-logged-out' => session('logged_user')['token'] ?? null,
        ]);
    }

    public static function encrypt($raw_text) {
        $length = openssl_cipher_iv_length(env('CRYPTO_METHOD'));
        $iv = openssl_random_pseudo_bytes($length);
        $encrypted = openssl_encrypt($raw_text, env('CRYPTO_METHOD'), env('CRYPTO_KEY'), OPENSSL_RAW_DATA, $iv);
        //here we append the $iv to the encrypted, because we will need it for the decryption
        $encrypted_with_iv = base64_encode($encrypted) . '|' . base64_encode($iv);
        return $encrypted_with_iv;
    }

    public static function decrypt($encrypted_text) {
        $arr = explode('|', $encrypted_text);
        if (count($arr)!=2) {
            return null;
        }
        $data = $arr[0];
        $iv = $arr[1];
        $iv = base64_decode($iv);

        try {
            $raw_text = openssl_decrypt($data, env('CRYPTO_METHOD'), env('CRYPTO_KEY'), 0, $iv);
        } catch (\Exception $e) {
            $raw_text = false;
        }

        return $raw_text;
    }
}