<?php

declare(strict_types=1);

namespace App\Validators\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Entities\User;
use App\Exceptions\FormValidationException;
use Doctrine\ORM\EntityManagerInterface;
use Valitron\Validator;

class RegisterUserRequestValidator implements RequestValidatorInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {

    }

    public function validate(array $data): array
    {
        $v = new Validator($data);
        $v->rule('required', ['name', 'email', 'password', 'passwordConfirmation']);
        $v->rule('email', 'email');
        $v->rule('equals', 'passwordConfirmation', 'password')->label('Confirm your password');
        $v->rule(
            fn($field, $value, $params, $fields): bool =>
            !(bool) $this->entityManager->getRepository(User::class)->count(['email' => $value])
            ,
            'email'
        )->message('Oops! User with the given {field} address already exists.');

        if (!$v->validate()) {
            throw new FormValidationException($v->errors());
        }

        return $data;
    }
}