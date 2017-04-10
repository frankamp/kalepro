<?php

class Recipe_Pro_Import_Log {
	public function __construct() {
		$this->notes = array();
		$this->success = false;
		$this->recipe = null;
	}

	public function addNote( $message ) {
		array_push( $this->notes, $message);
	}

}