<?php

declare(strict_types=1);

namespace App\Validators\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exceptions\FormValidationException;
use Valitron\Validator;

class PasswordResetRequestValidator implements RequestValidatorInterface
{
    public function validate(array $data): array
    {
        $v = new Validator($data);
        $v->rule('required', 'email');
        $v->rule('email', 'email');

        if (!$v->validate()) {
            throw new FormValidationException($v->errors());
        }


        return $data;
    }
}