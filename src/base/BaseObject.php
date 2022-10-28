<?php
/**
 * BaseObject
 */
namespace Sandpear\Crypt\base;

use Sandpear\Crypt\base\exception\BoxException;

class BaseObject
{

    public static function className(): string
    {
        return get_called_class();
    }

    public function __construct($config = [])
    {
        if (!empty($config)) {
            self::configure($this, $config);
        }
        $this->init();
    }

    public static function configure($object, $properties)
    {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }

    public function init()
    {
    }

    /**
     * @throws BoxException
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (method_exists($this, 'set' . $name)) {
            throw new BoxException('Getting write-only property: ' . get_class($this) . '::' . $name);
        }

        throw new BoxException('Getting unknown property: ' . get_class($this) . '::' . $name);
    }

    /**
     * @throws BoxException
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new BoxException('Setting read-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new BoxException('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    public function __isset($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        }

        return false;
    }

    /**
     * @throws BoxException
     */
    public function __unset($name)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter(null);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new BoxException('Unsetting read-only property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * @throws BoxException
     */
    public function __call($name, $params)
    {
        throw new BoxException('Calling unknown method: ' . get_class($this) . "::$name()");
    }

    public function hasProperty($name, $checkVars = true): bool
    {
        return $this->canGetProperty($name, $checkVars) || $this->canSetProperty($name, false);
    }

    public function canGetProperty($name, $checkVars = true): bool
    {
        return method_exists($this, 'get' . $name) || $checkVars && property_exists($this, $name);
    }

    public function canSetProperty($name, $checkVars = true): bool
    {
        return method_exists($this, 'set' . $name) || $checkVars && property_exists($this, $name);
    }

    public function hasMethod($name): bool
    {
        return method_exists($this, $name);
    }
}
