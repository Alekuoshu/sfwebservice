<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);


class AesCipher {
    
    const OPENSSL_CIPHER_NAME = 'aes-256-cbc';
    const CIPHER_KEY_LEN = 16; //128 bits

    static function fixKey($key) {
        
        if (strlen($key) < AesCipher::CIPHER_KEY_LEN) {
            //0 pad to len 16
            return str_pad("$key", AesCipher::CIPHER_KEY_LEN, "0"); 
        }
        
        if (strlen($key) > AesCipher::CIPHER_KEY_LEN) {
            //truncate to 16 bytes
            return substr($key, 0, AesCipher::CIPHER_KEY_LEN); 
        }

        return $key;
    }

    /**
    * Encrypt data using AES Cipher (CBC) with 128 bit key
    * 
    * @param type $key - key to use should be 16 bytes long (128 bits)
    * @param type $iv - initialization vector
    * @param type $data - data to encrypt
    * @return encrypted data in base64 encoding with iv attached at end after a :
    */
    static function encrypt($key, $iv, $data) {

        $encodedEncryptedData = base64_encode(openssl_encrypt($data, AesCipher::OPENSSL_CIPHER_NAME, AesCipher::fixKey($key), OPENSSL_RAW_DATA, $iv));
        $encodedIV = base64_encode($iv);
        $encryptedPayload = $encodedEncryptedData.":".$encodedIV;

        return $encryptedPayload;
    }

    /**
    * Decrypt data using AES Cipher (CBC) with 128 bit key
    * 
    * @param type $key - key to use should be 16 bytes long (128 bits)
    * @param type $data - data to be decrypted in base64 encoding with iv attached at the end after a :
    * @return decrypted data
    */
    static function decrypt($key, $data) {

        $decodedPayload = base64_decode($data);
        $iv = AesCipher::fixKey($decodedPayload);
        $cipherText = substr($decodedPayload, AesCipher::CIPHER_KEY_LEN);
        $plainText = openssl_decrypt($cipherText, AesCipher::OPENSSL_CIPHER_NAME, $key, OPENSSL_RAW_DATA, $iv);

        return $plainText;

    }
};

// $str='abbott-secret-key';
// $hash_key = 'keyForHash';
// $key = hash_hmac('sha256', $str, $hash_key, true);

// $cipher_text = 'bJF/kb05AnFVdAR5fOdvnJuC/ldN/mL/nOd1kfSM5GyKJF8imlJh3oS0iilAy2dwmK/if3HXgn4Mx4mUhlCJw8O1pPN1N1aYQVdns4ttCX7vGhAIPVT2wnYbFyFKgGNOeDy+ojPvN5orOE5s8NJ+mPga/WRb4Vga671UnFgRQF+eqjDYeFysB4zwKzHUhzq+r7vskFPl0wKkxlVXYH6GRKKW7d1J0SueqUQ6qmASdVk=';

// $decrypted = AesCipher::decrypt($key, $cipher_text);

// var_dump($decrypted);


?>

