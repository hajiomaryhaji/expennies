<?php

declare(strict_types=1);

namespace App\Services;

use App\ConfigParser;
use App\Entities\Email;
use App\Entities\User;
use App\Enums\EmailStatus;
use App\SignedUrl;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\BodyRendererInterface;

class EmailService
{
    public function __construct(
        private readonly EntityManagerService $entityManagerService,
        private readonly MailerInterface $mailer,
        private readonly TemplatedEmail $templatedEmail,
        private readonly ConfigParser $configParser,
        private readonly BodyRendererInterface $bodyRenderer,
        private readonly SignedUrl $signedUrl,
        private readonly ContainerInterface $container,
        private readonly UserLoginCodeService $userLoginCodeService,
        private readonly PasswordResetService $passwordResetService
    ) {

    }

    public function queue(string $subject, string $template, User $user): void
    {
        $from = new Address($this->configParser->get('email.from'), 'ICT Support Team');
        $to = new Address($user->getEmail(), $user->getName());

        $metadata['from'] = $from->toString();
        $metadata['to'] = $to->toString();

        $email = new Email();

        $email
            ->setSubject($subject)
            ->setHtmlBody($template)
            ->setMetadata(json_encode($metadata))
            ->setStatus(EmailStatus::Queued)
            ->setUser($user);

        $this->entityManagerService->sync($email);

    }

    public function send(): void
    {
        $queuedEmails = $this->entityManagerService
            ->getRepository(Email::class)
            ->createQueryBuilder('e')
            ->where('e.status = :status')->setParameter(':status', EmailStatus::Queued->value)
            ->getQuery()
            ->getResult();


        foreach ($queuedEmails as $queuedEmail) {
            $metadata = json_decode($queuedEmail->getMetadata(), true);

            $expirationDate = new \DateTime('+30 minutes');

            $context = match ($queuedEmail->getHtmlBody()) {
                'emails/signup.html.twig' => [
                    'activationLink' => $this->signedUrl->generate(
                        'verify-signed-url',
                        ['uuid' => $queuedEmail->getUser()->getId(), 'hash' => sha1($queuedEmail->getUser()->getEmail())],
                        ['expirationTime' => $expirationDate->getTimestamp()]
                    ),
                    'expirationDate' => $expirationDate
                ],
                'emails/two-factor.html.twig' => [
                    'code' => $this->userLoginCodeService->generate($queuedEmail->getUser())->getCode()
                ],
                'emails/password-reset.html.twig' => [
                    'resetLink' => $this->signedUrl->generate(
                        'password-reset',
                        ['token' => $this->passwordResetService->getToken($queuedEmail->getUser()->getEmail())->getToken()],
                        ['expirationTime' => $expirationDate->getTimestamp()]
                    )
                ]
            };

            $message = $this->templatedEmail
                ->from($metadata['from'])
                ->to($metadata['to'])
                ->subject($queuedEmail->getSubject())
                ->htmlTemplate($queuedEmail->getHtmlBody())
                ->context($context);

            $this->bodyRenderer->render($message);

            $this->mailer->send($message);

            $queuedEmail->setStatus(EmailStatus::Sent);

            $this->entityManagerService->sync($queuedEmail);
        }
    }
}