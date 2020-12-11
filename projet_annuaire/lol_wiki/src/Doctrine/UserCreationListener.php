<?php


namespace App\Doctrine;


use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class UserCreationListener
{

    private MailerInterface $mailer;

    private UrlGeneratorInterface $urlGenerator;

    /**
     * UserCreationListener constructor.
     * @param MailerInterface $mailer
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(MailerInterface $mailer, UrlGeneratorInterface $urlGenerator)
    {
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param User $user
     * @ORM\PostPersist()
     * @throws TransportExceptionInterface
     */
    public function validateEmail(User $user) {
        $email = (new TemplatedEmail())
            ->from('student@bes-webdeveloper-seraing.be')
            ->to($user->getEmail())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('You created an account')
            ->text('Bienvenue dans la communauté LolWiki !')
            ->context([
                'user' => $user
            ])
        ;

        $this->mailer->send($email);
    }

}
