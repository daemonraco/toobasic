sudo mysql -u root -e "CREATE USER '${TRAVISCI_MYSQL_USERNAME}'@'%' IDENTIFIED BY '${TRAVISCI_MYSQL_PASSWORD}'" 2>&1;
sudo mysql -u root -e "CREATE USER '${TRAVISCI_MYSQL_USERNAME}'@'localhost' IDENTIFIED BY '${TRAVISCI_MYSQL_PASSWORD}'" 2>&1;
sudo mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO '${TRAVISCI_MYSQL_USERNAME}'@'%'" 2>&1;
sudo mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO '${TRAVISCI_MYSQL_USERNAME}'@'localhost'" 2>&1;
sudo mysql -u root -e "UPDATE mysql.user SET plugin='mysql_native_password' WHERE User = '${TRAVISCI_MYSQL_USERNAME}'; FLUSH PRIVILEGES" 2>&1;
