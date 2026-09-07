<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

if (!defined('SMF'))
	die('Hacking attempt...');

function arcadeIsSerialized($data, $strict = true) {
	// If it isn't a string, it isn't serialized.
	if ( ! is_string( $data ) ) {
		return false;
	}
	$data = trim( $data );
	if ( 'N;' === $data ) {
		return true;
	}
	if ( strlen( $data ) < 4 ) {
		return false;
	}
	if ( ':' !== $data[1] ) {
		return false;
	}
	if ( $strict ) {
		$lastc = substr( $data, -1 );
		if ( ';' !== $lastc && '}' !== $lastc ) {
			return false;
		}
	} else {
		$semicolon = strpos( $data, ';' );
		$brace     = strpos( $data, '}' );
		// Either ; or } must exist.
		if ( false === $semicolon && false === $brace ) {
			return false;
		}
		// But neither must be in the first X characters.
		if ( false !== $semicolon && $semicolon < 3 ) {
			return false;
		}
		if ( false !== $brace && $brace < 4 ) {
			return false;
		}
	}
	$token = $data[0];
	switch ( $token ) {
		case 's':
			if ( $strict ) {
				if ( '"' !== substr( $data, -2, 1 ) ) {
					return false;
				}
			} elseif ( ! str_contains( $data, '"' ) ) {
				return false;
			}
			// Or else fall through.
		case 'a':
		case 'O':
		case 'E':
			return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
		case 'b':
		case 'i':
		case 'd':
			$end = $strict ? '$' : '';
			return (bool) preg_match( "/^{$token}:[0-9.E+-]+;$end/", $data );
	}
	return false;
}

function _arcade_safe_unserialize($str)
{
	// Input  is not a string.
	if (empty($str) || !is_string($str))
		return false;

	// The substring 'O:' is used to serialize objects.
	// If it is not present, then there are none in the serialized data.
	if (strpos($str, 'O:') === false) {
		$dbArray = @unserialize($str);
		if ($dbArray === false) {
			$dbArray =  @unserialize(utf8_decode($str));
			if (!empty($dbArray))
				return array_map('utf8_encode', $dbArray);
			else
				return array();
		}
	}

	$stack = array();
	$expected = array();

	/*
	 * states:
	 *   0 - initial state, expecting a single value or array
	 *   1 - terminal state
	 *   2 - in array, expecting end of array or a key
	 *   3 - in array, expecting value or another array
	 */
	$state = 0;
	while ($state != 1)
	{
		$type = isset($str[0]) ? $str[0] : '';
		if ($type == '}')
			$str = substr($str, 1);

		elseif ($type == 'N' && $str[1] == ';')
		{
			$value = null;
			$str = substr($str, 2);
		}
		elseif ($type == 'b' && preg_match('/^b:([01]);/', $str, $matches))
		{
			$value = $matches[1] == '1' ? true : false;
			$str = substr($str, 4);
		}
		elseif ($type == 'i' && preg_match('/^i:(-?[0-9]+);(.*)/s', $str, $matches))
		{
			$value = (int) $matches[1];
			$str = $matches[2];
		}
		elseif ($type == 'd' && preg_match('/^d:(-?[0-9]+\.?[0-9]*(E[+-][0-9]+)?);(.*)/s', $str, $matches))
		{
			$value = (float) $matches[1];
			$str = $matches[3];
		}
		elseif ($type == 's' && preg_match('/^s:([0-9]+):"(.*)/s', $str, $matches) && substr($matches[2], (int) $matches[1], 2) == '";')
		{
			$value = substr($matches[2], 0, (int) $matches[1]);
			$str = substr($matches[2], (int) $matches[1] + 2);
		}
		elseif ($type == 'a' && preg_match('/^a:([0-9]+):{(.*)/s', $str, $matches))
		{
			$expectedLength = (int) $matches[1];
			$str = $matches[2];
		}

		// Object or unknown/malformed type.
		else
			return false;

		switch ($state)
		{
			case 3: // In array, expecting value or another array.
				if ($type == 'a')
				{
					$stack[] = &$list;
					$list[$key] = array();
					$list = &$list[$key];
					$expected[] = $expectedLength;
					$state = 2;
					break;
				}
				if ($type != '}')
				{
					$list[$key] = $value;
					$state = 2;
					break;
				}

				// Missing array value.
				return false;

			case 2: // in array, expecting end of array or a key
				if ($type == '}')
				{
					// Array size is less than expected.
					if (count($list) < end($expected))
						return false;

					unset($list);
					$list = &$stack[count($stack) - 1];
					array_pop($stack);

					// Go to terminal state if we're at the end of the root array.
					array_pop($expected);

					if (count($expected) == 0)
						$state = 1;

					break;
				}

				if ($type == 'i' || $type == 's')
				{
					// Array size exceeds expected length.
					if (count($list) >= end($expected))
						return false;

					$key = $value;
					$state = 3;
					break;
				}

				// Illegal array index type.
				return false;

			// Expecting array or value.
			case 0:
				if ($type == 'a')
				{
					$data = array();
					$list = &$data;
					$expected[] = $expectedLength;
					$state = 2;
					break;
				}

				if ($type != '}')
				{
					$data = $value;
					$state = 1;
					break;
				}

				// Not in array.
				return false;
		}
	}

	// Trailing data in input.
	if (!empty($str))
		return false;

	return $data;
}

