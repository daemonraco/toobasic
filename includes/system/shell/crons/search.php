<?php

use TooBasic\Managers\SearchManager;
use TooBasic\Shell\Color;
use TooBasic\Shell\Option;

class SearchCron extends TooBasic\Shell\ShellCron {
	//
	// Constants.
	const OptionForceFullscan = 'ForceFullscan';
	const OptionLimit = 'Limit';
	const OptionOffset = 'Offset';
	const OptionSearch = 'Search';
	const OptionUpdate = 'Update';
	//
	// Protected methods.
	protected function setOptions() {
		$this->_options->setHelpText("This cron tool allows you to perform periodic tasks related to searchable items.");

		$text = "When serching this option limits the amount of returned items.\n";
		$text.= "Default is all items.";
		$this->_options->addOption(Option::EasyFactory(self::OptionLimit, ['--limit', '-l'], Option::TypeValue, $text, 'value'));

		$text = "When serching and limiting this option tells where to start.\n";
		$text.= "Default is from the begining.";
		$this->_options->addOption(Option::EasyFactory(self::OptionOffset, ['--offset', '-o'], Option::TypeValue, $text, 'value'));

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
		$limitOpt = $this->_options->option(self::OptionLimit);
		$offsetOpt = $this->_options->option(self::OptionOffset);

		$limit = $limitOpt->activated() ? $limitOpt->value() : 0;
		$offset = $offsetOpt->activated() ? $offsetOpt->value() : 0;
		$info = false;

		$results = SearchManager::Instance()->search($terms, $limit, $offset, null, $info);
		echo "{$spacer}Searching for '".implode(' ', $info[GC_AFIELD_TERMS])."'\n";
		echo "{$spacer}Total count: {$info[GC_AFIELD_COUNT]}".($info[GC_AFIELD_GARBAGE] ? "({$info[GC_AFIELD_GARBAGE]} lost)\n" : "\n");
		echo "{$spacer}Elapsed time {$info[GC_AFIELD_TIMERS][GC_AFIELD_FULL]}ms:\n";
		echo "{$spacer}\t- Searching: {$info[GC_AFIELD_TIMERS][GC_AFIELD_SEARCH]}ms\n";
		echo "{$spacer}\t- Expanding: {$info[GC_AFIELD_TIMERS][GC_AFIELD_EXPAND]}ms\n";
		echo "{$spacer}Results:\n";
		foreach($results as $item) {
			echo "{$spacer}\t- {$item}\n";
		}
	}
}
