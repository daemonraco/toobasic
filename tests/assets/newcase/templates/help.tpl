Usage:
	<%$program%> <suite-name> <case-name> [options]
	<%$program%> <task-name>  [options]

Suites:
	- cases
	- cases-by-class
	- cases-by-issue
	- cases-on-selenium
Tasks:
	- help
		Show this help text.

	- add-test-ctrl
		Creates a test controller inside a case directory. Usage:
		<%$program%> <case-full-path>

	- add-asset
		Creates an empty asset for certain case. Usage:
		<%$program%> <suite-name> <case-index> <asset-path> [<asset-type>]
