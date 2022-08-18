ip_actual_servidor='10.10.10.10'
usuario_actual_servidor='user'
pass_actual_servidor='pass'

ip_nuevo_servidor='10.10.10.11'
usuario_nuevo_servidor='user'
pass_nuevo_servidor='pass'

ruta_proyectos='/var/www/html'

echo "Ejecutar en servidor actual donde se encuentran los proyectos"
cd ${ruta_proyectos}
# Buscar proyectos en servidor que se conectan a bases de datos locales
apps=$(grep -r -l --include=".env" -e "DB_HOST=127.0.0.1" -e "DB_HOST=localhost" -e "DB_HOST=${ip_actual_servidor}" ${ruta_proyectos})
echo "Proyectos a migrar DBs"
echo ${apps}
for app in $apps; 
do 
# Split a BD_DATABASE= para crear lista solo con el nombre de la base de datos
db=$(grep "DB_DATABASE=" "$app" | awk -F'=' '{print $2}');
# Verificar si en servidor nuevo existe base de datos del servidor actual
if [[ ! -z "`mysql -u ${usuario_nuevo_servidor} -h ${ip_nuevo_servidor} -p${pass_nuevo_servidor} -qfsBe "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME='${db}'" 2>&1`" ]];
    then
    # Avisar que existe (determinar si se reemplaza o solo se configura la existente)
    echo "${db} EXISTE"
    # Escribir codigo para borrar o configurar
    else
        # Crear DB y copiar datos desde base de datos existente
        echo "Creando ${db}"
        mysql -u ${usuario_nuevo_servidor} -h ${ip_nuevo_servidor} -p${pass_nuevo_servidor} --execute="CREATE DATABASE ${db}"
        mysqldump -B -u ${ip_actual_servidor} -p${pass_actual_servidor} ${db} > mysql -u ${usuario_nuevo_servidor} -h ${ip_nuevo_servidor} -p${pass_nuevo_servidor} ${db}
        echo "${db} migrada..."
    fi
done


