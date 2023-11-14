<?php

namespace UserBundle\Services;

class UserService
{
    private $em;
    private $userRepository;

    public function __construct($entityManager) {
        $this->em               = $entityManager;
        $this->userRepository   = $this->em->getRepository('UserBundle:User');
    }

    /**
     * Función que te imprime todos los usuarios de la base de datos
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     *
     * @return $users
     */
    public function getUsers() {
        $users = $this->userRepository->findAll();

        return array(
            "status"        => true,
            "statusCode"    => 200,
            "message"       => '',
            "data"          => $users
        );
    }

    /**
     * Función que recupera el password del usuario
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     *
     * @param $id
     * @return $currentPass
     */
    public function getCurrentPass($id) {
        $result = $this->userRepository->getCurrentPass($id);

        return array(
            "status"        => true,
            "statusCode"    => 200,
            "message"       => '',
            "data"          => $result
        );
    }

    /**
     * Función que actualiza la contraseña de un usuario
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     *
     * @param $user
     * @param $encodedPassword
     * @param $active
     * @return void
     */
    public function update($user, $encodedPassword, $active = null) {
        $result = $this->userRepository->update($user, $encodedPassword, $active);
        if (!$result) {
            return array(
                'status'        => false, 
                'statusCode'    => 400,
                'message'       => 'KO',
                'data'          => null
            );
        }

        return array(
            'status'        => true, 
            'statusCode'    => 200,
            'message'       => 'OK',
            'data'          => null
        );
    }

    /**
     * Función que elimina a un usuario tipo user y no a uno de tipo admin
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     *
     * @param $user
     * @return array
     */
    public function remove($user) {
        if ($user->getRole() == 'ROLE_ADMIN') {
            return array(
                "status"        => false,
                "statusCode"    => 400,
                "message"       => 'The user could not be deleted.',
                "data"          => null
            );
        }

        $result = $this->userRepository->remove($user);
        
        if (!$result) {
            return array(
                "status"        => false,
                "statusCode"    => 400,
                "message"       => 'The user could not be deleted.',
                "data"          => null
            );
        }
        
        return array(
            "status"        => true,
            "statusCode"    => 200,
            "message"       => "The user has been removed",
            "data"          => null
        );
    }
}