#!/bin/bash
#
sudo -u postgres psql << __END_SQL__ 2>&1
drop database ${TRAVISCI_PSQL_DBNAME};
drop owned by ${TRAVISCI_PSQL_USERNAME};
drop user ${TRAVISCI_PSQL_USERNAME};
__END_SQL__
