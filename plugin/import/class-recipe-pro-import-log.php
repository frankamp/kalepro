<?php

class Recipe_Pro_Import_Log {
	public function __construct() {
		$this->notes = array();
		$this->successful = false;
		$this->recipe = null;
	}

	public function addNote( $message ) {
		array_push( $this->notes, $message);
	}

}