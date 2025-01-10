<?php

declare(strict_types=1);

namespace App\Validators\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exceptions\FormValidationException;
use Valitron\Validator;

class UpdatePasswordRequestValidator implements RequestValidatorInterface
{
    public function validate(array $data): array
    {
        $v = new Validator($data);
        $v->rule('required', ['password', 'passwordConfirmation']);
        $v->rule('equals', 'passwordConfirmation', 'password')->label('Confirm your password');

        if (!$v->validate()) {
            throw new FormValidationException($v->errors());
        }

        return $data;
    }
}