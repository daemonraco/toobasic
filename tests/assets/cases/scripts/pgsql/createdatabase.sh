#!/bin/bash
#
sudo -u postgres psql << __END_SQL__ 2>&1
create user ${TRAVISCI_PSQL_USERNAME} with password '${TRAVISCI_PSQL_PASSWORD}';
create database ${TRAVISCI_PSQL_DBNAME};
grant all privileges on database ${TRAVISCI_PSQL_DBNAME} to ${TRAVISCI_PSQL_USERNAME};
__END_SQL__