function arcade_safe_unserialize($str)
{
	// Make sure we use the byte count for strings even when strlen() is overloaded by mb_strlen()
	if (function_exists('mb_internal_encoding') &&
		(((int) ini_get('mbstring.func_overload')) & 0x02))
	{
		$mbIntEnc = mb_internal_encoding();
		mb_internal_encoding('ASCII');
	}

	$out = _arcade_safe_unserialize($str);

	if (isset($mbIntEnc))
		mb_internal_encoding($mbIntEnc);

	return $out;
}

function arcade_flatten(array $data): array
{
	$flattenData = arcade_doFlatten($data);

	list($newData, $metadata, $metadataKey, $fieldSeparator, $metadataSeparator, $metadataPlaceholder) = [[], [], '_metadata', '.', '/', '*'];

	foreach ($flattenData as $key => $value) {
		unset($flattenData[$key]);

		/** @var string $metadataKey */
		$metadataKey = \preg_replace('/' . \preg_quote($metadataSeparator, '/') . '(\d+)' . \preg_quote($metadataSeparator, '/') . '/', $metadataSeparator, $key, -1);
		/** @var string $metadataKey */
		$metadataKey = \preg_replace('/' . \preg_quote($metadataSeparator, '/') . '(\d+)$/', '', $metadataKey, -1);
		$newKey = \str_replace($metadataSeparator, $fieldSeparator, $metadataKey);

		if ($newKey === $key) {
			$newData[$newKey] = $value;

			continue;
		}

		if ($metadataKey === $key) {
			$newData[$newKey] = $value;
			$newValue = [$value];
		} else {
			$newValue = \is_array($value) ? $value : [$value];
			$oldValue = ($newData[$newKey] ?? []);

			\assert(\is_array($oldValue), 'Expected old value of key "' . $newKey . '" to be an array got "' . \get_debug_type($oldValue) . '".');

			$newData[$newKey] = [
				...$oldValue,
				...$newValue,
			];
		}

		if (\str_contains($metadataKey, $metadataSeparator)) {
			foreach ($newValue as $v) {
				$metadata[$metadataKey][] = \preg_replace_callback('/[^' . \preg_quote($metadataSeparator, '/') . ']+/', fn ($matches) => \is_numeric($matches[0]) ? $matches[0] : $metadataPlaceholder, $key);
			}
		}
	}

	if ([] !== $metadata) {
		$newData[$metadataKey] = \json_encode($metadata, \JSON_THROW_ON_ERROR);
	}

	return $newData;
}

function arcade_doFlatten(array $data, string $prefix = ''): array
{
	list($newData, $metadataSeparator) = [[], '/'];

	foreach ($data as $key => $value) {
		if (!\is_array($value)
			|| [] === $value
		) {
			$newData[$prefix . $key] = $value;

			continue;
		}

		$flattened = arcade_doFlatten($value, $key . $metadataSeparator);
		foreach ($flattened as $subKey => $subValue) {
			$newData[$prefix . $subKey] = $subValue;
		}
	}

	return $newData;
}

function arcade_unflatten(array $data): array
{
	list($newData, $metadata, $metadataKeyMapping, $metadataKey, $fieldSeparator, $metadataSeparator, $metadataPlaceholder) = [[], [], [], '_metadata', '.', '/', '*'];

	$metadataKey = '_metadata';
	if (\array_key_exists($metadataKey, $data)) {
		\assert(\is_string($data[$metadataKey]), 'Expected metadata to be a string.');

		/** @var array<string, array<string>> $metadata */
		$metadata = \json_decode($data[$metadataKey], true, flags: \JSON_THROW_ON_ERROR);

		foreach (\array_keys($metadata) as $subMetadataKey) {
			$metadataKeyMapping[\str_replace($metadataSeparator, $fieldSeparator, $subMetadataKey)] = $subMetadataKey;
		}

		unset($data[$metadataKey]);
	}

	foreach ($data as $key => $value) {
		$metadataKey = $metadataKeyMapping[$key] ?? null;
		if (null === $metadataKey) {
			$newData[$key] = $value;

			continue;
		}

		$keyParts = \explode($metadataSeparator, $metadataKey);
		if (!\is_array($value)) {
			$value = [$value];
		}

		foreach ($value as $subKey => $subValue) {
			\assert(\array_key_exists($subKey, $metadata[$metadataKey]), 'Expected key "' . $subKey . '" to exist in "' . $key . '".');

			$keyPartsReplacements = $keyParts;

			/** @var string $newKeyPath */
			$newKeyPath = \preg_replace_callback('/' . \preg_quote($metadataPlaceholder, '/') . '/', function () use (&$keyPartsReplacements) {
				return \array_shift($keyPartsReplacements);
			}, $metadata[$metadataKey][$subKey]);

			$newSubData = &$newData;
			foreach (\explode($metadataSeparator, $newKeyPath) as $newKeyPart) {
				$newSubData = &$newSubData[$newKeyPart]; // @phpstan-ignore-line
			}

			$newSubData = $subValue;
		}
	}

	return $newData;
}

?>