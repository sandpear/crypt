<?php
/**
 * CryptInterface
 */
namespace Sandpear\Crypt\lib;

interface CryptInterface
{
    /**
     * @param string $value
     * @return mixed
     */
    public function encrypt(string $value);
    public function decrypt(string $value);
}