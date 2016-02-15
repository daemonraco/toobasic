#!/bin/bash
#
if [ -d "${TRAVISCI_EMAIL_TMP}/fake-mailbox" ]; then
	find "${TRAVISCI_EMAIL_TMP}/fake-mailbox" -type f | xargs sudo rm -fv;
fi;
