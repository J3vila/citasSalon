<?php

declare(strict_types=1);

namespace App\Validators;

use Respect\Validation\ValidatorBuilder as v;

final class CitasSalonValidator
{
    private function reglas(): array
    {
        return [

            'nombre_cliente' => [
                'regla' => v::stringType()
                    ->notBlank()
                    ->length(v::between(5, 100))
                    ->regex('/^[A-Za-zÑñÁÉÍÓÚáéíóú]{2,}(?: [A-Za-zÑñÁÉÍÓÚáéíóú]{2,}){1,3}$/u'),
                'mensaje' => 'El nombre del cliente es obligatorio y debe contener entre 5 y 100 caracteres, solo letras y espacios (de 2 a 4 palabras)',
            ],

            'telefono_cliente' => [
                'regla' => v::stringType()
                    ->regex('/^[2-8][0-9]{3}-[0-9]{4}$/')
                    ->length(v::equals(9)),
                'mensaje' => 'El telefono es obligatorio, debe tener el formato 0000-0000 y comenzar con un digito entre 2 y 8',
            ],

            'servicio_solicitado' => [
                'regla' => v::stringType()
                    ->notBlank()
                    ->length(v::between(3, 80)),
                'mensaje' => 'El servicio solicitado es obligatorio y debe tener entre 3 y 80 caracteres',
            ],

            'estilista_asignado' => [
                'regla' => v::stringType()
                    ->notBlank()
                    ->length(v::between(2, 100))
                    ->regex('/^[A-Za-zÑñÁÉÍÓÚáéíóú]{2,}(?: [A-Za-zÑñÁÉÍÓÚáéíóú]{2,}){0,3}$/u'),
                'mensaje' => 'El estilista asignado es obligatorio y debe contener entre 2 y 100 caracteres, solo letras y espacios',
            ],

            'fecha_hora_cita' => [
                'regla' => v::stringType()
                    ->notBlank()
                    ->regex('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'),
                'mensaje' => 'La fecha y hora de la cita es obligatoria y debe tener el formato YYYY-MM-DD HH:MM:SS',
            ],

            'precio_servicio' => [
                'regla' => v::intType()->positive(),
                'mensaje' => 'El precio del servicio es obligatorio y debe ser un número entero positivo',
            ],

            'estado' => [
                'regla' => v::stringType()
                    ->regex('/^(Pendiente|Completada|Cancelada)$/'),
                'mensaje' => 'El estado debe ser Pendiente, Completada o Cancelada',
            ],

        ];
    }

    public function validate(array $data): array
    {
        $errores = [];

        foreach ($this->reglas() as $campo => $config) {

            $valor = $data[$campo] ?? null;
            $regla = $config['regla'];
            $mensaje = $config['mensaje'];

            if ($campo === 'estado' && ($valor === null || $valor === "")) {
                continue;
            }

            if ($regla->validate($valor)->hasFailed()) {
                $errores[$campo] = $mensaje;
            }
        }

        return $errores;
    }
}
