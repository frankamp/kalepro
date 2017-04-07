<?php
require_once __DIR__."/../import/class-recipe-pro-easyrecipe-importer.php";

class Recipe_Pro_Importer {

	const STATUS_READY = 'ready';
	const STATUS_IMPORTING = 'importing';
	private $optionName = 'recipepro_importer_status';

	private function get_importer( $name ) {
		switch ($name):
			case 'easyrecipe':
				return "Recipe_Pro_EasyRecipe_Importer";
				break;
			default:
				wp_die( "Attempted to import an unregistered type", 500 );
		endswitch;
	}

	private function get_state( $default ) {
		$importer_state = get_option( $this->optionName );
		if ( $importer_state === false) {
			// we're intentionally using a plain associative array
			// throughout so that serialization is easy when persisting
			$importer_state = array(
				'status' => $default,
				'position' => 0,
				'total' => 0,
				'importer' => '',
				'imported' => array(),
				'notes' => array(),
				'errored' => array(),
				'errorMessages' => array()
			);
			add_option( $this->optionName, $importer_state, null, 'no');
		}
		return $importer_state;
	}

	private function set_status( $newStatus, $otherProperties = array() ) {
		$importer_state = $this->get_state( self::STATUS_READY );
		$importer_state['status'] = $newStatus;
		foreach ( $otherProperties as $key => $value ) {
			$importer_state[$key] = $value;
			//error_log( "setting status key " . $key . " to " . $value );
		}
		update_option( $this->optionName, $importer_state );
	}

	private function get_all_post_ids() {
		return get_posts( array( 'numberposts' => -1, 'fields' => 'ids') );
	}

	public function cancel() {
		delete_option( $this->optionName );
		return $this->get_state( self::STATUS_READY );
	}

	public function do_work() {
		$state = $this->get_state( self::STATUS_READY );
		$done = false;
		//error_log( "Doing work" );
		if ( $state['status'] == self::STATUS_IMPORTING ) {
			// find where we are at, loop
			//error_log( "Work: We're importing at position " . $state['position'] );
			if ( $state['position'] > ($state['total']-1) ) {
				$done = true;
				//error_log( "Work: Done" );
			} else {
				$posts = $this->get_all_post_ids();
				if ( count( $posts ) != $state['total'] ) {
					$this->set_status(self::STATUS_READY, array(
						'errorMessages' => array( "The number of posts changed during import. Run the import again to ensure everything is converted." )
					));
				} else {
					$post_id = $posts[$state['position']];
					$post = get_post( $post_id );
					$importer = $this->get_importer( $state['importer'] );
					//error_log( "Work: checking post " . $post_id );
					if ( $importer::is_instance( $post ) ) {
						//error_log( "Work: converting post " . $post_id );
						$result = $importer::convert($post);
						if ( $result->success ) {
							array_push( $state['imported'], $post_id);
						} else {
							array_push( $state['errored'], $post_id);
							array_push( $state['errorMessages'], "An error occurred importing post $post_id." );
						}
						if ( count( $result->notes ) > 0 ) {
							$state['notes'][$post_id] = $result->notes;
						}
					}
					//error_log( "Work: updating position post " . $post_id );
					$this->set_status(self::STATUS_IMPORTING, array(
						'position' => $state['position'] + 1,
						'imported' => $state['imported'],
						'errored' => $state['errored'],
						'notes' => $state['notes'],
						'errorMessages' => $state['errorMessages']
					));
				}
			}			
		}
		if ( $done ) {
			$this->set_status( self::STATUS_READY );
		}
		return $this->get_state( self::STATUS_READY );
	}

	public function begin_import( $importer ) {
		$state = $this->get_state( self::STATUS_READY );
		if ( $state['status'] == self::STATUS_READY ) {
			$total = count( $this->get_all_post_ids() );
			$this->set_status(self::STATUS_IMPORTING, array(
				'total' => $total,
				'position' => 0,
				'importer' => $importer,
				'imported' => array(),
				'errored' => array(),
				'notes' => array(),
				'errorMessages' => array()
			));
		}
		return $this->get_state( self::STATUS_READY );
	}
}