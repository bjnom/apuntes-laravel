# Habilitar Domain-Wide Delegation en cuenta de servicio

Para creacion de eventos en un calendario compartido con una cuenta que no usa pantalla de concentimiento.
> Para oauth con cuentas de usuario usar ID de clientes OAuth 2.0

##### 1 - Crear proyecto y credenciales
1. Iniciar sesion con cuenta de administrador de dominio en GCP
2. Buscar *Calendar* en la opcion del sidebar izquierdo __Biblioteca__
3. Seleccionar __Google Calendar__ y hacer clic al boton **__Habilitar__**
4. Ingresa a la opcion de __Credenciales__ en el sidebar izquierdo
5. Selecciona __Crear credencial__ en el navbar superior
6. Selecciona __Cuenta de servicio__
7. Agregar un nombre y opcionalmente completar el resto de los campos
8. Una vez creada seleccionala en el apartado de __Cuentas de servicio__
9. Descargar credencial en formato JSON que tendra esta estructura
```json
{
  "type": "service_account",
  "project_id": "nombre-12345",
  "private_key_id": "unhashlargo",
  "private_key": "-----BEGIN PRIVATE KEY-----\nLLAVEPRIVADAMUYLARGA\n-----END PRIVATE KEY-----\n",
  "client_email": "nombre@nombre-12345.iam.gserviceaccount.com",
  "client_id": "987654321",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/nombre%40nombre-12345.iam.gserviceaccount.com"
}
```
10. Copia el __ID único__

##### 2 - Configuracion de Delegar la autoridad de todo el dominio a la cuenta de servicio

1. Sigue los pasos descritos en este [link](https://developers.google.com/identity/protocols/oauth2/service-account#delegatingauthority)
>- Debes usar el Id único que copiaste en el paso anterior**
>- Para agregar permisos de crear eventos en Calendar debes evitar usar el Scope** _.readonly_ , puedes agregar https://www.googleapis.com/auth/calendar

##### 3 - Crear Calendario en la cuenta que sera encargada de alojar las agendas (diferente a la de administrador)

1. Iniciar secion con la cuenta que estara destinada al calendario (o crear una cuenta ej: agendas@organizacion.cl)
2. Ir a Calendar y crear un calendario (aunque puedes usar el primario de esta cuenta)
3. Ir a configuración y seleccionar el calendario
4. Bajar hasta el apartado __Compartir con determinadas personas__
5. Seleccionar __Añadir personas__ y agregar el __client_email__ contenido en el JSON que se escargo desde credenciales
*Tiende a ser algo como nombre@nombre-12345.iam.gserviceaccount.com*
6. En permisos seleccionar __Hacer cambios y gestionar el uso compartido__
> El sistema indica que solo se le agregaran estos permisos cuando el usuario al que le compartimos el calendario ingrese en Gmail al correo que se le envio y acepte pero como es una cuenta de servicio este paso no es necesario
> Ademas en el campo de permisos solo se mostrara el permiso de menor nivel que es: *Ver solo libre/ocupado*, aunque puedas crear eventos

##### 4 - Crear cliente de la API con Laravel

1. Instalar package de spatie [Github Aqui](https://github.com/spatie/laravel-google-calendar)
2. En el archivo .env agregar las variables
```
GOOGLE_CALENDAR_ID=@agendas@organizacion.cl
GOOGLE_CALENDAR_IMPERSONATE=agendas@organizacion.cl
GOOGLE_CALENDAR_AUTH_PROFILE=service_account
```
3. Copiar archivo credncial en formato JSON descargado en proyecto-laravel/storage/app/google-calendar/
4. Configurar el nombre del archivo credencial en /proyecto-laravel/config/google-calendar.php en el array 'service_account'.
5. Si se realizaron varias pruebas se debe eliminar el cache ya que podria resultar con errores de permisos producidos por credenciales antiguas.
```bash
php artisan clear
php artisan config:clear
php artisan cache:clear
php artisan routes:clear
php artisan view:clear
```
6. Probar el funcionamiento (codigo agregado en el archivo de rutas pero se recomenta crear un contralador)
```php
use Spatie\GoogleCalendar\Event;
use Carbon\Carbon;
Route::get('crear_evento', function () {
    $event = new Event;
    $event->name = 'Prueba de calendar con dominio';
    $event->description = 'Event description';
    $event->startDateTime = Carbon::now();
    $event->endDateTime = Carbon::now()->addHour();
    $event->addAttendee([
        'email' => 'benjamin.olguin@organizacion.cl',
        'name' => 'Benjamin Olguin',
        'comment' => 'Prueba',
    ]);
    $event->save();
    dd($event);
});
```
> Corroborar que se pueda invitar personas con el metodo addAttendee. Si todo funciona bien mostrara el evento recien creado con la instrucción dd($event)

