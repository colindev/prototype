<?php namespace Rde;

class Prototype implements \ArrayAccess
{
    protected $drivers = array();

    public function __construct(array $drivers = array())
    {
        foreach ($drivers as $name => $driver) {
            $this->extend($name, $driver);
        }
    }

    public function extend($name, $callable)
    {
        $this->drivers[$name] = $callable;
    }

    public function hasDriver($name)
    {
        return isset($this->drivers[$name]);
    }

    public function __call($name, $args)
    {
        if ($this->hasDriver($name)) {
            array_unshift($args, $this);

            return \Rde\call($this->drivers[$name], $args);
        }

        throw new \BadMethodCallException("沒有安裝[{$name}]處理驅動");
    }

    public function offsetExists($key)
    {
        return isset($this->drivers[$key]);
    }

    public function offsetGet($key)
    {
        return $this->drivers[$key];
    }

    public function offsetSet($key, $val)
    {
        $this->extend($key, $val);
    }

    public function offsetUnset($key)
    {
        unset($this->drivers[$key]);
    }
}
