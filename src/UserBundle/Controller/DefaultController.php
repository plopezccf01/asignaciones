<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * Función que renderiza la página principal del módulo de usuarios
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('UserBundle:Default:index.html.twig');
    }

    /**
     * Función que renderiza una página con el nombre de la URL
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     * @param   string $name
     * @return  Response
     */
    public function exampleAction($name)
    {
        return $this->render('UserBundle:Default:index.html.twig', array('name' => $name));
    }
}
