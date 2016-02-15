#!/usr/bin/env bash
#
# Create a fake mail-box folder.
mailbox="${TRAVISCI_EMAIL_TMP-/tmp/}fake-mailbox";
mkdir -p $mailbox;

if [ ! -f $mailbox/counter ]; then
	echo '0' > $mailbox/counter;
fi;

count=$(cat $mailbox/counter);
count=$(($count + 1));
echo $count > $mailbox/counter;

name="${mailbox}/message_${count}.eml";
while read line; do
	echo $line >> $name;
done;

exit 0
