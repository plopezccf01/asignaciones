<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UserBundle\Entity\Task;
use UserBundle\Form\TaskType;

class TaskController extends Controller
{
    protected $container;
    private $em;
    private $taskService;

    public function setContainer(ContainerInterface $container = null) {

        parent::setContainer($container);

        $this->em                       = $this->getDoctrine()->getManager();
        $this->taskService              = $this->get('task_service');
    }
    /**
     * Función que renderiza la vista de las tareas
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     *
     * @return Response
     */
    public function indexAction() {
        $tasks = $this->em->getRepository('UserBundle:Task')->findBy(array(), array('id' => 'DESC'));

        return $this->render('UserBundle:Task:index.html.twig', array('tasks' => $tasks));
    }

    /**
     * Función que renderiza la vista de las tareas
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     *
     * @return Response
     */
    public function customAction(Request $request) {
        $idUser = $this->get('security.token_storage')->getToken()->getUser()->getId();

        $dql = "SELECT t FROM UserBundle:Task t JOIN t.user u WHERE u.id = :idUser ORDER BY t.id DESC";
        $tasks = $this->em->createQuery($dql)->setParameter('idUser', $idUser)->getResult();

        $updateForm = $this->createCustomForm(':TASK_ID', 'PUT', 'task_process');

        return $this->render('UserBundle:Task:custom.html.twig', array('tasks' => $tasks, 'updateForm' => $updateForm->createView()));
    }

    /**
     * Función que procesa los datos que se han actualizado en la base de datos
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     *
     * @param $id
     * @param Request $request
     * @return void
     */
    public function processAction($id, Request $request) {
        $task = $this->em->getRepository('UserBundle:Task')->find($id);

        if (!$task) {
            throw $this->createNotFoundException('Task not found');
        }

        $form = $this->createCustomForm($task->getId(), 'PUT', 'task_process');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // if ($task->getStatus() == 0) {
            //     $processed = 1;
            // } else {
            //     $processed = 0;
            // }

            $processed = ($task->getStatus() == 0) ? 1 : 0 ;

            $result = $this->taskService->updateStatus($task, 1);

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(
                    $result,
                    $result['statusCode'],
                    array('Content-type' => 'application/json')
                );
            }
            
        }
    }

    /**
     * Función que renderiza al formulario para crear una nueva tarea
     *
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     * 
     * @return Response
     */
    public function addAction() {
        $task = new Task();
        $form = $this->createCreateForm($task);

        return $this->render('UserBundle:Task:add.html.twig', array('form' => $form->createView()));
    }

    /**
     * Función que crea el formulario de creación de una nueva tarea
     *
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     * 
     * @param Task $entity
     * @return $form
     */
    private function createCreateForm(Task $entity) {
        $form = $this->createForm(new TaskType(), $entity, array(
            'action' => $this->generateUrl('task_create'),
            'method' => 'POST'
        ));

        return $form;
    }

    /**
     * Función que procesa lo que se envia dentro del formulario de creación de una nueva tarea
     *
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function createAction(Request $request) {
        $task = new Task();
        $form = $this->createCreateForm($task);
        $form->handleRequest($request);

        if(!$form->isSubmitted() || !$form->isValid()){
            return $this->render('UserBundle:Task:add.html.twig', array('form' => $form->createView()));
        }
        
        try {
            $this->taskService->updateStatus($task, 0, true);
        } catch (\Throwable $th) {
            return $this->redirectToRoute('task_index');
        }

        return $this->redirectToRoute('task_index');
    }

    /**
     * Función que renderiza la vista de los datos de una tarea
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     *
     * @param $id
     * @return Response
     */
    public function viewAction($id) {
        $task = $this->em->getRepository('UserBundle:Task')->find($id);

        if (!$task) {
            throw $this->createNotFoundException('The task does not exist.');
        }

        $deleteForm = $this->createCustomForm($task->getId(), 'DELETE', 'task_delete');

        $user = $task->getUser();

        return $this->render('UserBundle:Task:view.html.twig', array('task' => $task, 'user' => $user, 'delete_form' => $deleteForm->createView()));
    }

    /**
     * Función que renderiza la vista de editar la tarea
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     *
     * @param $id
     * @return Response
     */
    public function editAction($id) {
        $task = $this->em->getRepository('UserBundle:Task')->find($id);

        try {
            $form = $this->createEditForm($task);
        } catch (\Throwable $th) {
            throw $this->createNotFoundException('The task does not found.');
        }

        return $this->render('UserBundle:Task:edit.html.twig', array('task' => $task, 'form' => $form->createView()));
    }

    /**
     * Función que crea el formulario de editar la tarea
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     *
     * @param Task $entity
     * @return $form
     */
    private function createEditForm(Task $entity) {
        $form = $this->createForm(new TaskType(), $entity,
            array('action' => $this->generateUrl('task_update', array('id' => $entity->getId())), 'method' => 'PUT')
        );

        return $form;
    }

    /**
     * Función que procesa y edita la tarea
     *
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     * 
     * @param $id
     * @param Request $request
     * @return RedirectResponse | Response
     */
    public function updateAction($id, Request $request) {
        $task = $this->em->getRepository('UserBundle:Task')->find($id);

        if (!$task) {
            throw $this->createNotFoundException('The task does not found.');
        }

        $form = $this->createEditForm($task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskService->updateStatus($task, 0);

            return $this->redirectToRoute('task_edit', array('id' => $task->getId()));
        }

        return $this->render('UserBundle:Task:edit.html.twig', array('task' => $task, 'form' => $form->createView()));
    }
    
    /**
     * Función que elimina una tarea
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     * 
     * @param Request $request, $id
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $id) {
        $task = $this->em->getRepository('UserBundle:Task')->find($id);

        if (!$task) {
            throw $this->createNotFoundException('The task does not found.');
        }

        $form = $this->createCustomForm($task->getId(),'DELETE', 'user_delete');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $this->em->remove($task);
            // $this->em->flush();
            $result = $this->taskService->removeTask($task);

            if ($result["status"]) {
                return $this->redirectToRoute('task_index');
            } else {
                return new JsonResponse($result["message"]);
            }

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
            ->setAction($this->generateUrl($route, array('id' => $id))) //Crea la acción que va a procesar el formulario
            ->setMethod($method) // Define el método que va a procesar el formulario
            ->getForm(); //Procesa el formulario
    }
}
