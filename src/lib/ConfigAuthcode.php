<?php
/**
 * Authcode配置
 */

namespace Sandpear\Crypt\lib;


use Sandpear\Crypt\base\BaseObject;

class ConfigAuthcode extends BaseObject {
    public string $key = ''; # 加解密密钥
    public int $expiry = 0;  # 加密数据有效期限，为0是永久有效
}