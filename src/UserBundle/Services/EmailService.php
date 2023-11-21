<?php

namespace UserBundle\Services;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EmailService
{
    private $em;
    private $mailer;
    private $container;

    public function __construct($entityManager, $mailer, $container) {
        $this->em                   = $entityManager;
        $this->mailer               = $mailer;
        $this->container            = $container;
    }

    public function send($subject, $from, $to, $template, $params) {
        $templateRender = $this->container->get('templating')->render($template, $params);
        // Crear un objeto Swift_Message
        $message = $this->mailer->createMessage()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setBody($templateRender, 'text/html');

        try {
            if ($this->mailer->send($message)) {
                return 1;
            } else{
                return 0;
            }

        } catch (\Throwable $th) {
            throw new Exception($th);
            return 0;
        }
    }
}

