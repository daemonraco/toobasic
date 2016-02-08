#!/bin/bash
#
MYSQL_AUTHORIZATION="-u ${TRAVISCI_MYSQL_USERNAME}"
#
if [ -n "${TRAVISCI_MYSQL_PASSWORD}" ]; then
	MYSQL_AUTHORIZATION="${MYSQL_AUTHORIZATION} -p${TRAVISCI_MYSQL_PASSWORD}";
fi;
#
echo "Creating database 'travis_test' (user: '${TRAVISCI_MYSQL_USERNAME}'):\n";
mysql ${MYSQL_AUTHORIZATION} << __END_SQL__ 2>&1
show databases;
create database travis_test;
show databases;
__END_SQL__
