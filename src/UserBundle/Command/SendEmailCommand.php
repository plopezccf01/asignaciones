<?php 
namespace UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendEmailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cmd:SendEmail')
            ->setDescription('command description')
            //->addArgument('id', InputArgument::REQUIRED, 'Id')
            //->addArgument('type', InputArgument::REQUIRED, 'Tipo')
        ;
    }

    //Esta función es llamada cada hora desde una tarea del servidor para mandar un correo a los proveedores juniors

    protected function execute(InputInterface $input, OutputInterface $output) {

        $container      =  $this->getContainer();
        $em             = $container->get('doctrine')->getEntityManager();
        $emailService   = $container->get('email_service');


        $contacts       = $em->getRepository('UserBundle:Contact')->findBy(
            array(
                'sendEmail' => false
            )
        );

        if (empty($contacts)) {
            $output->writeln('No se han obtenido contactos para enviar correos');
            return false;
        }

        foreach ($contacts as $contact) {
            $params = array(
                'name' => $contact->getName(),
                'description' => $contact->getDescription(),
            );

            if (empty($contact->getEmail())) {
                $message = 'No se ha encontrado el correo';
                continue;
            }

            $result = $emailService->send(
                'Prueba', 
                'pablo.lopez@eurotransportcar.com', 
                $contact->getEmail(), 
                'UserBundle:Emails:emailContact.html.twig', 
                $params
            );

            if ($result == 1) {
                $contact->getSendEmail(true);
                $em->persist($contact);
                $em->flush();
            }
        }
    }
}
?>