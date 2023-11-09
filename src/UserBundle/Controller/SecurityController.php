<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{

    /**
     * Función que renderiza a la vista del formulario de inicio de sesión
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     *
     * @return Response
     */
    public function loginAction() {
        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername(); // Recupera el último nombre de usuario si se ha logeado de forma incorrecta

        return $this->render('UserBundle:Security:login.html.twig', array('last_username' => $lastUsername, 'error' => $error));
    }

    public function loginCheckAction() {

    }
}