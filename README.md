# BACKUP DB local a remoto (vps) Usando mysqldump y mysql

## DBeaver
seleccionalos la base de datos remota, clic derecho > tools > dump database
y en la base de datos local, clic derecho > tools > restore database

## local a remoto
En tu computadora local:
- Exporta tu base de datos local a un archivo .sql (lo hacemos desde phpMyAdmin)

Copia el archivo al VPS usando scp:
> scp luna_purificadora_DATE.sql root@69.164.192.248:/root/

Conéctate al VPS:
> ssh root@69.164.192.248 


Importa el respaldo a MySQL dentro del VPS:
> mysql -u luna_purificadora -p luna_purificadora < /root/luna_purificadora_DATE.sql 
- pass: la de toda la vida con la primera minuscula

## remoto a local
abrimos termius y escribimos:
> mysqldump -u root -p luna_purificadora > /root/luna_purificadora_BACKUP.sql

ahora abrir powershell de windows (y SIN entrar a la vps) asi normal
> scp root@69.164.192.248:/root/luna_purificadora_BACKUP.sql "C:\Users\Abraham\Desktop\DEV\Laravel"
nos pedira las pass del akami la ponemos y listo.

- ahora nos vamos a Dbeaver
En DBeaver:
 * Abre la conexión local.
 * Haz clic derecho sobre la base de datos luna_purificadora_local (o el nombre que uses).
 * Selecciona "Tools → Restore Database" (o "Restaurar base de datos").
 * En el campo “Input file”, selecciona el archivo que descargaste:
 * C:\Users\Abraham\Desktop\DEV\Laravel\luna_purificadora_BACKUP.sql

Da clic en Start o Execute.


# VPS - DEPLOY
## local
- recordemos hacer:
> npm run build

git add .
git commit -m "Actualización: ajustes locales"
git push origin main

## Git - remoto
- actualizar cambios que se hicieron en 
ruta del proyecto:
> cd /var/www/purificadora/
> git pull

### comando utiles en remoto:
- Si cambiaste código PHP, paquetes, migraciones o assets,
> composer install --no-dev --optimize-autoloader
> php artisan optimize

- en caso que queramos reforzar el optimize:
```
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
