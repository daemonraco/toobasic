# Basics
## What is this page?
Well, you've just downloaded this framework and to give you some hints we provided you with a starter module. If check the folder __ROOTDIR/modules__ you'll find a subfolder called __starterdoc__, just delete it or rename it to **_starterdoc** and you'll have a complete empty site ot play with.

## Basic Directory Structure
__TooBasic__ has a main list of  directories that must remain there:
* `cache`: Every dynamic content ends up here, things like pre-compiled PHPs, temporary thumbnails, etc.
* `config`: Main system configurations and definitions.
* `includes`: __TooBasic__ core logics are stored inside this folder.
* `libraries`: Here you can drop every library you project may require.
* `modules`: This folder is where plugins/modules are installed.
* `shell`: Shell tool's core logics are stored here.
* `site`: Site's customized controllers, views, scripts, etc.
 
## Site/Module Directories Structure
Inside your site folder or any module may contain these folder:
* `configs`
* `controllers`
* `langs`
* `models`: Press this [link](?example=hellomodel) to use example model.
* `scripts`
* `services`
* `snippets`
* `styles`
* `templates`
    * `action`
    * `modal`
____
# Appendix
## Known Debugs
This is a list of known url parameter that may help on debugging time.
* `a_debug`: a description.
