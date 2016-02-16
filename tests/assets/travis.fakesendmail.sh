#!/usr/bin/env bash
#
# Create a fake mail-box folder.
mailbox="/tmp/fake-mailbox";
echo "mailbox=${mailbox}" > /home/travis/build/daemonraco/toobasic/fakemail.txt; #DEBUG

if [ ! -f "${mailbox}/counter" ]; then
	echo '0' > "${mailbox}/counter";
fi;

count=$(cat "${mailbox}/counter");
count=$(($count + 1));
echo $count > "${mailbox}/counter";

name="${mailbox}/message_${count}.eml";
while read line; do
	echo $line >> $name;
	echo "${line}" >> /home/travis/build/daemonraco/toobasic/fakemail.txt; #DEBUG
done;

exit 0
