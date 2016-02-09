#!/bin/bash
#
#drop database if exists ${TRAVISCI_PSQL_DBNAME};
#drop owned by ${TRAVISCI_PSQL_USERNAME};
#drop user ${TRAVISCI_PSQL_USERNAME};
sudo -u postgres psql << __END_SQL__ 2>&1
create user ${TRAVISCI_PSQL_USERNAME} with password '${TRAVISCI_PSQL_PASSWORD}';
create database ${TRAVISCI_PSQL_DBNAME};
grant all privileges on database ${TRAVISCI_PSQL_DBNAME} to ${TRAVISCI_PSQL_USERNAME};
__END_SQL__
