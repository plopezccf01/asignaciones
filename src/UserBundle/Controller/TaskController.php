<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UserBundle\Entity\Task;
use UserBundle\Form\TaskType;

class TaskController extends Controller
{
    public function indexAction() {
        exit('Lista de tareas');
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

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setStatus(0);
            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();

            return $this->redirectToRoute('task_index');
        }

        return $this->render('UserBundle:Task:add.html.twig', array('form' => $form->createView()));
    }
}
