<?php

/**
 * @file loader.php
 * @author Alejandro Dario Simi
 */
//
// SuperLoader main list.
$SuperLoader = array();
//
// Basics @{
$SuperLoader['TooBasic\\AbstractExporter'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/AbstractExporter.php";
$SuperLoader['TooBasic\\AbstractExports'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/AbstractExports.php";
$SuperLoader['TooBasic\\Adapters\\Adapter'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/Adapter.php";
$SuperLoader['TooBasic\\CacheException'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/Exception.php";
$SuperLoader['TooBasic\\ConfigException'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/Config.php";
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
$SuperLoader['TooBasic\\Manifest'] = "{$Directories[GC_DIRECTORIES_INCLUDES]}/Manifest.php";
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
$SuperLoader['TooBasic\\Managers\\ActionsManager'] = "{$Directories[GC_DIRECTORIES_MANAGERS]}/ActionsManager.php";
$SuperLoader['TooBasic\\Managers\\ConfigsManager'] = "{$Directories[GC_DIRECTORIES_MANAGERS]}/ConfigsManager.php";
$SuperLoader['TooBasic\\Managers\\DBManager'] = "{$Directories[GC_DIRECTORIES_MANAGERS]}/DBManager.php";
$SuperLoader['TooBasic\\Managers\\DBStructureManager'] = "{$Directories[GC_DIRECTORIES_MANAGERS]}/DBStructureManager.php";
$SuperLoader['TooBasic\\Managers\\DBStructureManagerExeption'] = "{$Directories[GC_DIRECTORIES_MANAGERS]}/DBStructureManager.php";
$SuperLoader['TooBasic\\Managers\\EmailsManager'] = "{$Directories[GC_DIRECTORIES_MANAGERS]}/EmailsManager.php";
$SuperLoader['TooBasic\\Managers\\Manager'] = "{$Directories[GC_DIRECTORIES_MANAGERS]}/Manager.php";
$SuperLoader['TooBasic\\Managers\\ManifestsManager'] = "{$Directories[GC_DIRECTORIES_MANAGERS]}/ManifestsManager.php";
$SuperLoader['TooBasic\\Managers\\RoutesManager'] = "{$Directories[GC_DIRECTORIES_MANAGERS]}/RoutesManager.php";
$SuperLoader['TooBasic\\Managers\\ServicesManager'] = "{$Directories[GC_DIRECTORIES_MANAGERS]}/ServicesManager.php";
$SuperLoader['TooBasic\\Managers\\ShellManager'] = "{$Directories[GC_DIRECTORIES_MANAGERS]}/ShellManager.php";
$SuperLoader['TooBasic\\Managers\\UrlManager'] = "{$Directories[GC_DIRECTORIES_MANAGERS]}/UrlManager.php";
// @}
//
// Cache adapters @{
$SuperLoader['TooBasic\\Adapters\\Cache\\Adapter'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_CACHE]}/Adapter.php";
$SuperLoader['TooBasic\\Adapters\\Cache\\DB'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_CACHE]}/DB.php";
$SuperLoader['TooBasic\\Adapters\\Cache\\DBMySQL'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_CACHE]}/DBMySQL.php";
$SuperLoader['TooBasic\\Adapters\\Cache\\DBPostgreSQL'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_CACHE]}/DBPostgreSQL.php";
$SuperLoader['TooBasic\\Adapters\\Cache\\DBSQLite'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_CACHE]}/DBSQLite.php";
$SuperLoader['TooBasic\\Adapters\\Cache\\File'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_CACHE]}/File.php";
$SuperLoader['TooBasic\\Adapters\\Cache\\Memcache'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_CACHE]}/Memcache.php";
$SuperLoader['TooBasic\\Adapters\\Cache\\Memcached'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_CACHE]}/Memcached.php";
$SuperLoader['TooBasic\\Adapters\\Cache\\NoCache'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_CACHE]}/NoCache.php";
$SuperLoader['TooBasic\\Adapters\\Cache\\Redis'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_CACHE]}/Redis.php";
// @}
//
// Database adapters @{
$SuperLoader['TooBasic\\Adapters\\DB\\Adapter'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_DB]}/Adapter.php";
$SuperLoader['TooBasic\\Adapters\\DB\\QueryAdapter'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_DB]}/QueryAdapter.php";
$SuperLoader['TooBasic\\Adapters\\DB\\QueryMySQL'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_DB]}/QueryMySQL.php";
$SuperLoader['TooBasic\\Adapters\\DB\\QueryPostgreSQL'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_DB]}/QueryPostgreSQL.php";
$SuperLoader['TooBasic\\Adapters\\DB\\QuerySQLite'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_DB]}/QuerySQLite.php";
$SuperLoader['TooBasic\\Adapters\\DB\\SpecAdapter'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_DB]}/SpecAdapter.php";
$SuperLoader['TooBasic\\Adapters\\DB\\SpecMySQL'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_DB]}/SpecMySQL.php";
$SuperLoader['TooBasic\\Adapters\\DB\\SpecPostgreSQL'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_DB]}/SpecPostgreSQL.php";
$SuperLoader['TooBasic\\Adapters\\DB\\SpecSQLite'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_DB]}/SpecSQLite.php";
$SuperLoader['TooBasic\\Adapters\\DB\\Version1'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_DB]}/Version1.php";
$SuperLoader['TooBasic\\Adapters\\DB\\Version2'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_DB]}/Version2.php";
$SuperLoader['TooBasic\\Adapters\\DB\\VersionAdapter'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_DB]}/VersionAdapter.php";
// @}
//
// View adapters @{
$SuperLoader['TooBasic\\Adapters\\View\\Adapter'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_VIEW]}/Adapter.php";
$SuperLoader['TooBasic\\Adapters\\View\\BasicAdapter'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_VIEW]}/BasicAdapter.php";
$SuperLoader['TooBasic\\Adapters\\View\\Dump'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_VIEW]}/Dump.php";
$SuperLoader['TooBasic\\Adapters\\View\\JSON'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_VIEW]}/JSON.php";
$SuperLoader['TooBasic\\Adapters\\View\\Printr'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_VIEW]}/Printr.php";
$SuperLoader['TooBasic\\Adapters\\View\\Serialize'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_VIEW]}/Serialize.php";
$SuperLoader['TooBasic\\Adapters\\View\\Smarty'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_VIEW]}/Smarty.php";
$SuperLoader['TooBasic\\Adapters\\View\\XML'] = "{$Directories[GC_DIRECTORIES_ADAPTERS_VIEW]}/XML.php";
// @}
//
// Representations @{
$SuperLoader['TooBasic\\Representations\\ItemRepresentation'] = "{$Directories[GC_DIRECTORIES_REPRESENTATIONS]}/ItemRepresentation.php";
$SuperLoader['TooBasic\\Representations\\ItemsFactory'] = "{$Directories[GC_DIRECTORIES_REPRESENTATIONS]}/ItemsFactory.php";
$SuperLoader['TooBasic\\Representations\\ItemsFactoryProvider'] = "{$Directories[GC_DIRECTORIES_REPRESENTATIONS]}/ItemsFactoryProvider.php";
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
// Config interpreters includes @{
$SuperLoader['TooBasic\\Config'] = "{$Directories[GC_DIRECTORIES_CONFIG_INTERPRETERS]}/Config.php";
$SuperLoader['TooBasic\\ComplexConfig'] = "{$Directories[GC_DIRECTORIES_CONFIG_INTERPRETERS]}/ComplexConfig.php";
// @}
//
// Simple API Reader includes @{
$SuperLoader['TooBasic\\Managers\\SApiManager'] = "{$Directories[GC_DIRECTORIES_SAPIREADER]}/SApiManager.php";
$SuperLoader['TooBasic\\SApiReader'] = "{$Directories[GC_DIRECTORIES_SAPIREADER]}/SApiReader.php";
$SuperLoader['TooBasic\\SApiReaderException'] = "{$Directories[GC_DIRECTORIES_SAPIREADER]}/SApiReader.php";
$SuperLoader['TooBasic\\SApiReaderJSON'] = "{$Directories[GC_DIRECTORIES_SAPIREADER]}/SApiReaderJSON.php";
$SuperLoader['TooBasic\\SApiReaderXML'] = "{$Directories[GC_DIRECTORIES_SAPIREADER]}/SApiReaderXML.php";
// @}
//
// Known librearies @{
$SuperLoader['Smarty'] = array(
	"{$Directories[GC_DIRECTORIES_LIBRARIES]}/smarty/Smarty.class.php",
	"{$Directories[GC_DIRECTORIES_LIBRARIES]}/smarty.git/libs/Smarty.class.php"
);
$SuperLoader['Predis\\Client'] = array(
//$SuperLoader['\\Predis\\Autoloader'] = array(
	"{$Directories[GC_DIRECTORIES_LIBRARIES]}/predis/autoload.php",
	"{$Directories[GC_DIRECTORIES_LIBRARIES]}/predis.git/autoload.php"
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
