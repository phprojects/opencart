
cp ./upload/config-dist.php ./upload/config.php
cp ./upload/admin/config-dist.php ./upload/admin/config.php
chmod 0777 ./upload/config.php
chmod 0777 ./upload/admin/config.php
chmod -R 0755 ./upload/system/storage/
chmod -R 0777 ./upload/vqmod/
chmod -R 0755 ./upload/image/