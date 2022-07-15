<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Services\GoogleCalendar;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;

class Agenda extends Model
{
    protected $table = 'agendas';
    // La tabla tiene un campo de texto llamado googleCalendarEventId
    // para guardar el evento de google que registro y poder gestionarlo

    // Arreglo de datos eniados para crear el evento en google calendar
    public function getDatosGoogleCalendarAttribute()
    {
        $datos = [
            'name' => 'Entrevista ' . $this->title,
            'description' => 'Organización - ' . $this->estado->nombre,
            'startDateTime' => Carbon::create($this->fecha . $this->start),
            'endDateTime' => Carbon::create($this->fecha . $this->end),
            'entrevistador' => $this->usuario ? $this->usuario->email : null,
            'nombre_entrevistador' => $this->usuario ? $this->usuario->name : null,
            'entrevistado' => $this->entrevistado ? $this->entrevistado->email : $this->entrevistado->mail,
            'nombre_entrevistado' => $this->entrevistado ? $this->entrevistado->nombre . ' ' . $this->entrevistado->apellido_paterno . ' ' . $this->entrevistado->apellido_materno : null,
        ];
        return $datos;
    }

    /*  */
    /* Funciones para manejo del calendario de Google */
    /*  */
    // Retorna evnto de Google Calendar si existiera
    public function verEventoCalendar()
    {
        $calendar = new GoogleCalendar();
        return $calendar->verEventoCalendar($this->googleCalendarEventId);  // Retornara falso si no existe evento
    }

    // Crea evento en Google calendar con los datos ya registrados de la agenda
    // Retorna el id del evento y lo registra en la agenda local
    public function crearEventoCalendar()
    {
        $datos = $this->datos_google_calendar;
        $calendar = new GoogleCalendar();
        $evento = $calendar->crearEventoCalendar($datos);
        if ($evento != false) {
            $this->googleCalendarEventId = $evento->id;
            $this->save();
            return true;
        }
        return false;
    }

    // Elimina evento de Google Calendar con id del evento
    public function eliminarEventoCalendar()
    {
        $calendar = new GoogleCalendar();
        $resultado = $calendar->eliminarEventoCalendar($this->googleCalendarEventId);
        if ($resultado) {
            $this->googleCalendarEventId = null;
            $this->save();
            return true;
        }
        // Retorna falso si no se elimina
        return false;
    }

    public function actualizarEventoCalendar()
    {
        $datos = $this->datos_google_calendar;
        $calendar = new GoogleCalendar();
        $resultado = $calendar->editarEventoCalendar($datos, $this->googleCalendarEventId);
        if ($resultado) {
            return true;
        }
        // retorna falso si no se logro actualizar
        return false;
    }
}
