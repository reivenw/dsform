<?php

namespace dsf\Core;

use dsf\Core\Type\Extension;

class Builder
{
  private $extensions = [];
  private $current = [];
  private $attrs = [];
  private $submit = null;
  private const PREFIXES = [
    'FORM' => 'dsf',
    'FIELD' => 'dsf_control',
    'SECTION' => 'dsf_section'
  ];

  private const TYPE_EXTENSIONS_MERGE = [
    'SECTION' => ['fields', 'title'],
    'INPUT' => [],
    'TEXTAREA' => ['text'],
    'BUTTON' => ['text'],
    'CUSTOM' => ['html'],
    'CHOICE' => ['options', 'selected']
  ];

  private static $properties = [];

  public function add(string $type, ...$args): self
  {

    self::$properties = array_merge(self::$properties, $type !== 'section' ? ['label'] : [], ['description', 'deps', 'output']);

    $field = self::_add($type, ...$args);

    if (!empty($this->current)) {
      if ($type === 'section') {
        throw new \Exception('you can\'t add a section inside a section');
      }
      $this->current[0]->setFields($field);
    } elseif ($type === 'section') {
      $this->current[] = $field;
    } else {
      $this->extensions[] = $field;
    }
    return $this;
  }

  public static function _add(string $type, ...$args): Extension
  {

    $data = ['type' => explode(':', $type)[0]];
    if (in_array($data['type'], ['INPUT', 'CHOICE']) && !self::isTypeHTMLInput(explode(':', $type)[1])) {
      throw new \Exception('The type ' . $data['type'] . ' is not a valid type');
    }

    if (!in_array($data['type'], array_keys(self::TYPE_EXTENSIONS_MERGE))) {
      throw new \Exception('The type ' . $data['type'] . ' is not a valid type');
    }

    $properties = array_merge(
      !in_array($data['type'], ['CUSTOM', 'SECTION', 'BUTTON']) ? ['name'] : [],
      self::TYPE_EXTENSIONS_MERGE[$data['type']],
      $data['type'] !== 'CUSTOM' ? ['attrs'] : [],
      self::$properties
    );
    self::$properties = [];

    $args = array_pad($args, count($properties), null);
    $data = array_merge($data, array_combine($properties, $args));

    $field = array_reduce(array_keys($data), function ($extension, $property) use ($data, $type) {
      if ($property === 'attrs') {
        $data = array_merge(
          $data,
          ['attrs' => array_merge(
            self::setAttrsDefaults(
              in_array($data['type'], ['INPUT', 'CHOICE']) ? ['type' => explode(':', $type)[1], 'name' => $data['name']] : [],
              $data['type'] !== 'SECTION' ? 'FIELD' : 'SECTION'
            ),
            $data['attrs'] ?? []
          )]
        );
      }

      if (!is_null($data[$property])) {
        self::validateData([$property => $data[$property]]);
        $func = 'set' . ucfirst($property);
        $extension->$func($data[$property]);
      }
      return $extension;
    }, new \dsf\Core\Type\Extension);

    return $field;
  }

  public function end(): self
  {
    if (!empty($this->current)) {
      array_push($this->extensions, $this->current[0]);
      $this->current = [];
    }
    return $this;
  }

  public static function setAttrsDefaults(array $attrs, string $prefix = 'FIELD'): array
  {
    extract($attrs, EXTR_OVERWRITE);
    $prefix = self::PREFIXES[$prefix];
    return array_merge(
      $attrs,
      ['id' => $id ?? $prefix . '_' . uniqid(), 'class' =>  isset($class) ? $prefix . ' ' . $class : $prefix],
      $prefix === 'dsf' ? ['method' => $method ?? 'POST', 'action' => $action ?? '#'] : ['autocomplete' => $autocomplete ?? 'off']
    );
  }

  public static function validateData(array $property): void
  {
    $typeChecks = [
      'array' => ['attrs', 'dependencies', 'fields', 'options', 'selected'],
      'string' => ['name', 'label', 'description', 'title', 'text', 'html', 'type'],
      'callable' => ['output']
    ];

    foreach ($property as $key => $value) {
      foreach ($typeChecks as $type => $keys) {
        if (in_array($key, $keys)) {
          $checkFunction = 'is_' . $type;
          if (!$checkFunction($value)) {
            throw new \Exception('The value of ' . $key . ' must be a ' . $type);
          }
        }
      }
    }
  }

  public function getExtensions(): array
  {
    return $this->extensions;
  }

  public function getAttrs(): array
  {
    $this->attrs = self::setAttrsDefaults($this->attrs ?? [], 'FORM');
    return $this->attrs;
  }

  public function setAttrs($attrs): void
  {
    $this->attrs = $attrs;
  }

  public static function isTypeHTMLInput(string $type): bool
  {
    return in_array($type, ['text', 'password', 'checkbox', 'radio', 'select', 'textarea', 'submit', 'reset', 'file', 'hidden', 'image', 'button', 'color', 'date', 'datetime-local', 'email', 'month', 'number', 'range', 'search', 'tel', 'time', 'url', 'week']);
  }

  public function setSubmit(string $text, array $attrs = []): void
  {
    $this->submit = $this->_add('BUTTON', $text, $attrs);
  }

  public function getSubmit(): Extension
  {
    if (is_null($this->submit)) $this->setSubmit('Submit');
    return $this->submit;
  }
}
