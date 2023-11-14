<?php

namespace UserBundle\Services;

class TaskService {
    private $em;
    private $taskRepository;

    public function __construct($entityManager) {
        $this->em               = $entityManager;
        $this->taskRepository   = $this->em->getRepository('UserBundle:Task');
    }

    /**
     * Función que te devuelve todas las tareas
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     *
     * @return $tasks
     */
    public function getTasks() {
        
        $tasks = $this->taskRepository->getTasks();
        
        return $tasks;
    }

    /**
     * Función que te actualiza el estado de la tarea
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     *
     * @param $task
     * @param $status
     * @param $needPersist
     * @return array
     */
    public function updateStatus($task, $status = null, $needPersist = false) {
        $result = $this->taskRepository->updateStatus($task, $status, $needPersist);

        if (!$result) {
            return array("status" => false, "statusCode" => 400, "message" => "KO");
        }

        return array("status" => true, "statusCode" => 200, "message" => "Ok");
    }

    /**
     * Función que te elimina una tarea
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     *
     * @param $task
     * @return array
     */
    public function removeTask($task) {
        $result = $this->taskRepository->removeTask($task);

        if (!$result) {
            return array("status" => false, "statusCode" => 400, "message" => "The task could not be deleted.");
        }

        return array("status" => true, "statusCode" => 200, "message" => "The task has been removed.");
    }
}