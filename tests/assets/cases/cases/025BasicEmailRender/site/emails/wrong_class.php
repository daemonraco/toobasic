<?php

class wrong_class extends \TooBasic\Email {
	protected $_layout = 'basic_layout';
	protected function basicRun() {
		$helper = $this->model->email_filler;
		$this->assign('name', $helper->name);
		$this->assign('surname', $helper->surname);

		return $this->status();
	}
	protected function simulation() {
		$this->assign('name', 'SOMENAME');
		$this->assign('surname', 'SOMESURNAME');

		return true;
	}
}
