<?php

class HomeController extends Controller {
	protected function basicRun() {
		$out = false;

		debugit("hello world!");
		$test = $this->model->test;

		return $out;
	}
}
