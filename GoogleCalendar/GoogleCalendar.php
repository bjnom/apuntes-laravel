<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Google\Service\Calendar\EventDateTime;

class GoogleCalendar
{

    protected $client;
    protected $calendarId;
    protected $service;

    function __construct()
    {
        /* Get config variables */
        $this->calendarId = ENV('GOOGLE_CALENDAR_ID');
        $key_file_location = Config::get('google.key_file_location');

        $this->client = new \Google_Client();
        $this->client->setApplicationName("Nombre Aplicacion");

        /* If we have an access token */
        if (Cache::has('service_token')) {
            $this->client->setAccessToken(Cache::get('service_token'));
        }

        /* Add the scopes you need */
        $scopes = array('https://www.googleapis.com/auth/calendar');
        $user_to_impersonate = ENV('GOOGLE_CALENDAR_IMPERSONATE');

        $this->client->setScopes($scopes);
        $this->client->setSubject($user_to_impersonate);
        $this->client->setAuthConfig($key_file_location);

        $this->service = new \Google_Service_Calendar($this->client);
        Cache::forever('service_token', $this->client->getAccessToken());
    }


    public function verEventoCalendar($eventId)
    {
        $event = $this->service->events->get($this->calendarId, $eventId);
        if($event){
            return $event;
        }
        return false;
    }

    public function crearEventoCalendar(array $datos)
    {
        try {
            $calendarId = $this->calendarId;
            $calendarService = new \Google_Service_Calendar($this->client);

            $event = new \Google_Service_Calendar_Event(array(
                'summary' => $datos['name'],
                'location' => 'Meet',
                'description' => $datos['description'],
                'visibility' => 'default',
                'start' => array(
                    'dateTime' => $datos['startDateTime'],
                    'timeZone' => 'America/Santiago',
                ),
                'end' => array(
                    'dateTime' => $datos['endDateTime'],
                    'timeZone' => 'America/Santiago',
                ),
                "conferenceData" => [
                    "createRequest" => [
                        "conferenceId" => [
                            "type" => "eventNamedHangout"
                        ],
                        "requestId" => "123"
                    ]
                ],
                'attendees' => array(
                    [
                        'email' => $datos['entrevistador'],
                        'name' => $datos['nombre_entrevistador'],
                        'comment' => 'Entrevistador',
                    ],
                    [
                        'email' => $datos['entrevistado'],
                        'name' => $datos['nombre_entrevistado'],
                        'comment' => 'Entrevistado',
                    ]
                ),
                'reminders' => array(
                    'useDefault' => FALSE,
                    'overrides' => array(
                        array('method' => 'popup', 'minutes' => 10),
                    ),
                ),
            ));

            $event = $calendarService->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);
            return $event;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function eliminarEventoCalendar($eventId)
    {
        try {
            $calendarService = new \Google_Service_Calendar($this->client);
            $calendarService->events->delete($this->calendarId, $eventId);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function editarEventoCalendar(array $datos, string $eventId)
    {
        try {
            $calendarService = new \Google_Service_Calendar($this->client);
            // Se recuperan los datos del evento a modificar
            $nuevoEvento = $calendarService->events->get($this->calendarId, $eventId);

            // Modificar el evento requiere la clase EventDateTime de la Api 
            $start = new EventDateTime();
            $end = new EventDateTime();

            // Esta clase recive necesariamente un string datetime en formato RFC3339
            $inicio = $datos['startDateTime']->format(\DateTime::RFC3339);
            $termino = $datos['endDateTime']->format(\DateTime::RFC3339);

            // Ademas se le asigna la zona horaria local
            $start->setDateTime($inicio);
            $start->setTimeZone('America/Santiago');
            $end->setDateTime($termino);
            $end->setTimeZone('America/Santiago');

            // se asignan los nuevos datos a el evento que sobre escribe al anterior
            $nuevoEvento->setSummary($datos['name']);
            $nuevoEvento->setDescription($datos['description']);
            $nuevoEvento->setStart($start);
            $nuevoEvento->setEnd($end);

            // Guardar datos
            $calendarService->events->update($this->calendarId, $this->eventId, $nuevoEvento);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
