#  Pasos para subir Proyecto hecho en Laravel 


## Instalar Apache2
- sudo apt-get install apache2

## Configurar index.php
- sudo nano /etc/apache2/mods-enabled/dir.conf
> Priorizar index.php en la lista)

## Mysql
- sudo apt-get install mysql-server
- mysql_secure_installation

## Extra (No es nesesario)
## Habilitar conexiones remotas
- editar /etc/mysql/mysql.conf.d/mysql.cnf

descomentar 
```skip-external-locking```

```bind-address = 127.0.0.1``` 
---> bind-address = 0.0.0.0

```#skip-networking``` (comentar)

- sudo service mysql restart
- mysql -u root -p
```
GRANT ALL PRIVILEGES ON basededatos.* TO usuario@'%' IDENTIFIED BY 'password';
FLUSH PRIVILEGES;
```

>- Si los problemas persisten
>- ```nano /etc/hosts/allow ```
>- Y añadimos la linea
>- ```mysqld: all```


## Crear usuario y base de datos (con privilegios para una base y conexiones remotas '%')
```
create database basededatos;
create user usuario identified by password;
grant all privileges on basededatos.* to usuario@'%' identified by 'password';
```

## Instalar PHP
- sudo apt-get install php libapache2-mod-php php-mcrypt php-mysql
- sudo nano /etc/apache2/mods-enabled/dir.conf
- sudo systemctl restart apache2

## Configurar Laravel

## Habilitar mod rewrite de apache
- sudo a2enmod rewrite

## Composer
- cd
- curl -sS https://getcomposer.org/installer | php
- sudo mv composer.phar /usr/local/bin/composer

## Git
- sudo apt-get install git

## Crear virtualhost
- sudo nano /etc/apache2/sites-available/my_app.conf
> Escribir
```
<VirtualHost *:80>
    ServerName my-site.com
    ServerAlias Xxx.ZxZ.1X7.XxX #your server ip

    DocumentRoot /var/www/html/your-project-name/public
    <Directory /var/www/html/your-project-name/public>
        # Don't show directory index
        Options -Indexes +FollowSymLinks +MultiViews

        # Allow .htaccess files
        AllowOverride All
	#Options FollowSymLinks Indexes                 
	Order allow,deny
	Allow from all

        # Allow web access to this directory
        # Require all granted
    </Directory>

    # Error and access logs
    ErrorLog ${APACHE_LOG_DIR}/my-site.error.log
    # Possible values include: debug, info, notice, warn, error, crit,
    # alert, emerg.
    LogLevel warn
    CustomLog ${APACHE_LOG_DIR}/my-site.access.log combined
</VirtualHost>
```

## Revisar configuracion de sites-available
- apache2ctl configtest

#Habilitar configuracion del sitio
- sudo a2ensite my_app
- sudo service apache2 reload
- sudo service apache2 restart

##Laravel
- composer dump-autoload
>Configurar archivos .env y config/database.php
- php artisan migrate
- php artisan db:seed

Cambiar permisos a carpetas storage y bootstrap/cache
> **Hay formas mas seguras de configurar permisos usando www-data**
- chmod a+w -R storage
- chmod a+w -R bootstrap/cache


## Extra (Por si hubieran problemas en las rutas)

Cambiar Rutas de public/index.php

```
require __DIR__.'/../bootstrap/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
```

Por las rutas:
```
require __DIR__.'../bootstrap/autoload.php';
$app = require_once __DIR__.'../bootstrap/app.php';
```


## Finalmente si hay algun problema se puede reiniciar los servidores
- sudo service apache2 restart
- sudo service mysql restart
- sudo reboot
