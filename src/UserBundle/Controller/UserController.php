<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;
use UserBundle\Entity\User;
use UserBundle\Form\UserType;

class UserController extends Controller
{

    /**
     * Función que renderiza a la home page
     *
     * @return Response
     */
    public function homeAction() {
        return $this->render('UserBundle:User:home.html.twig');
    }

    /**
     * Función que renderiza la vista de los usuarios
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     * @return Response
     */

    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('UserBundle:User')->findAll();

        $deleteFormAjax = $this->createCustomForm(':USER_ID', 'DELETE', 'user_delete');

        return $this->render('UserBundle:User:index.html.twig', array('users' => $users, 'delete_form_ajax' => $deleteFormAjax->createView()));
    }

    /**
     * Función que renderiza la vista del formulario de nuevos usuarios
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     * @return Response
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

            $passwordConstraint = new Assert\NotBlank();
            $errorList = $this->get('validator')->validate($password, $passwordConstraint);

            if (count($errorList) == 0) {
                $encoder = $this->container->get('security.password_encoder');
                $encoded = $encoder->encodePassword($user, $password);
    
                $user->setPassword($encoded);
    
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
    
                return $this->redirectToRoute('user_index');
            }else {
                $errorMessage = new FormError($errorList[0]->getMessage());
                $form->get('password')->addError($errorMessage);
            }
        }

        return $this->render('UserBundle:User:add.html.twig', array('form' => $form -> createView()));
    }

    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('UserBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $form = $this->createEditForm($user);

        return $this->render('UserBundle:User:edit.html.twig', array('user' => $user, 'form' => $form->createView()));
    }

    private function createEditForm(User $entity) {
        $form = $this->createForm(new UserType(), $entity, 
            array('action' => $this->generateUrl('user_update', array('id' => $entity->getId())), 'method' => 'PUT'));

        return $form;
    }

    
    public function updateAction($id, Request $request) {

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('UserBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $form = $this->createEditForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password =$form->get('password')->getData();
            if (!empty($password)) {
                $encoder = $this->container->get('security.password_encoder');
                $encoded = $encoder->encodePassword($user, $password);
                $user->setPassword($encoded);
            } else {
                $recoverPass = $this->recoverPass($id);
                $user->setPassword($recoverPass[0]['password']);
            }

            if ($form->get('role')->getData() == 'ROLE_ADMIN') {
                $user->setIsActive(1);
            }

            $em->flush();

            return $this->redirectToRoute('user_edit', array('id' => $user->getId()));
        }

        return $this->render('UserBundle:User:edit.html.twig', array('user' => $user, 'form' => $form->createView()));
    }

    /**
     * Función que recupera el password del usuario
     *
     * @param $id
     * @return $currentPass
     */
    private function recoverPass($id) {
       
        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT u.password
            FROM UserBundle:User u
            WHERE u.id = :id'
        )->setParameter('id', $id);

        $currentPass = $query->getResult();

        return $currentPass;
    }

    /**
     * Función que renderiza nuestra vista de los datos de un usuario
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     * @return Response
     */

    public function viewAction($id) {
        $repository = $this->getDoctrine()->getRepository('UserBundle:User');

        $user = $repository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $deleteForm = $this->createCustomForm($user->getId(), 'DELETE', 'user_delete');

        return $this->render('UserBundle:User:view.html.twig', array('user' => $user, 'delete_form' => $deleteForm -> createView()));
    }

    /**
     * Función que elimina a un usuario
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     * @param Request $request, $id
     * @return void
     */

    public function deleteAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('UserBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // $form = $this->createDeleteForm($user);
        $form = $this->createCustomForm($user->getId(),'DELETE', 'user_delete');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->isXmlHttpRequest()) {
                $res = $this->deleteUser($user -> getRole(), $em, $user);

                return new Response(
                    json_encode(array('removed' => $res['removed'])), 200, array('Content-Type' => 'application/json')
                );
            }

            $res = $this->deleteUser($user -> getRole(), $em, $user);

            return $this->redirectToRoute('user_index');
        }
    }

    private function deleteUser($role, $em, $user) {
        if ($role == 'ROLE_USER') {
            $em->remove($user);
            $em->flush();

            $removed = 1;
            $alert = 'mensaje';
        }

        elseif ($role == 'ROLE_ADMIN') {
            echo "The user could not be deleted.";
            $removed = 0;
            $alert = 'error';
        }

        return array('removed'=> $removed,'alert'=>$alert);
    }

    private function createCustomForm($id, $method, $route) {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl($route, array('id' => $id)))
            ->setMethod($method)
            ->getForm();
    }
}