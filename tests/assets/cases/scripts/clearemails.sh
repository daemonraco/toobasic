#!/bin/bash
#
if [ -d "/tmp/fake-mailbox" ]; then
	find "/tmp/fake-mailbox" -type f | xargs sudo rm -fv;
fi;
