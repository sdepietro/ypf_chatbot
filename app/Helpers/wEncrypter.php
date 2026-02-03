<?php

namespace App\Helpers;




if (!function_exists('wEncryptString')) {

    function wEncryptString($string)
    {
        if(empty($string)){
            return $string;
        }
        $masterPassword = config('constants.password_wencrypter');

        $method = 'aes-256-cbc'; // Método de cifrado
        $ivLength = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($ivLength); // Vector de inicialización

        $encryptedString = openssl_encrypt($string, $method, $masterPassword, 0, $iv);
        // Devolvemos el texto cifrado junto con el IV, necesario para el descifrado
        return base64_encode($encryptedString . '::' . $iv);
    }
}


if (!function_exists('wDecryptString')) {
    function wDecryptString($encryptedString)
    {
        if(empty($encryptedString)){
            return $encryptedString;
        }
        $masterPassword = config('constants.password_wencrypter');

        $method = 'aes-256-cbc';

        list($encryptedData, $iv) = explode('::', base64_decode($encryptedString), 2);
        if (false === $encryptedData || false === $iv) {
            die("error aal decifrar la clave");
            throw new Exception('Error al descifrar la cadena.');
        }

        return openssl_decrypt($encryptedData, $method, $masterPassword, 0, $iv);
    }
}

