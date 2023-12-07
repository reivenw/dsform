<?php

namespace dsf\Core\Type;

use dsf\Core\Type\Extension;
use dsf\Render\Render;

class PwControl
{
	private const INPUT_CONTROL_DATA_TYPES = [
		'generator' => 'Generate',
		'canceler' => 'Cancel',
		'enabler' => 'Set',
		'hider' => 'Show'
	];

	public static $enabled = true;

	private static $controls = [];

	private static function getInputTypeControls(array $controls, array $unuses): array
	{
		if (empty(self::$controls)) {
			self::setInputTypeControls($controls, $unuses);
		}
		return self::$controls;
	}

	private static function setInputTypeControls(array $controls, array $unuses): void
	{
		$controlsType = array_keys(self::INPUT_CONTROL_DATA_TYPES);

		foreach ($unuses as $key => $unuse) {
			if (!in_array($unuse, $controlsType)) {
				throw new \InvalidArgumentException(sprintf('The %s control is not valid.', $unuse));
			}
			unset($controlsType[array_search($unuse, $controlsType)]);
		}

		if (in_array('enabler', $controlsType)) {
			unset($controlsType[array_search('generator', $controlsType)]);
		} else {
			unset($controlsType[array_search('canceler', $controlsType)]);
		}

		foreach ($controlsType as $action) {
			$text = $controls[$action]['text'] ?? self::INPUT_CONTROL_DATA_TYPES[$action];
			$attrs = $controls[$action]['attrs'] ?? [];

			if ($action === 'hider') {
				$attrs = array_merge($attrs, [
					'aria-label-hidden' => $attrs['aria-label-hidden'] ?? 'Hide',
					'aria-label' => $text
				]);
			}
			self::$controls[$action] = self::createInputTypeButton($attrs, $action, $text);
		}
	}

	private static function createInputTypeButton(array $attrs, string $action, string $text): Extension
	{
		return (new  Extension)
			->setType('BUTTON')
			->setAttrs(array_merge($attrs, ['data-action' => $action]))
			->setText($text);
	}

	public static function getControls(array $controls = [], array $unuses = []): string
	{
		$actions = self::getInputTypeControls($controls, $unuses);
		$controlOutput = '';
		if (in_array('enabler', array_keys($actions))) {
			self::$enabled = false;
		}
		foreach ($actions as $action => $control) {
			$controlOutput .= sprintf(
				'<div class=\'dsf_pass_action\' %s >%s</div>',
				!self::$enabled && $action !== 'enabler' ? 'data-enabled=\'false\'' : '',
				Render::renderTypeExtension($control)
			);
		}

		return sprintf(
			'<div id=\'pass-strength-result\' %s></div><div class=\'pass-controls\'>%s</div>',
			!self::$enabled && $action !== 'enabler' ? 'data-enabled=\'false\'' : '',
			$controlOutput
		);
	}
}
