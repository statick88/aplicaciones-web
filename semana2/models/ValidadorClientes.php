<?php

declare(strict_types=1);

/**
 * Validación de los datos del formulario antes de tocar el repositorio.
 * Sin variables de variables: arreglos y parámetros con tipo (teoría, sección 04).
 */
class ValidadorClientes
{
    /**
     * @param string $nombre nombre tal como llegó del formulario
     * @param string $correo correo tal como llegó del formulario
     * @return list<string> mensajes de error; vacío cuando los datos son válidos
     */
    public function validar(string $nombre, string $correo): array
    {
        $errores = [];

        if (trim($nombre) === '') {
            $errores[] = 'El nombre es obligatorio.';
        }

        if (filter_var(trim($correo), FILTER_VALIDATE_EMAIL) === false) {
            $errores[] = 'El correo electrónico no es válido.';
        }

        return $errores;
    }
}
