<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\Entities\Category;
use App\Helpers\ResponseFormatter;
use App\Services\CategoryService;
use App\Services\RequestService;
use App\Validators\RequestValidators\CreateCategoryRequestValidator;
use App\Validators\RequestValidators\UpdateCategoryRequestValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class CategoryController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly CategoryService $categoryService,
        private readonly RequestService $requestService
    ) {

    }

    public function index(Response $response): Response
    {
        return $this->twig->render($response, 'categories/index.html.twig');
    }

    public function load(Request $request, Response $response): Response
    {
        $params = $this->requestService->getDataTablesQueryParams($request);

        $filteredCategories = $this->categoryService->dataTable($params);

        $formatCategories = function (Category $category): array {
            return [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'createdAt' => $category->getCreatedAt()->format('d-m-Y h:i:s A'),
                'updatedAt' => $category->getUpdatedAt()->format('d-m-Y h:i:s A')
            ];
        };

        $totalRecords = count($filteredCategories);

        return ResponseFormatter::json($response, [
            'data' => array_map($formatCategories, (array) $filteredCategories->getIterator()),
            'draw' => $params->draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords
        ]);

    }

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(CreateCategoryRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $this->categoryService->create($data['name'], $request->getAttribute('user'));

        return $response;
    }

    public function show(Response $response, Category $category): Response
    {
        $data = [
            'id' => $category->getId(),
            'name' => $category->getName()
        ];

        return ResponseFormatter::json($response, $data);
    }

    public function update(Request $request, Response $response, Category $category): Response
    {
        $data = $this->requestValidatorFactory->make(UpdateCategoryRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $this->categoryService->update($category, $data['name']);

        return $response;
    }

    public function destroy(Response $response, Category $category): Response
    {
        $this->categoryService->delete($category, true);

        return $response;
    }
}