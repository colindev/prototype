<?php namespace Rde;

class Prototype implements \ArrayAccess
{
    private $drivers = array();
    private $events = array();

    final public function extend($name, $callable)
    {
        $this->drivers[$name] = $callable;
    }

    final public function hasDriver($name)
    {
        return isset($this->drivers[$name]);
    }

    final public function on($name, $callable, $weights = 0)
    {
        $weights = (int) $weights;
        $this->events[$name][$weights][] = $callable;

        return $this;
    }

    private function fetchEvent($name, $timing)
    {
        return isset($this->events["{$name}.{$timing}"]) ?
            $this->events["{$name}.{$timing}"] :
            array();
    }

    private function fireEvent($name, $timing, $payload = array())
    {
        $events_list = $this->fetchEvent($name, $timing);
        ! empty($events_list) and krsort($events_list);
        foreach ($events_list as $events) {
            foreach ($events as $event) {
                if (false === $event($payload)) {
                    return;
                }
            }
        }
    }

    final public function __call($name, $args)
    {
        if ($this->hasDriver($name)) {
            $payload = $args;
            array_unshift($args, $this);

            $this->fireEvent($name, 'before', $payload);
            $ret = \Rde\call($this->drivers[$name], $args);
            $this->fireEvent($name, 'after', $payload);

            return $ret;
        }

        throw new \BadMethodCallException("沒有安裝[{$name}]處理驅動");
    }

    final public function offsetExists($key)
    {
        return isset($this->drivers[$key]);
    }

    final public function offsetGet($key)
    {
        return $this->drivers[$key];
    }

    final public function offsetSet($key, $val)
    {
        $this->extend($key, $val);
    }

    final public function offsetUnset($key)
    {
        unset($this->drivers[$key]);
    }
}
