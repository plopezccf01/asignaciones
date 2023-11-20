<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends Controller
{
    protected $container;
    private $em;
    private $contactService;

    public function setContainer(ContainerInterface $container = null) {

        parent::setContainer($container);

        $this->em                           = $this->getDoctrine()->getManager();
        $this->contactService               = $this->get('contact_service');
    }

    /**
     * Función que renderiza la vista de las tareas
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     *
     * @return Response
     */
    public function indexAction() {

        return $this->render('UserBundle:Contact:index.html.twig');
    }

    public function addContactDataAction(Request $request) {
        $result = $this->contactService->validateContactData($request->request->all());

        if (!$result['status']) {
            return new JsonResponse($result);
        }

        return new JsonResponse($this->contactService->newContact($request->request->all()));
    }
}