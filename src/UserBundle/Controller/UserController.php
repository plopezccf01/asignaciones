<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UserBundle\Entity\User;
use UserBundle\Form\UserType;

class UserController extends Controller
{
    /**
     * Función que renderiza la vista de los usuarios
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     * @return Render
     */

    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('UserBundle:User')->findAll();

        return $this->render('UserBundle:User:index.html.twig', array('users' => $users));
    }

    /**
     * Función que renderiza la vista del formulario de nuevos usuarios
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     * @return Render
     */

    public function addAction() {
        $user = new User();
        $form = $this->createCreateForm($user);

        return $this->render('UserBundle:User:add.html.twig', array('form' => $form -> createView()));
    }

    private function createCreateForm(User $entity) {
        $form = $this->createForm(new UserType(), $entity, array(
                'action' => $this->generateUrl('user_create'),
                'method' => 'POST'
            ));
        
        return $form;
    }

    public function createAction(Request $request) {
        $user = new User();
        $form = $this->createCreateForm($user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $password = $form->get('password')->getData();

            $encoder = $this->container->get('security.password_encoder');
            $encoded = $encoder->encodePassword($user, $password);

            $user->setPassword($encoded);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('UserBundle:User:add.html.twig', array('form' => $form -> createView()));
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

        return new Response('Usuario: ' . $user->getUsername() . ' con email: ' . $user->getEmail());
    }
}
