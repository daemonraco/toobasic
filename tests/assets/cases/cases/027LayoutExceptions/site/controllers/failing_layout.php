<?php

class FailingLayoutController extends \TooBasic\Layout {
	public function basicRun() {
		$this->setError(HTTPERROR_INTERNAL_SERVER_ERROR, 'FORCED INTERNAL ERROR');

		return $this->status();
	}
}
