<?php

declare(strict_types=1);

namespace App\controllers;

use App\Models\CitasSalonModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CitasSalon
{

    private ContainerInterface $container;

    public function __construct(ContainerInterface $c)
    {
        $this->container = $c;
    }

    public function read(Request $request, Response $response, array $args): Response
    {
        $this->container->get('eloquent');

        if (isset($args['id'])) {
            $cita = CitasSalonModel::query()
                ->select(['id', 'nombre_cliente', 'telefono_cliente', 'servicio_solicitado', 'estilista_asignado', 'fecha_hora_cita', 'precio_servicio', 'estado', 'created_at'])
                ->find($args['id']);

            if ($cita === null) {
                return $this->json($response, ['error' => 'Cita no encontrada.'], 404);
            }

            return $this->json($response, ['data' => $cita], 200);
        }

        $citas = CitasSalonModel::query()
            ->select(['id', 'nombre_cliente', 'telefono_cliente', 'servicio_solicitado', 'estilista_asignado', 'fecha_hora_cita', 'precio_servicio', 'estado', 'created_at'])
            ->get()
            ->toArray();

        return $this->json($response, ['data' => $citas], 200);
    }


    public function create(Request $request, Response $response, array $args): Response
    {
        $this->container->get('eloquent');
        $data = $request->getParsedBody();
        $data = is_array($data) ? $data : [];

        $errors = [];
        $clientName = trim((string) ($data['nombre_cliente'] ?? ''));
        $clientPhone = trim((string) ($data['telefono_cliente'] ?? ''));
        $service = trim((string) ($data['servicio_solicitado'] ?? ''));
        $stylist = trim((string) ($data['estilista_asignado'] ?? ''));
        $dateTime = trim((string) ($data['fecha_hora_cita'] ?? ''));
        $price = filter_var($data['precio_servicio'] ?? null, FILTER_VALIDATE_FLOAT);
        $status = trim((string) ($data['estado'] ?? 'Pendiente'));

        if ($clientName === '' || mb_strlen($clientName) > 100) {
            $errors['nombre_cliente'] = 'Es obligatorio y debe tener máximo 100 caracteres.';
        }
        if ($clientPhone === '' || mb_strlen($clientPhone) > 20) {
            $errors['telefono_cliente'] = 'Es obligatorio y debe tener máximo 20 caracteres.';
        }
        if ($service === '' || mb_strlen($service) > 80) {
            $errors['servicio_solicitado'] = 'Es obligatorio y debe tener máximo 80 caracteres.';
        }
        if ($stylist === '' || mb_strlen($stylist) > 100) {
            $errors['estilista_asignado'] = 'Es obligatorio y debe tener máximo 100 caracteres.';
        }
        $parsedDateTime = \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $dateTime);
        $dateErrors = \DateTimeImmutable::getLastErrors();
        if (
            $parsedDateTime === false
            || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
            || $parsedDateTime->format('Y-m-d H:i:s') !== $dateTime
        ) {
            $errors['fecha_hora_cita'] = 'Debe tener el formato Y-m-d H:i:s.';
        }
        if ($price === false || $price <= 0) {
            $errors['precio_servicio'] = 'Debe ser un número mayor a 0.';
        }
        if ($status !== 'Pendiente' && $status !== 'Completada' && $status !== 'Cancelada') {
            $errors['estado'] = 'Debe ser Pendiente, Completada o Cancelada.';
        }

        if ($errors !== []) {
            return $this->json($response, ['errors' => $errors], 422);
        }

        if ($status === 'Pendiente') {
            $citasCount = CitasSalonModel::query()
                ->where('estilista_asignado', $stylist)
                ->where('fecha_hora_cita', $dateTime)
                ->where('estado', 'Pendiente')
                ->count();

            if ($citasCount > 0) {
                return $this->json($response, ['error' => 'El estilista ya tiene una cita en ese horario.'], 409);
            }
        }

        $cita = CitasSalonModel::create([
            'nombre_cliente' => $clientName,
            'telefono_cliente' => $clientPhone,
            'servicio_solicitado' => $service,
            'estilista_asignado' => $stylist,
            'fecha_hora_cita' => $dateTime,
            'precio_servicio' => $price,
            'estado' => $status,
        ]);

        return $this->json($response, ['data' => $cita->toArray()], 201);
    }


    public function update(Request $request, Response $response, array $args): Response
    {
        $this->container->get('eloquent');
        if (isset($args['id'])) {
            $cita = CitasSalonModel::find($args['id']);

            if ($cita === null) {
                return $this->json($response, ['error' => 'Cita no encontrada.'], 404);
            }

            $data = $request->getParsedBody();
            $data = is_array($data) ? $data : [];

            if (isset($data['nombre_cliente'])) {
                $clientName = trim((string) $data['nombre_cliente']);
                if ($clientName === '' || mb_strlen($clientName) > 100) {
                    return $this->json($response, ['error' => 'Nombre del cliente inválido.'], 422);
                }
                $cita->nombre_cliente = $clientName;
            }

            if (isset($data['telefono_cliente'])) {
                $clientPhone = trim((string) $data['telefono_cliente']);
                if ($clientPhone === '' || mb_strlen($clientPhone) > 20) {
                    return $this->json($response, ['error' => 'Teléfono inválido.'], 422);
                }
                $cita->telefono_cliente = $clientPhone;
            }

            if (isset($data['servicio_solicitado'])) {
                $service = trim((string) $data['servicio_solicitado']);
                if ($service === '' || mb_strlen($service) > 80) {
                    return $this->json($response, ['error' => 'Servicio inválido.'], 422);
                }
                $cita->servicio_solicitado = $service;
            }

            if (isset($data['estilista_asignado'])) {
                $stylist = trim((string) $data['estilista_asignado']);
                if ($stylist === '' || mb_strlen($stylist) > 100) {
                    return $this->json($response, ['error' => 'Estilista inválido.'], 422);
                }
                $cita->estilista_asignado = $stylist;
            }

            if (isset($data['fecha_hora_cita'])) {
                $dateTime = trim((string) $data['fecha_hora_cita']);
                $parsedDateTime = \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $dateTime);
                $dateErrors = \DateTimeImmutable::getLastErrors();
                if (
                    $parsedDateTime === false
                    || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
                    || $parsedDateTime->format('Y-m-d H:i:s') !== $dateTime
                ) {
                    return $this->json($response, ['error' => 'La fecha debe tener el formato Y-m-d H:i:s.'], 422);
                }
                $cita->fecha_hora_cita = $dateTime;
            }

            if (isset($data['precio_servicio'])) {
                $price = filter_var($data['precio_servicio'], FILTER_VALIDATE_FLOAT);
                if ($price === false || $price <= 0) {
                    return $this->json($response, ['error' => 'Precio inválido.'], 422);
                }
                $cita->precio_servicio = $price;
            }

            if (isset($data['estado'])) {
                $status = trim((string) $data['estado']);
                if ($status !== 'Pendiente' && $status !== 'Completada' && $status !== 'Cancelada') {
                    return $this->json($response, ['error' => 'Estado inválido.'], 422);
                }
                $cita->estado = $status;
            }

            if ($cita->estado === 'Pendiente') {
                $citasCount = CitasSalonModel::query()
                    ->where('id', '<>', $cita->id)
                    ->where('estilista_asignado', $cita->estilista_asignado)
                    ->where('fecha_hora_cita', $cita->fecha_hora_cita)
                    ->where('estado', 'Pendiente')
                    ->count();

                if ($citasCount > 0) {
                    return $this->json($response, ['error' => 'El estilista ya tiene una cita en ese horario.'], 409);
                }
            }

            $cita->save();

            return $this->json($response, ['data' => $cita->toArray()], 200);
        }

        return $this->json($response, ['error' => 'ID no proporcionado.'], 400);
    }


    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->container->get('eloquent');
        if (isset($args['id'])) {
            $cita = CitasSalonModel::find($args['id']);

            if ($cita === null) {
                return $this->json($response, ['error' => 'Cita no encontrada.'], 404);
            }

            $cita->delete();

            return $response->withStatus(204);
        }

        return $this->json($response, ['error' => 'ID no proporcionado.'], 400);
    }


    private function json(Response $response, array $payload, int $status): Response
    {
        $response->getBody()->write(json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        ));

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($status);
    }

}
