<?php

declare(strict_types=1);

namespace App\Validators\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exceptions\FormValidationException;
use Valitron\Validator;

class CreateCategoryRequestValidator implements RequestValidatorInterface
{
    public function validate(array $data): array
    {
        $v = new Validator($data);

        $v->rule('required', 'name');
        $v->rule('lengthMax', 'name', 35);

        if (!$v->validate()) {
            throw new FormValidationException($v->errors());
        }

        return $data;
    }
}