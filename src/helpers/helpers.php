<?php

use dsf\Core\Builder;
use dsf\Core\Type\Extension;
use dsf\Render\Render;

function addInputHTMLForm(string $type, ...$args)
{
  if (!Builder::isTypeHTMLInput($type)) {
    throw new \Exception('The type ' . $type . ' is not a valid type');
  }

  $properties = array_merge(
    $type !== 'button' ? ['name'] : [],
    in_array($type, ['textarea', 'button']) ? ['text'] : [],
    in_array($type, ['select', 'radio', 'checkbox']) ? ['options', 'selected'] : [],
    ['attrs']
  );

  $data = array_merge(
    ['type' => !in_array($type, ['select', 'radio', 'checkbox']) ? ($type === 'button' ? 'BUTTON' : 'INPUT') : 'CHOICE'],
    array_combine($properties, array_pad($args, count($properties), null))
  );

  $field = array_reduce(array_keys($data), function ($extension, $property) use ($data, $type) {
    if ($property === 'attrs') {
      $data = array_merge(
        $data,
        ['attrs' => array_merge(
          Builder::setAttrsDefaults(in_array($data['type'], ['INPUT', 'CHOICE']) ? ['type' => $type] : [],  'FIELD'),
          $data['attrs'] ?? []
        )]
      );
    }

    if (!is_null($data[$property])) {
      Builder::validateData([$property => $data[$property]]);
      $func = 'set' . ucfirst($property);
      $extension->$func($data[$property]);
    }
    return $extension;
  }, new Extension);

  return Render::renderTypeExtension($field);
}
