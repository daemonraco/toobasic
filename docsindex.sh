#!/bin/bash
#
CURRENTDIR=$(echo $PWD);
PROGRAM="$0";
#
F_GENERATE="";
F_SHOWHELP="";
F_SUMMARY="";
P_FILES="README.md docs/ docs/tech/";
F_FILES_LOADED="";
P_GLOSSARY_PATH="docsindex.md";
P_SUMMARY_PATH="SUMMARY.md";
P_SUMMARY_SECTIONS="$(cat docs/summary-section.txt|grep -v '^$')";
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
#	- GenerateSummary()
#	- GenerateSummarySection(string section, string config)
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
function GenerateSummary() {
	#
	# <!--:GBSUMMARY:<tree-path>:<link-name>:<priority>:-->
	#
	LoadFiles;

	echo -e "# Summary\n" | tee "$P_SUMMARY_PATH";
	#
	# Catching GitBooks config.
	local config='';
	for p in $P_FILES; do
		gbconfig="$(cat "${p}"|egrep '^<!--:GBSUMMARY:(.*):-->$')";
		if [ -n "$gbconfig" ]; then
			gbconfig="$(echo "$gbconfig"|sed -e 's:<!--::g' -e 's:-->::g'):${p}";
			config="$(echo -e "${config}\n${gbconfig}")";
		fi;
	done;
	config="$(echo "${config}"|grep -v '^$'|sort -u)";

	{
		sectionConfig="$(echo "$config"|grep "^:GBSUMMARY::")";
		config="$(echo "$config"|grep -v "^:GBSUMMARY::")";
		GenerateSummarySection "" "$sectionConfig";

		while read section; do
			if [ -n "$section" ]; then
				sectionConfig="$(echo "$config"|grep "^:GBSUMMARY:${section}:")";
				config="$(echo "$config"|grep -v "^:GBSUMMARY:${section}:")";
				GenerateSummarySection "$section" "$sectionConfig";
			fi;
		done << __ENDL__
$P_SUMMARY_SECTIONS
__ENDL__
	} | tee -a "$P_SUMMARY_PATH";
}
#
function GenerateSummarySection() {
	local section="$1";
	local config="$2";
	local prefix="	* ";

	if [ -z "$section" ]; then
		prefix="* ";
	else
		echo "* ${section}";
	fi;

	while read line; do
		if [ -n "$line" ]; then
			title="$(echo "$line"|cut -d: -f5)";
			doc="$(echo "$line"  |cut -d: -f7)";
			echo "${prefix}[${title}](${doc})";
		fi;
	done << __ENDL__
$config
__ENDL__
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
		Generates the docsindex at '${P_GLOSSARY_PATH}'.

	-s, --generate-summary
		Generates the docsindex at '${P_SUMMARY_PATH}'.

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
		elif  [ "$1" = "-s" ] || [ "$1" = "--generate-summary" ]; then
			F_SUMMARY="F_SUMMARY";
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
elif [ -n "$F_SUMMARY" ]; then
	TORUN="GenerateSummary";
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
