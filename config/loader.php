<?php

//
// SuperLoader main list.
$SuperLoader = array();
//
// Basics @{
$SuperLoader['TooBasic\\AbstractExporter'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/AbstractExporter.php";
$SuperLoader['TooBasic\\AbstractExports'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/AbstractExports.php";
$SuperLoader['TooBasic\\Adapter'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/Adapter.php";
$SuperLoader['TooBasic\\Controller'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/Controller.php";
$SuperLoader['TooBasic\\ControllerExports'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/ControllerExports.php";
$SuperLoader['TooBasic\\DBException'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/Exception.php";
$SuperLoader['TooBasic\\Email'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/Email.php";
$SuperLoader['TooBasic\\EmailExports'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/EmailExports.php";
$SuperLoader['TooBasic\\EmailLayout'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/EmailLayout.php";
$SuperLoader['TooBasic\\EmailPayload'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/EmailPayload.php";
$SuperLoader['TooBasic\\ErrorController'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/ErrorController.php";
$SuperLoader['TooBasic\\Exception'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/Exception.php";
$SuperLoader['TooBasic\\Exporter'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/Exporter.php";
$SuperLoader['TooBasic\\FactoryClass'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/FactoryClass.php";
$SuperLoader['TooBasic\\Layout'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/Layout.php";
$SuperLoader['TooBasic\\MagicProp'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/MagicProp.php";
$SuperLoader['TooBasic\\Model'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/Model.php";
$SuperLoader['TooBasic\\ModelsFactory'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/ModelsFactory.php";
$SuperLoader['TooBasic\\Names'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/Names.php";
$SuperLoader['TooBasic\\Params'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/Params.php";
$SuperLoader['TooBasic\\ParamsStack'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/ParamsStack.php";
$SuperLoader['TooBasic\\Paths'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/Paths.php";
$SuperLoader['TooBasic\\Service'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/Service.php";
$SuperLoader['TooBasic\\Singleton'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/Singleton.php";
$SuperLoader['TooBasic\\Timer'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/Timer.php";
$SuperLoader['TooBasic\\Translate'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/Translate.php";
// @}
//
// Managers @{
$SuperLoader['TooBasic\\ActionsManager'] = "{$Directories[GC_DIRECTORIES_MANAGERS]}/ActionsManager.php";
$SuperLoader['TooBasic\\DBManager'] = "{$Directories[GC_DIRECTORIES_MANAGERS]}/DBManager.php";
$SuperLoader['TooBasic\\DBStructureManager'] = "{$Directories[GC_DIRECTORIES_MANAGERS]}/DBStructureManager.php";
$SuperLoader['TooBasic\\Managers\\EmailsManager'] = "{$Directories[GC_DIRECTORIES_MANAGERS]}/EmailsManager.php";
$SuperLoader['TooBasic\\Manager'] = "{$Directories[GC_DIRECTORIES_MANAGERS]}/Manager.php";
$SuperLoader['TooBasic\\RoutesManager'] = "{$Directories[GC_DIRECTORIES_MANAGERS]}/RoutesManager.php";
$SuperLoader['TooBasic\\ServicesManager'] = "{$Directories[GC_DIRECTORIES_MANAGERS]}/ServicesManager.php";
$SuperLoader['TooBasic\\ShellManager'] = "{$Directories[GC_DIRECTORIES_MANAGERS]}/ShellManager.php";
$SuperLoader['TooBasic\\UrlManager'] = "{$Directories[GC_DIRECTORIES_MANAGERS]}/UrlManager.php";
// @}
//
// Cache adapters @{
$SuperLoader['TooBasic\\CacheAdapter'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_CACHE]}/CacheAdapter.php";
$SuperLoader['TooBasic\\CacheAdapterDB'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_CACHE]}/CacheAdapterDB.php";
$SuperLoader['TooBasic\\CacheAdapterDBMySQL'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_CACHE]}/CacheAdapterDBMySQL.php";
$SuperLoader['TooBasic\\CacheAdapterFile'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_CACHE]}/CacheAdapterFile.php";
$SuperLoader['TooBasic\\CacheAdapterMemcache'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_CACHE]}/CacheAdapterMemcache.php";
$SuperLoader['TooBasic\\CacheAdapterMemcached'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_CACHE]}/CacheAdapterMemcached.php";
$SuperLoader['TooBasic\\CacheAdapterNoCache'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_CACHE]}/CacheAdapterNoCache.php";
// @}
//
// Database adapters @{
$SuperLoader['TooBasic\\DBAdapter'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_DB]}/DBAdapter.php";
$SuperLoader['TooBasic\\DBSpecAdapter'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_DB]}/DBSpecAdapter.php";
$SuperLoader['TooBasic\\DBSpecAdapterMySQL'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_DB]}/DBSpecAdapterMySQL.php";
$SuperLoader['TooBasic\\DBSpecAdapterSQLite'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_DB]}/DBSpecAdapterSQLite.php";
// @}
//
// View adapters @{
$SuperLoader['TooBasic\\ViewAdapter'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_VIEW]}/ViewAdapter.php";
$SuperLoader['TooBasic\\ViewAdapterBasic'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_VIEW]}/ViewAdapterBasic.php";
$SuperLoader['TooBasic\\ViewAdapterDump'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_VIEW]}/ViewAdapterDump.php";
$SuperLoader['TooBasic\\ViewAdapterJSON'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_VIEW]}/ViewAdapterJSON.php";
$SuperLoader['TooBasic\\ViewAdapterPrint'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_VIEW]}/ViewAdapterPrint.php";
$SuperLoader['TooBasic\\ViewAdapterSerialize'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_VIEW]}/ViewAdapterSerialize.php";
$SuperLoader['TooBasic\\ViewAdapterSmarty'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_VIEW]}/ViewAdapterSmarty.php";
$SuperLoader['TooBasic\\ViewAdapterXML'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_VIEW]}/ViewAdapterXML.php";
// @}
//
// Representations @{
$SuperLoader['TooBasic\\ItemRepresentation'] = "{$Directories[GC_DIRECTORIES_REPRESENTATIONS]}/ItemRepresentation.php";
$SuperLoader['TooBasic\\ItemsFactory'] = "{$Directories[GC_DIRECTORIES_REPRESENTATIONS]}/ItemsFactory.php";
$SuperLoader['TooBasic\\ItemsFactoryProvider'] = "{$Directories[GC_DIRECTORIES_REPRESENTATIONS]}/ItemsFactoryProvider.php";
// @}
//
// Shell includes @{
$SuperLoader['TooBasic\\Shell\\Color'] = "{$Directories[GC_DIRECTORIES_SHELL_INCLUDES]}/Color.php";
$SuperLoader['TooBasic\\Shell\\Option'] = "{$Directories[GC_DIRECTORIES_SHELL_INCLUDES]}/Option.php";
$SuperLoader['TooBasic\\Shell\\Options'] = "{$Directories[GC_DIRECTORIES_SHELL_INCLUDES]}/Options.php";
$SuperLoader['TooBasic\\Shell\\ShellCron'] = "{$Directories[GC_DIRECTORIES_SHELL_INCLUDES]}/ShellCron.php";
$SuperLoader['TooBasic\\Shell\\ShellTool'] = "{$Directories[GC_DIRECTORIES_SHELL_INCLUDES]}/ShellTool.php";
$SuperLoader['TooBasic\\Shell\\Scaffold'] = "{$Directories[GC_DIRECTORIES_SHELL_INCLUDES]}/Scaffold.php";
// @}
//
// Known librearies @{
$SuperLoader['Smarty'] = array(
	"{$Directories[GC_DIRECTORIES_LIBRARIES]}/smarty/Smarty.class.php",
	"{$Directories[GC_DIRECTORIES_LIBRARIES]}/smarty.git/libs/Smarty.class.php"
);
// @}
//
// This is the function in charge of loading TooBasic clases when they are
// required.
spl_autoload_register(function($class) {
	global $SuperLoader;

	if(isset($SuperLoader[$class])) {
		$path = false;

		if(!is_array($SuperLoader[$class])) {
			$SuperLoader[$class] = array($SuperLoader[$class]);
		}
		foreach($SuperLoader[$class] as $loaderPath) {
			$path = \TooBasic\Sanitizer::DirPath($loaderPath);
			$path = is_readable($path) ? $path : false;

			if($path) {
				break;
			}
		}

		if($path) {
			if(isset($_REQUEST['debugloader']) || isset($_ENV['debugloader'])) {
				\TooBasic\debugThing("TooBasic SuperLoader: Loading class '{$class}' from '{$path}'.");
			}
			require_once $path;
		} else {
			if((isset($_REQUEST['debugloader']) && $_REQUEST['debugloader'] == 'heavy') || ( isset($_ENV['debugloader']) && $_ENV['debugloader'] == 'heavy')) {
				\TooBasic\debugThing("TooBasic SuperLoader: Loading class '{$class}' from: '".implode("', '", $SuperLoader[$class])."'.");
			}
		}
	}
});
