<?php

<%if $issueId%>
/**
 * @issue <%$issueId%>
 * @brief <%$issueTitle%>
 * @url https://github.com/daemonraco/toobasic/issues/<%$issueId%>
 */

<%/if%>
class <%$className%>Test extends <%$parentClassName%> {
	//
	// Test cases @{
	public function testTheTruth() {
		$this->assertTrue(false, "TODO");
	}
	// @}
}
