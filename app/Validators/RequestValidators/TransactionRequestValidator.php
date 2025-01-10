<?php

declare(strict_types=1);

namespace App\Validators\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exceptions\FormValidationException;
use App\Services\CategoryService;
use Valitron\Validator;

class TransactionRequestValidator implements RequestValidatorInterface
{
    public function __construct(private readonly CategoryService $categoryService)
    {

    }

    public function validate(array $data): array
    {

        $v = new Validator($data);

        $v->rule('required', ['description', 'amount', 'date', 'category']);
        $v->rule('lengthMax', 'description', 40);
        $v->rule('dateFormat', 'dateFormat', 'Y/m/d g:i');
        $v->rule('numeric', 'amount');
        $v->rule('integer', 'category');
        $v->rule(function ($field, $value, $params, $fields) use (&$data): bool {
            $id = (int) $value;

            if (!$id) {
                return false;
            }

            $category = $this->categoryService->find($id);

            if ($category) {
                $data['category'] = $category;

                return true;
            }

            return false;
        }, "category")->message("{field} not found");


        if (!$v->validate()) {
            throw new FormValidationException($v->errors());
        }

        return $data;
    }
}