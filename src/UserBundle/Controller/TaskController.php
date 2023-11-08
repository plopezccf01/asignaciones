<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UserBundle\Entity\Task;
use UserBundle\Form\TaskType;

class TaskController extends Controller
{
    /**
     * Función que renderiza la vista de las tareas
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     *
     * @return Response
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $dql = "SELECT t FROM UserBundle:Task t ORDER BY t.id DESC";
        $tasks = $em->createQuery($dql)->getResult();

        return $this->render('UserBundle:Task:index.html.twig', array('tasks' => $tasks));
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
     * @return Response
     */
    public function createAction(Request $request) {
        $task = new Task();
        $form = $this->createCreateForm($task);
        $form->handleRequest($request);

        if(!$form->isSubmitted() || !$form->isValid()){
            return $this->render('UserBundle:Task:add.html.twig', array('form' => $form->createView()));
        }

        $task->setStatus(0);
        $em = $this->getDoctrine()->getManager();
        
        try {
            $em->persist($task);
            $em->flush();
        } catch (\Throwable $th) {
            return $this->redirectToRoute('task_index');
        }

        return $this->redirectToRoute('task_index');
        
    }
}
