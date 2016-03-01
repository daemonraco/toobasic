<?php

class SApiReportsAsBasicTypeTest extends TooBasic_SeleniumTestCase {
	//
	// Test cases @{
	public function testCheckingTableRendering() {
		$this->url('?action=this_site_issues');
		$this->checkCurrentSource();
		//
		// Checking table.
		$table = $this->byCssSelector('table#this_site_issues');
		$this->assertTrue(boolval($table), "No table found with id '#this_site_issues'.");
		$this->assertEquals('InternalClass', $table->attribute('class'), "Table attribute 'class' has an unexpected value.");
		//
		// Checking header.
		$thead = $table->byTag('thead');
		$this->assertTrue(boolval($thead), "Table has no header.");

		$headers = $thead->elements($this->using('css selector')->value('th'));
		$this->assertEquals(6, count($headers), "There are more/less headers than expected.");
		$expectedHeaders = [
			'ID',
			'State',
			'Assignee',
			'Reporter',
			'@title_avatar',
			'Description'
		];
		foreach($headers as $pos => $header) {
			$this->assertEquals($expectedHeaders[$pos], $header->text(), 'The '.($pos + 1)."° header has an unexpected value.");
		}
		//
		// Checking body.
		$tbody = $table->byTag('tbody');
		$this->assertTrue(boolval($tbody), "Table has no body.");
		$rows = $tbody->elements($this->using('css selector')->value('tr'));
		//
		// Checking exclusions.
		//	30: total in the JSON sample.
		//	2:  excluded issues.
		//	14: pull requests.
		$this->assertEquals(30 - 2 - 14, count($rows), "There are more/less rows than expected.");
		//
		// Analizing first row information:
		$firstRow = $rows[0];
		$columns = $firstRow->elements($this->using('css selector')->value('td'));
		$this->assertEquals(6, count($columns), "There are more/less columns than expected in the first row.");
		//
		// Checking ID column.
		$anchors = $columns[0]->elements($this->using('css selector')->value('a'));
		$this->assertEquals(1, count($anchors), "There are more/less 'a' tags than expected in the 1st column of 1st row.");
		$this->assertEquals('117', $anchors[0]->text(), "1st column of 1st row has an unexpected value.");
		$this->assertEquals('_blank', $anchors[0]->attribute('target'), "Anchor in the 1st column of 1st row has an unexpected value for attribute 'target'.");
		$this->assertEquals('https://github.com/daemonraco/toobasic/issues/117', $anchors[0]->attribute('href'), "Anchor in the 1st column of 1st row has an unexpected value for attribute 'href'.");
		//
		// Checking state column.
		$spans = $columns[1]->elements($this->using('css selector')->value('span'));
		$this->assertEquals(1, count($spans), "There are more/less 'span' tags than expected in the 2nd column of 1st row.");
		$this->assertEquals('closed', $spans[0]->text(), "2nd column of 1st row has an unexpected value.");
		//
		// Checking assignee column.
		$buttons = $columns[2]->elements($this->using('css selector')->value('button'));
		$this->assertEquals(1, count($buttons), "There are more/less 'button' tags than expected in the 3rd column of 1st row.");
		$this->assertEquals('daemonraco', $buttons[0]->text(), "3rd column of 1st row has an unexpected value.");
		$this->assertEquals('btn-xs btn-danger', $buttons[0]->attribute('class'), "Button in the 3rd column of 1st row has an unexpected value for attribute 'class'.");
		$this->assertEquals("location.href='https://github.com/daemonraco';return false;", $buttons[0]->attribute('onclick'), "Button in the 3rd column of 1st row has an unexpected value for attribute 'onclick'.");
		//
		// Checking reporter column.
		$buttons = $columns[3]->elements($this->using('css selector')->value('button'));
		$this->assertEquals(1, count($buttons), "There are more/less 'button' tags than expected in the 4th column of 1st row.");
		$this->assertEquals('daemonraco', $buttons[0]->text(), "4th column of 1st row has an unexpected value.");
		$this->assertEquals('btn-xs btn-info', $buttons[0]->attribute('class'), "Button in the 4th column of 1st row has an unexpected value for attribute 'class'.");
		$this->assertEquals("location.href='https://github.com/daemonraco';return false;", $buttons[0]->attribute('onclick'), "Button in the 4th column of 1st row has an unexpected value for attribute 'onclick'.");
		//
		// Checking avatar column.
		$images = $columns[4]->elements($this->using('css selector')->value('img'));
		$this->assertEquals(1, count($images), "There are more/less 'button' tags than expected in the 5th column of 1st row.");
		$this->assertRegExp('~width:(.*)24px;~', $images[0]->attribute('style'), "Image in the 5th column of 1st row has an unexpected value for attribute 'style'.");
		$this->assertEquals('https://avatars.githubusercontent.com/u/4871323?v=3', $images[0]->attribute('src'), "Image in the 5th column of 1st row has an unexpected value for attribute 'src'.");
		//
		// Checking avatar column.
		$codes = $columns[5]->elements($this->using('css selector')->value('pre'));
		$this->assertEquals(1, count($codes), "There are more/less 'pre' tags than expected in the 6th column of 1st row.");
		$this->assertRegExp('~## What to do~m', $codes[0]->text(), "6th column of 1st row has an unexpected value.");
		$this->assertEquals('value', $codes[0]->attribute('data-some'), "Code in the 6th column of 1st row has an unexpected value for attribute 'data-some'.");
	}
	// @}
}
