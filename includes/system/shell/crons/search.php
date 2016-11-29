<?php

use TooBasic\Managers\SearchManager;
use TooBasic\Shell\Color;
use TooBasic\Shell\Option;

class SearchCron extends TooBasic\Shell\ShellCron {
	//
	// Constants.
	const OptionForceFullscan = 'ForceFullscan';
	const OptionSearch = 'Search';
	const OptionUpdate = 'Update';
	//
	// Protected methods.
	protected function setOptions() {
		$this->_options->setHelpText("This cron tool allows you to perform periodic tasks related to searchable items.");

		$text = "This options provides a simple interface to run a search.";
		$this->_options->addOption(Option::EasyFactory(self::OptionSearch, ['--search', '-s'], Option::TypeValue, $text, 'value'));

		$text = 'Indexes all pending search entries.';
		$this->_options->addOption(Option::EasyFactory(self::OptionUpdate, ['--update', '-u'], Option::TypeNoValue, $text, 'value'));

		$text = "Forces full-scan of all search entries.\n";
		$text.= "Warning: this may take a long time.";
		$this->_options->addOption(Option::EasyFactory(self::OptionForceFullscan, ['--force-fullscan', '-f'], Option::TypeNoValue, $text, 'value'));
	}
	protected function taskUpdate($spacer = "") {
		echo "{$spacer}Indexing pending search entries: ";
		$count = SearchManager::Instance()->index();
		echo Color::Green('Done')." ({$count} entries indexed)\n";
	}
	protected function taskForceFullscan($spacer = "") {
		echo "{$spacer}Forcing a full-scan of search entries: ";
		$count = SearchManager::Instance()->forceFullScan();
		echo Color::Green('Done')." ({$count} entries indexed)\n";
	}
	protected function taskSearch($spacer = "") {
		$terms = $this->_options->option(self::OptionSearch)->value();

		echo "{$spacer}Searching for '{$terms}'\n";
		$results = SearchManager::Instance()->search($terms);
		foreach($results as $items) {
			foreach($items as $item) {
				echo "{$spacer}\t- {$item}\n";
			}
		}
	}
}
