<?php
/**
 * Primary class file for REFYN Search
 */
defined('ABSPATH') || exit;

require_once('class-refyn-search-ajax.php');
require_once('class-refyn-search-shortcodes.php');

if(!class_exists('Refyn_Search')){
	class Refyn_Search {
		public function __construct() {
			$this->init();
		}
		public function init() {
			add_action( 'admin_init' , array($this, 'register_settings' ) );
			add_action('wp_ajax_nopriv_refyn_search','Refyn_Ajax_Search');
			add_action('wp_ajax_refyn_search','Refyn_Ajax_Search');
			add_action( 'init', array($this, 'create_page' ), 20);
			add_action( 'init', array($this, 'load_shortcodes' ));
			add_action( 'admin_menu', array($this, 'load_settings' ));
			if (get_option('use_refyn') && !empty(get_option('api_key'))){
				$data = array('url' => esc_url( home_url( '' ) ), 'key' => get_option('api_key'));
				$options = array(
					'http' => array(
						'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
						'method'  => 'POST',
						'content' => http_build_query($data)
					)
				);
				$context  = stream_context_create($options);
				$member = json_decode(file_get_contents("https://api.refyn.org/get_suggestions.php", false, $context),true)["ok"];
				if ($member){
					//checking if the apikey is in the database
					add_action( 'wp_head', array($this, 'load_header' ), 10 );
					add_action( 'wp_footer', array($this, 'load_footer' ), 10 );
					add_action( 'wp_enqueue_scripts', array($this, 'load_assets' ), 20 ); //we need jquery to finish loading
				}
			}
		}
		function load_shortcodes(){
			add_shortcode('refyn-search-page','Refyn_Search_Page');
			add_shortcode('refyn-search','Refyn_Shortcode');
		}
		function create_page(){
			$post_details = array(
				'post_title'    => 'Refyn Search',//get_option('page_title'),
				'post_content'  => '[refyn-search-page]',
				'post_status'   => 'publish',
				'post_author'   => 1,
				'post_type' => 'page'
			);
			if (get_option('use_refyn') && get_option('refyn_page')){
				if (get_option('page_id') == 0){
					$page_id = wp_insert_post( $post_details );
					update_option('page_id',$page_id);
				} else {
					if (get_post(get_option('page_id'))){
						$post_details['ID'] = get_option('page_id');
						wp_update_post( $post_details );
					} else {
						$page_id = wp_insert_post( $post_details );
						update_option('page_id',$page_id);
					}
				}
			} else {
				wp_delete_post(get_option('page_id'), true);
			}
		}
		function load_settings() {
			add_menu_page('Refyn Search', 'Refyn Search', 'manage_options', 'refyn_search_settings', array($this, 'main_settings'), 'https://i0.wp.com/refyn.org/wp-content/uploads/2016/06/favicon.png', 110);
		}
		function main_settings(){
			require_once 'admin/class-refyn-search-settings.php';
		}
		function load_assets() {
			wp_enqueue_script('refyn_scripts', plugin_url . '/assets/js/refyn-search.js', array( 'jquery'), '1.0', true);
			$settings = array(
				"trigger" => strval(get_option( 'trigger' )),
				"no_found" => strval(get_option( 'no_found' )),
				"popup" => strval(get_option( 'use_popup' )),
				"ai" => json_encode(array(
								"max"		=> get_option( 'num_suggestions' ),
								"stopwords" => get_option( 'use_stopwords'),
								"spellcheck" => get_option( 'use_spellcheck'),
								"ai" => get_option( 'use_ai'),
								"history" => get_option( 'use_history'),
								"sound" => get_option( 'use_sound'),
								"scraping" => get_option( 'use_scraping'),
								"cutoff" => get_option( 'cutoff_count'),
							)),
			);
			//send data to javascript as a variable called 'local_vals'
			wp_localize_script('refyn_scripts', 'local_vals', ["key" => get_option('api_key'), "wp_query" => admin_url( 'admin-ajax.php' ), "settings" => $settings] );
			wp_enqueue_style('refyn_styles', plugin_url . '/assets/css/refyn-search.css');
		}	
		function load_header(){
			//loading jquery and bootstrap just in case its not on the website
			echo '
			<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet"/>
			<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/lodash.js/0.10.0/lodash.min.js"></script>
			<script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
			<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
			';
		}
		function load_footer() {
			$home = esc_url( home_url( '/' ) );
			$form = '<form id="refyn-form" method="post" role="search" action="'.$home.'">
								<div class="input-group" >
									<div class="form-outline">
										<input type="hidden" id="s" placeholder="" value="" name="s"/>
										<input id="refyn-input" type="search" class="form-control refyn-pad" placeholder="'.strval(get_option('search_placeholder')).'"/>
									</div>
									<input type="submit" style="display:none">
								</div>
							</form>';
			$style = '';
			$blur = '<div id="blur"></div>';
			$overlay = 'Refyn-Overlay';
			if (get_option('use_popup')==0){
				$form='';
				$overlay = 'Refyn-Dropdown';
				$style='style="width:96%;margin-top:1%;position:relative;"';
				$blur='';
			}
			echo
				'<div id="'.$overlay.'" style="display:none">
					<style>
						.refyn-row:hover{
							background-color: '.get_option('highlight_color').' !important;
						}
						.form-control,.bg-white{
							  background-color: '.get_option('background_color').' !important;
						}
						select.form-control:focus::-ms-value {
						  background-color: '.get_option('background_color').' !important;
						}
						.text-dark {
						  color: '.get_option('primary_color').' !important;
						}
						.text-primary {
						  color: '.get_option('second_color').' !important;
						}
					</style>
					<div class="row justify-content-center align-items-middle">
						<div id="Refyn-Search" '.$style.'>
							'.$form.'
							<div class="panel panel-default border rounded mt-1">
								<div class="panel-body bg-white text-dark pt-2 pb-2">
									<div id="refyn-results" class="scrollbar"></div>
									<div id="refyn-credits" class="panel panel-default pl-3 pr-3 pt-2 pb-1">
										Powered By 
										<a href="https://www.refyn.org">
											<img alt="Refyn" src="https://i0.wp.com/refyn.org/wp-content/uploads/2016/06/logo-refyn-210x48.png" style="width:55px">
										</a>
									</div>
								</div>
							</div>
						</div>
					</div>
					'.$blur.'
				</div>';
		}
		function register_settings(){
			//registering refyn settings, can be obtained using "get_option( <setting_name> )"
			register_setting('refyn_settings','page_id', array('default'=>0));
			register_setting('refyn_settings','api_key', array('default'=>''));
			register_setting('refyn_settings','trigger', array('default'=>"[type='search'], #example-id, .example-class"));
			register_setting('refyn_settings','use_refyn', array('default'=>1));
			register_setting('refyn_settings','use_popup', array('default'=>1));
			register_setting('refyn_settings','refyn_page', array('default'=>1));
			register_setting('refyn_settings','page_title', array('default'=>'Refyn Search'));
			register_setting('refyn_settings','search_placeholder', array('default'=>'Search...'));
			register_setting('refyn_settings','no_found', array('default'=>"Sorry, couldn't find anything."));
			register_setting('refyn_settings','out_of_stock', array('default'=>1));
			register_setting('refyn_settings','sku_search', array('default'=>1));
			register_setting('refyn_settings','search_content', array('default'=>1));
			register_setting('refyn_settings','num_suggestions', array('default'=>5));
			register_setting('refyn_settings','num_categories', array('default'=>5));
			register_setting('refyn_settings','num_tags', array('default'=>5));
			register_setting('refyn_settings','num_products', array('default'=>10));
			register_setting('refyn_settings','num_posts', array('default'=>10));
			register_setting('refyn_settings','num_pages', array('default'=>10));
			register_setting('refyn_settings','cutoff_count', array('default'=>3));
			register_setting('refyn_settings','use_stopwords', array('default'=>1));
			register_setting('refyn_settings','use_spellcheck', array('default'=>1));
			register_setting('refyn_settings','use_ai', array('default'=>1));
			register_setting('refyn_settings','use_history', array('default'=>1));
			register_setting('refyn_settings','use_sound', array('default'=>1));
			register_setting('refyn_settings','use_scraping', array('default'=>1));
			register_setting('refyn_settings','desc_count', array('default'=>0));
			register_setting('refyn_settings','display_cats', array('default'=>0));
			register_setting('refyn_settings','display_tags', array('default'=>0));
			register_setting('refyn_settings','display_images', array('default'=>1));
			register_setting('refyn_settings','display_sku', array('default'=>0));
			register_setting('refyn_settings','display_price', array('default'=>1));
			register_setting('refyn_settings','background_color', array('default'=>'#ffffff'));
			register_setting('refyn_settings','highlight_color', array('default'=>'#547a8b'));
			register_setting('refyn_settings','primary_color', array('default'=>'#343a40'));
			register_setting('refyn_settings','second_color', array('default'=>'#007bff'));
		}
	}
}