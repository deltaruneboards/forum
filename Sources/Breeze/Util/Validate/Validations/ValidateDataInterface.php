<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations;

interface ValidateDataInterface
{
	public function setData(array $data): void;

	public function isValid(): void;

	public function successKeyString(): string;
}
