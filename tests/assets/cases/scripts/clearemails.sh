#!/bin/bash
#
find "${TRAVISCI_EMAIL_TMP}/fake-mailbox" -type f | xargs sudo rm -fv;
