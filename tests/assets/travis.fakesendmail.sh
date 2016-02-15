#!/usr/bin/env bash
#
# Create a fake mail-box folder.
mailbox="${TRAVISCI_EMAIL_TMP-/tmp/}fake-mailbox";
echo "mailbox=${mailbox}" > /home/travis/build/daemonraco/toobasic/fakemail.txt;
#mkdir -p $mailbox;

if [ ! -f $mailbox/counter ]; then
	echo '0' > $mailbox/counter;
fi;

count=$(cat $mailbox/counter);
count=$(($count + 1));
echo $count > $mailbox/counter;

name="${mailbox}/message_${count}.eml";
while read line; do
	echo $line >> $name;
	echo "${line}" >> /home/travis/build/daemonraco/toobasic/fakemail.txt;
done;

exit 0
