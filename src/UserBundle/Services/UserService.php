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

        return $users;
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
        $query = $this->em->createQuery(
            'SELECT u.password
            FROM UserBundle:User u
            WHERE u.id = :id'
        )->setParameter('id', $id);

        $currentPass = $query->getResult();

        return $currentPass;
    }

    /**
     * Función que actualiza la contraseña de un usuario
     *
     * @param $user
     * @param $encodedPassword
     * @param $active
     * @return void
     */
    public function update($user, $encodedPassword, $active = null) {
        $user->setIsActive($active);
        $user->setPassword($encodedPassword);
        $this->em->flush();
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
        
        try {
            $this->em->remove($user);
            $this->em->flush();

            $status = true;
            $alert = 'The user has been removed.';

        } catch (\Throwable $th) {
            $status = false;
            $alert = 'The user could not be deleted.';
        }
        
        return array(
            "status"        => $status,
            "statusCode"    => 200,
            "message"       => $alert,
            "data"          => null
        );
    }
}