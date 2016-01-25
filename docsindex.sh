#!/bin/bash
#
CURRENTDIR=$(echo $PWD);
PROGRAM="$0";
#
F_GENERATE="";
F_SHOWHELP="";
P_FILES="README.md docs/ docs/tech/";
F_FILES_LOADED="";
P_GLOSSARY_PATH="docsindex.md";
#
TORUN="";
ERROR="";
ERRORAPPEND="";
#
################################################################################
# Errors
ERR0001="Nothing to do";
ERR0002="Needs more parameters";
#
#################################################################################
# Functions
#	- Generate()
#	- LoadFiles()
#	- SetError(string error_code[, string message])
#	- ShowHelp()
#
function Generate() {
	LoadFiles;

	echo "# TooBasic: Index" | tee "$P_GLOSSARY_PATH";

	for p in $P_FILES; do
		title="$(cat "${p}" | grep '^# ' | cut -c2- | sed 's%TooBasic: %%g')";
		#
		# Fixing non MD lines:
		title="$(echo "${title}" | grep -v '^.*m.*h.*dom.*mon.*dow.*command$')";

		if [ "$p" == "README.md" ]; then
			title="Main Page";
		fi;

		echo "## ${title} (${p}):";
		echo;
		while read line; do
			if [ -n "$line" ]; then
				prefix="";
				subTitle="$line";
				if [ -n "$(echo "$line" | grep '^###')" ]; then
					prefix="	* ";
					subTitle="$(echo "$line"|cut -c5-)";
				elif [ -n "$(echo "$line" | grep '^##')" ]; then
					prefix="* ";
					subTitle="$(echo "$line"|cut -c4-)";
				fi;

				link="${p}#$(echo -e "${subTitle,,}" | sed -e 's%__%_%g' -e 's%_ % %g' -e 's% _% %g' -e 's%\([\?!'"'"'.:>\\(\\)\\$]\)%%g' -e 's% %-%g' -e 's:^_\(.*\):\1:g' -e 's:\(.*\)_$:\1:g')";
#				if [ -n "$(echo "$link"|grep '^docs/')" ]; then
#					link="${link#docs/}";
#				else
#					link="../${link}";
#				fi;
				echo "${prefix}[${subTitle}](${link})";
			fi;
		done << __ENDL__;
$(cat "${p}" | grep '^##')
__ENDL__
		echo;
	done | tee -a "$P_GLOSSARY_PATH";
}
#
function LoadFiles() {
	if [ -z "$F_FILES_LOADED" ]; then
		aux="";
		for p in $P_FILES; do
			if [ -n "$(echo "${p}"|grep '\/$')" ]; then
				aux="${aux} $(find $p -maxdepth 1 -type f|grep '\.md$'|sort|grep -v "${P_GLOSSARY_PATH}")";
			else
				aux="${aux} ${p}";
			fi;
		done;
		P_FILES="$aux";

		F_FILES_LOADED="F_FILES_LOADED";
	fi;
}
#
function SetError() {
	ERROR="$ERROR $1";
	if [ -n "$2" ]; then
		if [ -n "$ERRORAPPEND" ]; then
			ERRORAPPEND="$ERRORAPPEND$(echo)";
		fi;
		ERRORAPPEND="${ERRORAPPEND}[${1}] ${2}";
	fi;
}
#
function ShowHelp() {
	cat << __END_HELP__
Usage:
	$PROGRAM [options]
 
Options:
	-g, --generate
		Generates the docsindex ad '${P_GLOSSARY_PATH}'.

	-h, --help
		Shows this help text.
__END_HELP__
}
#
#################################################################################
# Main
#
# Getting parameters.
needmore="";
addmore="";
while [ -n "$1" ]; do
	if [ -n "$needmore" ]; then
		eval "$needmore='$1'";
		needmore="";
	elif [ -n "$addmore" ]; then
		eval "$addmore=\"\$$addmore\$(echo)$1\"";
		addmore="";
	else
		if  [ "$1" = "-h" ] || [ "$1" = "--help" ]; then
			F_SHOWHELP="F_SHOWHELP";
		elif  [ "$1" = "-g" ] || [ "$1" = "--generate" ]; then
			F_GENERATE="F_GENERATE";
		fi;
	fi;
 
	shift;
done;
#
# Run?
if [ -n "$needmore" ]; then
	SetError "ERR0002";
elif [ -n "$addmore" ]; then
	SetError "ERR0002";
elif [ -n "$F_GENERATE" ]; then
	TORUN="Generate";
elif [ -n "$F_SHOWHELP" ]; then
	TORUN="ShowHelp";
else
	TORUN="ShowHelp";
fi;
#
# Running.
$TORUN;
 
if [ -n "$ERROR" ]; then
	b="";
	for a in $ERROR; do
		b="echo \"Error [$a]: \$$a\"";
		eval "$b" >&2;
		if [ -n "$ERRORAPPEND" ]; then
			echo "Extra error information: $ERRORAPPEND" >&2;
		fi;
	done;
fi;
