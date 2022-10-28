<?php
/**
 * AES配置
 */

namespace Sandpear\Crypt\lib;


use Sandpear\Crypt\base\BaseObject;

class ConfigAes extends BaseObject {
    public string $cipher  = 'aes-128-cbc';
    public string $key     = '';
    public string $iv      = '';
    public string $tag     = '';
    public string $aad     = '';
    public int $tagLen     = 16;
    public int $options    = PKCS7_TEXT;

}