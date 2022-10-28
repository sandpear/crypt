<?php
/**
 * RSA配置
 */

namespace Sandpear\Crypt\lib;


use Sandpear\Crypt\base\BaseObject;

class ConfigRsa extends BaseObject {
    public string $password      = ''; # 私钥密码
    public string $private_key   = ''; # 私钥
    public string $public_key    = ''; # 公钥
    public string $digest_alg    = 'SHA512';
    public int $private_key_bits = 4096;
    public int $padding          = OPENSSL_PKCS1_PADDING;

    /**
     * 格式化密钥对
     * @return void
     */
    function init(){
        if(!empty($this->private_key)) {
            $this->private_key = CryptRsa::privateKeyStringToFormat($this->private_key, !empty($this->password) ? 1 : 0);
            $detail = CryptRsa::getDetails($this->private_key, $this->password);
            if(!empty($detail['key']) && empty($this->public_key)) {
                $this->public_key = $detail['key'];
            }
            if(!empty($detail['bits'])) {
                $this->private_key_bits = $detail['bits'];
            }
        }
        if(!empty($this->public_key)) {
            $this->public_key = CryptRsa::publicKeyStringToFormat($this->public_key);
        }
    }
}