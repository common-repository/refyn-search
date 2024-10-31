<?php
defined('ABSPATH') || exit;

if(!function_exists('Refyn_Shortcode')){
	function Refyn_Shortcode() {
		
		if (empty($_GET['s'])) return; //we need to be in a search results
				
		$items = file_get_contents(URL . '/wp-json/wp/v2/search?seach=' . $_GET['s']);
		$items = json_decode($items,true);
		
		if (count($items) > 3) return;

		$data = array('url' => esc_url( home_url( '' ) ), 'key' => get_option('api_key'), 'q' => $_GET['s']);
		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => http_build_query($data)
			)
		);
		$context  = stream_context_create($options);
		$refyn = file_get_contents("https://api.refyn.org/get_suggestions.php", false, $context);
		$refyn = json_decode($refyn,true);
		
		$final ='';
		if (!empty($refyn)){
			if (sizeof($refyn) > 1){
				echo "<div class='p-2'></div> <h3> Try Also: </h3>";
				foreach ($refyn as $value) {
					$url = home_url( '/' ).'?s='.$value.'&old_s='.$_GET['s'];
					$final .= '<a href="'.$url.'"> <button type="button" class="btn btn-dark m-1">'.$value.'</button> </a>';
				}
				/*if (isset($_GET['old_s'])){
					refyn_log($_GET['old_s'],$_GET['s']);
				}*/
				echo $final . "<div class='p-2'></div> ";
			}
		}
	}
}

if ( !function_exists('get_refyn_array')){
	function get_refyn_array($site,$key,$r){
		$data = array('url' => $site, 'key' => $key, 'r' => $r);
		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => http_build_query($data)
			)
		);
		$context  = stream_context_create($options);
		return file_get_contents("https://api.refyn.org/get_suggestions.php", false, $context);
	}	
}

if(!function_exists('Refyn_Search_Page')){
	function Refyn_Search_Page() {
		
		$r="get_api_count";
		$json_file = get_refyn_array(home_url( '' ),get_option('api_key'),$r);
		$jsonObs = json_decode($json_file,true);
		echo "<div> <h3> Total Refyn API Hits: ".number_format($jsonObs['count'])."</h3> <h4> These are the search terms... </h4> ";
		$r="history";
		$json_file = get_refyn_array(home_url( '' ),get_option('api_key'),$r);
		$jsonObs = json_decode($json_file,true);
		if (!empty($jsonObs)){
			echo "<ul style='display: grid; grid-template-columns: auto auto auto auto auto auto auto;'>";
			foreach ($jsonObs['history'] as $value)
			{	
				$url = home_url( '/' ).'?s='.$value;
				if (strcmp(strtolower($value),"refyn")==0){
					$url = "www.refyn.org";
				}
				echo '<li style="padding:10px 10px 10px 10px;"><a href="'.$url.'">'.$value.'</a></li>';
			}
			echo "</ul>";
		}
		echo "</div>";
		
	}
}