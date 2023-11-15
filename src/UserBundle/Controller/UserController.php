<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use UserBundle\Entity\User;
use UserBundle\Form\UserType;

class UserController extends Controller
{
    protected $container;
    private $em;
    private $userService;

    public function setContainer(ContainerInterface $container = null) {

        parent::setContainer($container);

        $this->em                       = $this->getDoctrine()->getManager();
        $this->userService              = $this->get('user_service');
    }

    /**
     * Función que renderiza a la home page
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
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
     * 
     * @return Response
     */
    public function indexAction()
    {
        $result = $this->userService->getUsers();

        $deleteFormAjax = $this->createCustomForm(':USER_ID', 'DELETE', 'user_delete');

        return $this->render('UserBundle:User:index.html.twig', array('users' => $result["data"], 'delete_form_ajax' => $deleteFormAjax->createView()));
    }

    /**
     * Función que renderiza la vista del formulario de nuevos usuarios
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     * 
     * @return Response
     */
    public function addAction() {
        $user = new User();
        $form = $this->createCreateForm($user);

        return $this->render('UserBundle:User:add.html.twig', array('form' => $form -> createView()));
    }

    /**
     * Función que crea el formulario de creación de un nuevo usuario
     *
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     * 
     * @param User $entity
     * @return $form
     */
    private function createCreateForm(User $entity) {
        $form = $this->createForm(new UserType(), $entity, array(
                'action' => $this->generateUrl('user_create'),
                'method' => 'POST'
            ));
        
        return $form;
    }

    /**
     * Función que procesa lo que se envia dentro del formulario de creación de un nuevo usuario
     *
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     * 
     * @param Request $request
     * @return Response
     */
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
    
                $this->userService->update($user, $encoded, null, true);
    
                return $this->redirectToRoute('user_index');
            }else {
                $errorMessage = new FormError($errorList[0]->getMessage());
                $form->get('password')->addError($errorMessage);
            }
        }

        return $this->render('UserBundle:User:add.html.twig', array('form' => $form -> createView()));
    }

    /**
     * Función que renderiza la vista de editar un usuario
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     *
     * @param $id
     * @return Response
     */
    public function editAction($id) {
        $user = $this->em->getRepository('UserBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $form = $this->createEditForm($user);

        return $this->render('UserBundle:User:edit.html.twig', array('user' => $user, 'form' => $form->createView()));
    }

    /**
     * Función que crea el formulario de editar un usuario
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     *
     * @param User $entity
     * @return Form $form
     */
    private function createEditForm(User $entity) {
        $form = $this->createForm(new UserType(), $entity, 
            array('action' => $this->generateUrl('user_update', array('id' => $entity->getId())), 'method' => 'PUT'));

        return $form;
    }

    /**
     * Función que procesa y edita un usuario
     *
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     * 
     * @param $id
     * @param Request $request
     * @return RedirectResponse | Response
     */
    public function updateAction($id, Request $request) {
        $user = $this->em->getRepository('UserBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $form = $this->createEditForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password =$form->get('password')->getData();

            if ($form->get('role')->getData() == 'ROLE_ADMIN') {
                $active = 1;
            }

            if (!empty($password)) {
                $encoder = $this->container->get('security.password_encoder');
                $encoded = $encoder->encodePassword($user, $password);

                
            } else {
                $result = $this->userService->getCurrentPass();
                $encoded = $result["data"][0]['password'];
            }

            $this->userService->update($user, $encoded, $active);

            return $this->redirectToRoute('user_edit', array('id' => $user->getId()));
        }

        return $this->render('UserBundle:User:edit.html.twig', array('user' => $user, 'form' => $form->createView()));
    }

    /**
     * Función que renderiza nuestra vista de los datos de un usuario
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     * 
     * @param $id
     * @return Response
     */
    public function viewAction($id) {
        $repository = $this->em->getRepository('UserBundle:User');

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
     * 
     * @param Request $request, $id
     * @return void | RedirectResponse
     */
    public function deleteAction(Request $request, $id) {
        $user = $this->em->getRepository('UserBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // $form = $this->createDeleteForm($user);
        $form = $this->createCustomForm($user->getId(),'DELETE', 'user_delete');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->isXmlHttpRequest()) {
                $result = $this->userService->remove($user);

                return new JsonResponse($result, $result['statusCode'], array('Content-Type' => 'application/json'));
            }

            return $this->redirectToRoute('user_index');
        }
    }

    /**
     * Función que crea un formulario
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     *
     * @param $id
     * @param $method
     * @param $route
     * @return FormBuilder
     */
    private function createCustomForm($id, $method, $route) {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl($route, array('id' => $id)))
            ->setMethod($method)
            ->getForm();
    }
}