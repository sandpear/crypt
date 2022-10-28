<?php
/**
 * RSA.
 */
namespace Sandpear\Crypt\lib;


class CryptRsa implements CryptInterface
{
    const CRYPT_NAME = 'RSA';
    protected ConfigRsa $Config;
    protected array $Error;

    function __construct(ConfigRsa $config) {
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
     * 创建密钥对
     * @return array
     */
    public function create(): array
    {
        //创建密钥
        $res = openssl_pkey_new([
            'private_key_bits' => $this->Config->private_key_bits,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'digest_alg'       => $this->Config->digest_alg,
        ]);

        //提取私钥
        if (!empty($this->Config->password))
        {
            openssl_pkey_export($res, $this->Config->private_key, $this->Config->password);
        } else {
            openssl_pkey_export($res, $this->Config->private_key);
        }

        //提取公钥
        $details = openssl_pkey_get_details($res);

        $this->Config->public_key = $details['key'];
        $en_max_length = $details['bits'] / 8 - 11;
        $de_length     = $details['bits'] / 8;

        return [
            'crypt_type'       => self::CRYPT_NAME,
            'private_key_type' => $details['type'],
            'private_key_bits' => $details['bits'],
            'public_key'       => $this->Config->public_key,
            'private_key'      => $this->Config->private_key,
            'password'         => $this->Config->password,
            'en_block_length'  => $en_max_length,
            'de_block_length'  => $de_length,
            'crypt_note'       => 'RSA/'
                .$this->Config->digest_alg.'/'
                .$details['bits']
                .'bits/PKCS#8, 因最大允许加密字节长度为'
                .$en_max_length.'，所以加密时，需要把数据按'
                .$en_max_length.'个字节长度进行截取分段加密，然后把所有的密文拼接成一个密文再进行base64加密；RSA解密的时候需要base64解密后把密文按'
                .$de_length.'个字按长度截取分段解密，然后再把所有解密后的明文拼接成数据。',
        ];
    }

    /**
     * 加密数据
     * @param string $value
     * @return bool|string
     */
    public function encrypt(string $value)
    {
        return $this->publicEncrypt($value, $this->Config->public_key);
    }

    /**
     * 解密数据
     * @param string $value
     * @return bool|string
     */
    public function decrypt(string $value)
    {
        return $this->privateDecrypt($value, $this->Config->private_key , $this->Config->password);
    }

    /**
     * 私钥加密
     * @param string      $data
     * @param string|null $private_key
     * @param string|null $password
     * @return bool|string
     */
    public function privateEncrypt(string $data, string $private_key = null, string $password = null)
    {
        if(empty($private_key))
            $private_key = $this->Config->private_key;

        if(empty($password))
            $password = $this->Config->password;

        if (!empty($password)) {
            $res = openssl_pkey_get_private($private_key, $password);
        } else {
            $res = openssl_pkey_get_private($private_key);
        }
        if(!$res) {
            $this->Error[] = '读取私钥失败！';
            return false;
        }
        $details   = openssl_pkey_get_details($res);
        //split length = bits / 8 - 11
        $plainData = str_split($data, $details['bits'] / 8 - 11);
        $encrypted = '';
        foreach ($plainData as $chunk) {
            $str = '';
            $encryption = openssl_private_encrypt($chunk, $str, $res, $this->Config->padding);
            if ($encryption === false) {
                $this->Error[] = '使用私钥加密数据失败！';
                return false;
            }
            $encrypted .= $str;
        }
        //encrypted coder base64_encode.
        $encrypted = base64_encode($encrypted);
        return $encrypted;
    }

    /**
     * 私钥解密
     * @param string      $encrypted
     * @param string|null $private_key
     * @param string|null $password
     * @return bool|string
     */
    public function privateDecrypt(string $encrypted, string $private_key = null, string $password = null)
    {
        if(empty($private_key))
            $private_key = $this->Config->private_key;

        if(empty($password))
            $password = $this->Config->password;

        if (!empty($password)) {
            $res = openssl_pkey_get_private($private_key, $password);
        } else {
            $res = openssl_pkey_get_private($private_key);
        }
        if(!$res) {
            $this->Error[] = '读取私钥失败！';
            return false;
        }
        $details   = openssl_pkey_get_details($res);
        $plainData = str_split(base64_decode($encrypted), $details['bits'] / 8);
        $decrypted = '';
        foreach ($plainData as $chunk) {
            $str = '';
            $decryption = openssl_private_decrypt($chunk, $str, $res, $this->Config->padding);
            if ($decryption === false) {
                $this->Error[] = '使用私钥解密失败！';
                return false;
            }
            $decrypted .= $str;
        }
        return $decrypted;
    }

    /**
     * 公钥加密
     * @param string      $data
     * @param string|null $public_key
     * @return bool|string
     */
    public function publicEncrypt(string $data, string $public_key = null)
    {
        if(empty($public_key))
            $public_key = $this->Config->public_key;

        $res = openssl_pkey_get_public($public_key);
        if(!$res) {
            $this->Error[] = '读取公钥失败！';
            return false;
        }
        $details    = openssl_pkey_get_details($res);
        $plainData  = str_split($data, $details['bits'] / 8 - 11);
        $encrypted  = '';
        foreach ($plainData as $chunk) {
            $str = '';
            $encryption = openssl_public_encrypt($chunk, $str, $res, $this->Config->padding);
            if ($encryption === false) {
                $this->Error[] = '使用公钥加密数据失败！';
                return false;
            }
            $encrypted .= $str;
        }
        $encrypted = base64_encode($encrypted);
        return $encrypted;
    }

    /**
     * 公钥解密
     * @param string      $encrypted
     * @param string|null $public_key
     * @return bool|string
     */
    public function publicDecrypt(string $encrypted, string $public_key = null)
    {
        if(empty($public_key))
            $public_key = $this->Config->public_key;

        $res = openssl_pkey_get_public($public_key);
        if(!$res) {
            $this->Error[] = '读取私钥失败！';
            return false;
        }
        $details    = openssl_pkey_get_details($res);
        $plainData  = str_split(base64_decode($encrypted), $details['bits'] / 8);
        $decrypted  = '';
        foreach ($plainData as $chunk) {
            $str = '';
            $decryption = openssl_public_decrypt($chunk, $str, $res, $this->Config->padding);
            if ($decryption === false) {
                $this->Error[] = '使用公钥解密数据失败！';
                return false;
            }
            $decrypted .= $str;
        }
        return $decrypted;
    }


    /**
     * 私钥签名
     * @param string $data
     * @return string|bool
     */
    public function sign(string $data)
    {
        if (!empty($this->Config->password)) {
            $res = openssl_pkey_get_private($this->Config->private_key, $this->Config->password);
        } else {
            $res = openssl_pkey_get_private($this->Config->private_key);
        }
        if(!$res) {
            $this->Error[] = '读取私钥失败！';
            return false;
        }
        openssl_sign($data, $signature, $res, $this->Config->digest_alg);
        $signature = base64_encode($signature);
        return $signature;
    }

    /**
     * 公钥验证签名
     * @param string $data
     * @param string $sign
     * @return bool
     */
    public function signVerify(string $data, string $sign): bool
    {
        $res = openssl_pkey_get_public($this->Config->public_key);
        if(!$res) {
            $this->Error[] = '读取公钥失败！';
            return false;
        }
        $signature = base64_decode($sign);
        $verify    = openssl_verify($data, $signature, $this->Config->public_key, $this->Config->digest_alg);
        //coder check verify, correct return 1 error returns 0
        if ($verify) {
            return true;
        }
        return false;
    }


    /**
     * 单行私钥格式化
     * @param string $private_key 私钥字符串
     * @param int    $encrypt     是否带密码(1:带密码，0:不带密码)
     * @return string
     */
    public static function privateKeyStringToFormat(string $private_key, int $encrypt = 1): string
    {
        if(substr($private_key, 0, 5) == '-----' || empty($private_key))
        {
            return $private_key;
        }
        $private_key = trim(str_replace(["\r", "\n"], '', $private_key));
        $private_key = chunk_split($private_key, 64, "\n");
        if($encrypt)
        {
            $private_key = "-----BEGIN ENCRYPTED PRIVATE KEY-----\n$private_key-----END ENCRYPTED PRIVATE KEY-----\n";
        }else{
            $private_key = "-----BEGIN PRIVATE KEY-----\n$private_key-----END PRIVATE KEY-----\n";
        }

        return trim($private_key);
    }

    /**
     * 单行公钥格式化
     * @param string $public_key
     * @return string
     */
    public static function publicKeyStringToFormat(string $public_key): string
    {
        if(substr($public_key, 0, 5) == '-----' || empty($public_key))
        {
            return $public_key;
        }
        $public_key = trim(str_replace(["\r", "\n"], '', $public_key));
        $public_key = chunk_split($public_key, 64, "\n");
        $public_key = "-----BEGIN PUBLIC KEY-----\n$public_key-----END PUBLIC KEY-----\n";
        return trim($public_key);
    }

    /**
     * 多行格式公私钥格式化成去头去尾过滤空格，转换成只有一行
     * @param string $keyString
     * @return string
     */
    public static function filter(string $keyString): string
    {
        $keyString = trim($keyString);
        if(substr($keyString, 0, 5) != '-----' || empty($keyString))
        {
            return $keyString;
        }
        $keyFormat = str_replace(["\r\n", "\r", "\n", PHP_EOL], '', $keyString);
        preg_match("/-----(.*?)-----(.*?)-----(.*?)-----/i", $keyFormat, $data);
        return trim($data[2] ?? $keyString);
    }


    /**
     * 读取私钥信息
     * @param string $private_key
     * @param string|null $password
     * @return array|false
     */
    public static function getDetails(string $private_key, string $password = null)
    {
        if (!empty($password)) {
            $res = openssl_pkey_get_private($private_key, $password);
        } else {
            $res = openssl_pkey_get_private($private_key);
        }
        if(!$res) {
            return false;
        }
        $details = openssl_pkey_get_details($res);
        return $details;
    }
}