<?php

namespace UserBundle\Services;

use UserBundle\Entity\Contact;

class ContactService {
    private $em;

    public function __construct($entityManager) {
        $this->em               = $entityManager;
    }

    /**
     * Función que valida los datos del formulario de contacto
     * 
     * @author Pablo López <pablo.lopez@eurotransportcar.com>
     *
     * @param array $data()
     * @return $result
     */
    public function validateContactData($data) {
        $result = array(
            "status"    =>  false,
            "statusCode"      =>  400,
            "message"   =>  'Error al realizar la operación',
            "data"      =>  array(
                "errors"    =>  array(),
                "success"   =>  array()
            )
        );

        if (!isset($data['firstName']) || empty($data['firstName'])) {
            array_push($result['data']['errors'], 'firstName');
        } else{
            array_push($result['data']['success'], 'firstName');
        }

        if (!isset($data['lastName']) || empty($data['lastName'])) {
            array_push($result['data']['errors'], 'lastName');
        } else{
            array_push($result['data']['success'], 'lastName');
        }

        if (!isset($data['email']) || empty($data['email']) || !preg_match('/\S+@\S+\.\S+/', $data['email'])) {
            array_push($result['data']['errors'], 'email');
        } else{
            array_push($result['data']['success'], 'email');
        }

        if (!empty($data['phone'])) {
            if (preg_match('/^[0-9-()+]{3,20}/', $data['phone'])) {
                array_push($result['data']['success'], 'phone');
            } else{
                array_push($result['data']['errors'], 'phone');
            }
        }

        if (!isset($data['affair']) || empty($data['affair'])) {
            array_push($result['data']['errors'], 'affair');
        } else{
            array_push($result['data']['success'], 'affair');
        }

        if (!isset($data['description']) || empty($data['description'])) {
            array_push($result['data']['errors'], 'description');
        } else{
            array_push($result['data']['success'], 'description');
        }

        if (empty($result['data']['errors'])) {
            $result['status'] = true;
            $result['statusCode'] = 200;
            $result['message'] = "Operación realizada correctamente";
        } else {
            if (count($result['data']['errors']) > 1) {
                $result['message'] = "Fallan los campos: ";
            } else{
                $result['message'] = "Falla el campo: ";
            }

            foreach($result['data']['errors'] as $errors) {
                $result['message'] .= $errors . ' ';
            }
        }

        return $result;
    }

    public function newContact($data) {
        
        $contact = new Contact($data['firstName'], $data['lastName'], $data['email'], $data['affair'], $data['description'], $data['phone']);
        try {
            $this->em->persist($contact);
            $this->em->flush();

            return array(
                "status"            =>  true,
                "statusCode"        =>  200,
                "message"           =>  'Se ha realizado correctamente',
                "data"              =>  null
            );
            
        } catch (\Throwable $th) {
            return array(
                "status"            =>  false,
                "statusCode"        =>  400,
                "message"           =>  $th->getMessage(),
                "data"              =>  null
            );
        }
    }
}