<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * Función que muestra todos los usuarios de nuestra aplicación
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     * @return Response
     */

    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('UserBundle:User')->findAll();

        // $res = 'Lista de usuarios: <br />';

        // foreach ($users as $user) {
        //     $res .= 'Usuario: ' . $user->getUsername() . ' - Email: ' . $user->getEmail() . '<br />';
        // }

        // return new Response($res);

        return $this->render('UserBundle:User:index.html.twig', array('users' => $users));
    }

    /**
     * Función que muestra un usuario determinado según su ID
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     * @return Response
     */

    public function viewAction($id) {
        $repository = $this->getDoctrine()->getRepository('UserBundle:User');

        $user = $repository->find($id);

        // $user = $repository->findOneByUsername($username);

        return new Response('Usuario: ' . $user->getUsername() . ' con email: ' . $user->getEmail());
    }
}
