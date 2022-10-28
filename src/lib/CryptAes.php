<?php
/**
 * AES加解密类.
 */
namespace Sandpear\Crypt\lib;


class CryptAes implements CryptInterface
{
    const CRYPT_NAME = 'AES';
    protected ConfigAes $Config;
    protected array $Error;

    function __construct(ConfigAes $config) {
        $this->Config = $config;
    }

    /**
     * 获取错信息列表
     * @return array
     */
    function getError():array {
        return $this->Error;
    }

    /**
     * AES 加密
     * @param string $value
     * @return string
     */
    public function encrypt(string $value): string
    {
        if(in_array(strtolower(substr($this->Config->cipher,-3)), ['gcm', 'ccm']))
        {
            $encrypt = @openssl_encrypt($value, $this->Config->cipher, $this->Config->key, $this->Config->options, $this->Config->iv, $this->Config->tag,
                $this->Config->aad, $this->Config->tagLen);
        }else{
            $encrypt = @openssl_encrypt($value, $this->Config->cipher, $this->Config->key, $this->Config->options, $this->Config->iv);
        }
        return base64_encode($encrypt);
    }

    /**
     * AES 解密
     * @param string $value
     * @return false|string
     */
    public function decrypt(string $value)
    {
        $encrypt = base64_decode($value);
        if(in_array(strtolower(substr($this->Config->cipher,-3)), ['gcm', 'ccm'])) {
            $text = @openssl_decrypt($encrypt, $this->Config->cipher, $this->Config->key, $this->Config->options,
                $this->Config->iv, $this->Config->tag, $this->Config->aad);
        }else{
            $text = @openssl_decrypt($encrypt, $this->Config->cipher, $this->Config->key, $this->Config->options, $this->Config->iv);
        }
        return $text;
    }

    public function getOpensslRandom(int $len)
    {
        return openssl_random_pseudo_bytes($len);
    }

    public function getIvLen()
    {
        return openssl_cipher_iv_length($this->Config->cipher);
    }

    /**
     * 创建aes密钥对->KEY+IV
     * @param string $cipher
     * @return array|false
     */
    public function createRandomKeyAndIv(string $cipher)
    {
        $cbc = [
            'aes-128-cbc' => 16,
            'aes-192-cbc' => 24,
            'aes-256-cbc' => 32,
        ];
        if(!in_array($cipher, array_keys($cbc)))
        {
            return false;
        }
        $length = $cbc[$cipher];
        $key    = $this->devUrandom($length);
        $iv     = $this->createRandomIv($cipher);
        if(empty($key) || empty($iv))
        {
            return false;
        }
        return [
            'cipher'     => $cipher,
            'key'        => $key,
            'key_length' => $length,
            'iv'         => $iv,
            'iv_length'  => strlen($iv),
        ];
    }

    /**
     * 创建aes密钥->IV
     * @param string $cipher
     * @return false|string
     */
    public function createRandomIv(string $cipher)
    {
        $length = openssl_cipher_iv_length($cipher);
        $iv     = $this->devUrandom($length);
        return $iv;
    }

    /**
     * 生成真随机数
     * 使用系统 /dev/urandom 生成
     * @param int $length  生成密钥长度
     * @return false|string
     */
    function devUrandom(int $length = 16)
    {
        ob_start();
        $urandom  = system('head /dev/urandom | LC_CTYPE=C tr -dc A-Za-z0-9 | head -c'.$length);
        ob_clean();
        return $urandom;
    }
}