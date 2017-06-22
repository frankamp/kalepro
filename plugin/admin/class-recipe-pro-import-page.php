<?php
require_once __DIR__."/../import/class-recipe-pro-importer.php";
require_once __DIR__."/../includes/class-recipe-pro-service.php";
class Recipe_Pro_Import_Page {

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->importer = new Recipe_Pro_Importer();
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name . "importer", plugin_dir_url( __FILE__ ) . 'js/recipe-pro-importer.js', array( 'jquery' ), $this->version, false );
	}

	public function page_display () {
		$html = '';
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		?>
		<style>
			#progressbar {
			  background-color: #aaa;
			  border-radius: 0; /* (height of inner div) / 2 + padding */
			  padding: 2px;
			}
			
		   #progressbar > div {
			   background-color: #afd87a;
			   width: 0%; /* Adjust with JavaScript */
			   height: 20px;
			   border-radius: 0;
		   }
		   #importer {
		   	margin: 20px 0;
		   }	
		</style>
		<div class="wrap">
			<form action='options.php' method='post'>
				<h2><?= __( 'Import Recipes', 'recipe-pro' ) ?></h2>
				<div id="importer">
					<div>Import Status: {{statusValues[status]}}</div>
					<button v-bind:disabled="status != 'ready'" v-on:click="beginImport">Start Import</button>
					<!-- <li v-for="item in importers">
						<strong>{{ item.name }}</strong> {{ item.description }}
						<button v-bind:disabled="status != 'ready'" v-on:click="beginImport" v-bind:name="item.name" v-bind:tag="item.tag">Start Import</button>
					</li> -->

					<div v-if="status != 'ready'">
						Importing
						<div style="width:50%; float left;">
							<div id="progressbar">
							  <div></div>
							</div>
						</div>
						<div style="float: right;">
							<button v-bind:disabled="status == 'ready'" v-on:click="cancel">Close</button>
						</div>
					</div>
					<div v-for="itemId in posts">
						<item v-bind:item-id="itemId"/>
					</div>
					<div v-for="item in errored">
						<item v-bind:item-id="item.itemId" v-bind:error-message="item.errorMessage" />
					</div>
				</div>
			</form>
		</div>
		<?php
	}

	public function ajax_item_data ( ) {
		$item_id = $_POST['item_id'];
		$post = get_post( $item_id );
		$undo = Recipe_Pro_Service::getUndoInformation( $item_id );
		if ( $undo ) {
			$notes = $undo['notes'];
		} else {
			$notes = array();
		}
		$data = array('name' => $post->post_title, 'link' => get_permalink($post), 'notes' => $notes );
		header ( "Content-Type: application/json" );
		echo json_encode( $data );
		wp_die();
	}

	public function ajax_cancel_import ( ) {
		$importer_status = $this->importer->cancel();
		header ( "Content-Type: application/json" );
		echo json_encode( $importer_status );
		wp_die();
	}

	public function ajax_do_import_work ( ) {
		$importer_status = $this->importer->do_work();
		header ( "Content-Type: application/json" );
		echo json_encode( $importer_status );
		wp_die();
	}

	public function ajax_begin_import ( ) {
		$importer_status = $this->importer->begin_import();
		header ( "Content-Type: application/json" );
		echo json_encode( $importer_status );
		wp_die();
	}

}