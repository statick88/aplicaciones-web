<?php
require_once __DIR__ . '/../models/Persona.php';

class PersonaController{

    private Persona $personaModel;

    public function __construct(private mysqli $conexion){
        $this->personaModel = new Persona($conexion);
    }

    public function guardarPersona($nombre):bool{
        return $this->personaModel->guardar($nombre);
    }

    public function listarPersonas():mysql_result|bool{
        return $this->personaModel->listar();
    }
}