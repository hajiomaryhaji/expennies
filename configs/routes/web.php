<?php

declare(strict_types=1);

use App\Controllers\AuthenticateUser;
use App\Controllers\CategoryController;
use App\Controllers\HomeController;
use App\Controllers\PasswordResetController;
use App\Controllers\ProfileController;
use App\Controllers\ReceiptController;
use App\Controllers\RegisterUser;
use App\Controllers\TransactionController;
use App\Controllers\VerifyController;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\GuestMiddleware;
use App\Middlewares\RateLimitingMiddleware;
use App\Middlewares\ValidateSignedUrlMiddleware;
use App\Middlewares\VerifyMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app): void {
    $app->group('', function (RouteCollectorProxy $group): void {
        $group->get('/', [HomeController::class, 'index'])->setName('home');
        $group->get('/stats/{user}/ytd', [HomeController::class, 'getYearToDateStatistics']);

        $group->group('/categories', function (RouteCollectorProxy $categories): void {
            $categories->get('', [CategoryController::class, 'index'])->setName('categories');
            $categories->post('', [CategoryController::class, 'store']);
            $categories->get('/load', [CategoryController::class, 'load']);
            $categories->get('/{category:[0-9]+}', [CategoryController::class, 'show']);
            $categories->patch('/{category:[0-9]+}', [CategoryController::class, 'update']);
            $categories->delete('/{category:[0-9]+}', [CategoryController::class, 'destroy']);
        })->add(AuthMiddleware::class);

        $group->group('/transactions', function (RouteCollectorProxy $transactions): void {
            $transactions->get('', [TransactionController::class, 'index'])->setName('transactions');
            $transactions->post('', [TransactionController::class, 'store']);
            $transactions->get('/load', [TransactionController::class, 'load']);
            $transactions->get('/{transaction:[0-9]+}', [TransactionController::class, 'show']);
            $transactions->patch('/{transaction:[0-9]+}', [TransactionController::class, 'update']);
            $transactions->delete('/{transaction:[0-9]+}', [TransactionController::class, 'destroy']);
            $transactions->post('/import', [TransactionController::class, 'import']);
            $transactions->post('/{transaction:[0-9]+}/receipt', [ReceiptController::class, 'store']);
            $transactions->get('/{transaction:[0-9]+}/receipts/{receipt:[0-9]+}', [ReceiptController::class, 'download']);
            $transactions->delete('/{transaction:[0-9]+}/receipts/{receipt:[0-9]+}', [ReceiptController::class, 'destroy']);
            $transactions->post('/{transaction:[0-9]+}/review', [TransactionController::class, 'review']);
        });

        $group->group('/profile', function (RouteCollectorProxy $profile): void {
            $profile->get('/edit', [ProfileController::class, 'edit'])->setName('edit-profile');
            $profile->get('/{user}/show', [ProfileController::class, 'show']);
            $profile->patch('/{user}/update', [ProfileController::class, 'update']);
            $profile->patch('/{user}/update-password', [ProfileController::class, 'updatePassword']);
            $profile->post('/{user}/2fa', [AuthenticateUser::class, 'enableTwoFactor']);
            $profile->get('/{user}/2fa', [AuthenticateUser::class, 'getTwoFactor']);
        });

    })->add(VerifyMiddleware::class)->add(AuthMiddleware::class);

    $app->group('', function (RouteCollectorProxy $group): void {
        $group->post('/logout', [AuthenticateUser::class, 'destroy'])->setName('sign-out');
        $group->get('/verify', [VerifyController::class, 'index']);
        $group->get('/verify/{uuid}/{hash}', [VerifyController::class, 'validate'])->add(ValidateSignedUrlMiddleware::class)->setName('verify-signed-url');
        $group->post('/resendEmailVerification', [RegisterUser::class, 'resend'])->setName('resendEmailVerification')->add(RateLimitingMiddleware::class);
    })->add(AuthMiddleware::class);

    $app->group('', function (RouteCollectorProxy $guest): void {
        $guest->get('/register', [RegisterUser::class, 'create'])->setName('sign-up');
        $guest->post('/register', [RegisterUser::class, 'store'])->setName('register')->add(RateLimitingMiddleware::class);
        $guest->get('/authenticate', [AuthenticateUser::class, 'create'])->setName('sign-in');
        $guest->post('/authenticate', [AuthenticateUser::class, 'store'])->setName('authenticate')->add(RateLimitingMiddleware::class);
        $guest->post('/authenticate/2fa', [AuthenticateUser::class, 'twoFactorAuthenticate']);
        $guest->get('/forgot-password', [PasswordResetController::class, 'create']);
        $guest->post('/forgot-password', [PasswordResetController::class, 'store'])->setName('handle-forgot-password')->add(RateLimitingMiddleware::class);
        $guest->get('/reset-password/{token}', [PasswordResetController::class, 'showPasswordResetForm'])->setName('password-reset')->add(ValidateSignedUrlMiddleware::class);
        $guest->post('/reset-password/{token}', [PasswordResetController::class, 'resetPassword'])->setName('password-update')->add(RateLimitingMiddleware::class);
    })->add(GuestMiddleware::class);
};