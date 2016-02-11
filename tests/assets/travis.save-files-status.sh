#!/bin/bash
#
P_MODE=$1;
P_FILES="travis.files-status.before travis.directories-status.before travis.files-status.after travis.directories-status.after";
P_PLACES="site modules cache/system";

for f in $P_FILES; do
	touch $f;
done;

if [ "$P_MODE" == "before" ] || [ "$P_MODE" == "after" ]; then
	find $P_PLACES -type f > travis.files-status.${P_MODE};
	find $P_PLACES -type d > travis.directories-status.${P_MODE};
elif [ "$P_MODE" == "compare" ]; then
	padding='BEGIN{x=0}/^(<|>)/{x++;print "\t"$0}END{if(x==0){print "\tNo difference"}}';
	echo "Comparing files 'before' <-> 'after':";
	diff travis.files-status.before travis.files-status.after | awk "$padding";
	echo "Comparing directories 'before' <-> 'after':";
	diff travis.directories-status.before travis.directories-status.after | awk "$padding";
else
	echo "Unknown command, onli 'before', 'after' and 'compare' are available.";
fi;
