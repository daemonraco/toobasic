#!/bin/bash
#
MYSQL_AUTHORIZATION="-u ${TRAVISCI_MYSQL_USERNAME}"
#
if [ -n "${TRAVISCI_MYSQL_PASSWORD}" ]; then
	MYSQL_AUTHORIZATION="${MYSQL_AUTHORIZATION} -p${TRAVISCI_MYSQL_PASSWORD}";
fi;
#
mysql ${MYSQL_AUTHORIZATION} << __END_SQL__
create database travis_test;
__END_SQL__
