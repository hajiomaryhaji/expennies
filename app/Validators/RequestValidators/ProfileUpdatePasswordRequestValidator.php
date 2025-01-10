<?php

declare(strict_types=1);

namespace App\Validators\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Entities\User;
use App\Exceptions\FormValidationException;
use Valitron\Validator;

class ProfileUpdatePasswordRequestValidator implements RequestValidatorInterface
{
    public function validate(array $data): array
    {
        $v = new Validator($data);
        $v->rule('required', ['currentPassword', 'newPassword', 'confirmPassword']);
        $v->rule('equals', 'passwordConfirmation', 'password')->label('Confirm your password');

        if (!$v->validate()) {
            throw new FormValidationException($v->errors());
        }

        return $data;
    }
}