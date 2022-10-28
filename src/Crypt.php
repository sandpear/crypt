<?php
/**
 * 类库调用示例
 */
namespace Sandpear\Crypt;

use Sandpear\Crypt\lib\ConfigAes;
use Sandpear\Crypt\lib\ConfigAuthcode;
use Sandpear\Crypt\lib\ConfigRsa;
use Sandpear\Crypt\lib\CryptAes;
use Sandpear\Crypt\lib\CryptAuthcode;
use Sandpear\Crypt\lib\CryptRsa;

class Crypt
{
    public array $Object;

    /**
     * AES 调用示例
     * @param ConfigAes $config
     * @return CryptAes
     */
    public function Aes(ConfigAes $config):CryptAes {
        $this->Object['AES'] = new CryptAes($config);
        return $this->Object['AES'];
    }

    /**
     * RSA 调用示例
     * @param ConfigRsa $config
     * @return CryptRsa
     */
    function Rsa(ConfigRsa $config):CryptRsa {
        $this->Object['RSA'] = new CryptRsa($config);
        return $this->Object['RSA'];
    }

    /**
     * Authcode 调用示例
     * @param ConfigAuthcode $config
     * @return CryptAuthcode
     */
    function Authcode(ConfigAuthcode $config):CryptAuthcode {
        $this->Object['AUTHCODE'] = new CryptAuthcode($config);
        return $this->Object['AUTHCODE'];
    }
}