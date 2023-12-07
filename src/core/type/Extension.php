<?php

namespace dsf\Core\Type;

class Extension
{
  private $properties = [];
  public function __call($func, $args)
  {
    if (count($args) > 1)
      throw new \InvalidArgumentException($func . ' accepts only one argument.');

    $property = $this->getPropertyName($func);
    if (self::isGetterMethod($func)) {
      if (!property_exists($this, lcfirst($property))) {
        return $this->properties[lcfirst($property)] ?? null;
      }
      return $this->{lcfirst($property)};
    } else if (self::isSetterMethod($func)) {
      $this->properties[lcfirst($property)] = $args[0];
      return $this;
    } else {
      throw new \InvalidArgumentException(' method not found: ' . $func);
    }
  }

  private static function isSetterMethod($func)
  {
    return strpos($func, 'set') === 0;
  }

  private static function isGetterMethod($func)
  {
    return strpos($func, 'get') === 0;
  }

  private function getPropertyName($func)
  {
    return lcfirst(substr($func, 3));
  }
}