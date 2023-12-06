<?php

namespace dsf\Render;

class Render
{
  private $builder;

  public function __construct($builder)
  {
    $this->builder = $builder;
  }

  public static function renderTypeExtension($extension)
  {

    $attrs = $extension->getType() === 'CHOICE' && in_array($extension->getAttrs()['type'] ?? '', ['radio', 'checkbox'])
      ? '' : self::renderTypeAttrs($extension->getAttrs() ?? []);

    $type = !in_array($extension->getType(), ['INPUT', 'CHOICE']) ? strtolower($extension->getType()) : (strtolower($extension->getAttrs()['type'] ?? ''));

    $templates = [
      'select' => function () use ($attrs, $extension) {
        return sprintf('<select %s>%s</select>', $attrs, self::renderTypeOptions($extension->getOptions() ?? [], $extension->getSelected() ?? []));
      },
      'radio' => function () use ($extension) {
        return self::renderInputChoice($extension);
      },
      'checkbox' => function () use ($extension) {
        return self::renderInputChoice($extension);
      },
      'textarea' => function () use ($attrs, $extension) {
        return sprintf('<textarea %s>%s</textarea>', $attrs, $extension->getText());
      },
      'button' => function () use ($attrs, $extension) {
        return sprintf('<button %s>%s</button>', $attrs, $extension->getText());
      },
      'custom' => function () use ($extension) {
        return $extension->getHtml();
      },
      'section' => function () use ($extension) {
        return sprintf('<div class=\'dsf_section\'><h2>%s</h2>%s</div>', $extension->getTitle(), self::renderFormExtensions($extension->getFields()));
      },
      'default' => function () use ($attrs) {
        return sprintf('<input %s>', $attrs);
      }
    ];

    return $templates[in_array($type, array_keys($templates)) ? $type : 'default']();
  }

  private static function renderInputChoice($extension)
  {
    $options = $extension->getOptions() ?? [];
    $attrs = $extension->getAttrs() ?? [];
    $selected = $extension->getSelected() ?? [];

    return implode('', array_map(function ($key, $value) use ($attrs, $selected) {

      $attrs = self::renderTypeAttrs(array_merge($attrs, ['id' => $attrs['id'] . '_' . $key]));

      return sprintf('<input %s value="%s" %s>%s</input>', $attrs, $key, in_array($key, $selected) ? 'checked' : '', $value);
    }, array_keys($options), $options));
  }

  private static function renderTypeAttrs(array $attrs): string
  {
    return implode(' ', array_map(function ($key, $value) {
      return $key . '="' . $value . '"';
    }, array_keys($attrs), $attrs));
  }

  private static function renderTypeOptions(array $options, array $selected): string
  {
    return implode('', array_map(function ($key, $value) use ($selected) {
      return sprintf('<option value="%s" %s>%s</option>', $key, in_array($key, $selected) ? 'selected' : '', $value);
    }, array_keys($options), $options));
  }

  private static function renderFormExtensions($extensions)
  {
    return implode('', array_map(function ($extension) {
      return sprintf(
        '<div class=\'dsf_extensions_control\' %s %s >%s</div>',
        !empty($extension->getDeps()) ? sprintf(' data-deps=\'%s\'',  json_encode($extension->getDeps())) : '',
        $extension->getType() !== 'custom' && !isset($extension->getAttrs()['id']) ? 'data-id=custom_' . uniqid() : sprintf(' data-id=\'%s_extension\'', $extension->getAttrs()['id']),
        $extension->getType() !== `section`
          ? sprintf(
            '%s<div class=\'dsf_wrap\'>%s%s%s</div>',
            !empty($extension->getLabel())
              ? sprintf(
                '<%1$s %2$s >%3$s</%1$s>',
                $extension->getType() === 'CHOICE' && in_array($extension->getAttrs()['type'] ?? '', ['radio', 'checkbox']) ? 'span' : 'label',
                $extension->getType() === 'CHOICE' && in_array($extension->getAttrs()['type'] ?? '', ['radio', 'checkbox']) ? '' : 'for=\'' . ($extension->getAttrs()['id'] ?? '') . '\'',
                $extension->getLabel()
              )
              : '',
            !empty($extension->getDescription()) ? sprintf('<p class=\'dsf_description\'>%s</p>', $extension->getDescription()) : '',
            self::renderTypeExtension($extension),
            !is_null($extension->getOutput()) ? ($extension->getOutput())() : ''
          )
          : '',
      );
    }, $extensions));
  }

  public function render()
  {
    return sprintf(
      '<form %s ><div class=\'dsf_extensions\'>%s</div><div class=\'dsf_submit\' >%s</div></form>',
      self::renderTypeAttrs($this->builder->getAttrs()),
      self::renderFormExtensions($this->builder->getExtensions()),
      self::renderTypeExtension($this->builder->getSubmit())
    );
  }
}
