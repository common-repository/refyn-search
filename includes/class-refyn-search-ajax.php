<?php
defined('ABSPATH') || exit;

if(!function_exists('Refyn_Ajax_Search')){
	function Refyn_Ajax_Search() {
		
		//we need search field

		if (!isset($_GET['search'])){ echo json_encode([]); die(); }
		
		$s = sanitize_text_field( $_GET['search'] );

		$data = array('url' => esc_url( home_url( '' ) ), 'key' => get_option('api_key'), 'q' => $s, 'r' => 'log');
		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => http_build_query($data)
			)
		);
		$context  = stream_context_create($options);
		$refyn = file_get_contents("https://api.refyn.org/get_suggestions.php", false, $context); //this is just to log the data
		
		//search value must be bigger or equal to 3 characters
		
		if (strlen($s) < 3){ echo json_encode([]); die(); }
				
		$output = array();
		
		//getting tags
		
		if (get_option('num_tags') > 0){
			$tags_out = array();
			$tags = get_tags();
			$max_tags = get_option('num_tags');
			$total_tags = 0;
			foreach($tags as $tag) {
				if (strpos(strtolower($tag->name),strtolower($s)) !== false) {

					$total_tags++;
					
					$tags_out[] = array(
						'title' => $tag->name,
						'permalink' => get_tag_link($tag->term_id)
					);
					
					if ($total_tags == $max_tags) break;
				}
			}

			if (!empty($tags_out)){
				$output[] = array(
					'category'  => 'Tags',
					'value' 	=> $tags_out,
				);
			}
		}
		
		//getting categories
		
		if (get_option('num_categories') > 0){
			$categories_out =array();
			$cats = get_categories();
			$pro_cats = get_terms(array( 'taxonomy' => 'product_cat' ));
			$categories = (is_array($cats) && is_array($pro_cats)) ? array_merge( $cats, $pro_cats ) : (is_array($cats) ? $cats : (is_array($pro_cats) ? $pro_cats : [] )) ;
			$max_cat = get_option('num_categories');
			$total_cat = 0;
			foreach($categories as $category) {
				if (strpos(strtolower($category->name),strtolower($s)) !== false) {

					$total_cat++;
					
					$categories_out[] = array(
						'title' => $category->name,
						'permalink' => get_category_link($category->term_id)
					);
					
					if ($total_cat == $max_cat) break;
				}
			}

			if (!empty($categories_out)){
				$output[] = array(
					'category'  => 'Categories',
					'value' 	=> $categories_out,
				);
			}
		}
		
		$args = array(
					's'				 => (get_option('sku_search') and is_numeric($s)) ? '' : $s,
					'posts_per_page' => get_option('num_products'),
					'no_found_rows'  => 1,
					'post_status'    => 'publish',
					'post_type'      => 'product',
					'meta_query' 	 => array(),
				);

		if (get_option('sku_search') and is_numeric($s)){
			$args['meta_query'][] =
			  array(
				'key' => '_sku',
				'value' => $s,
				'compare' => 'LIKE'
			  );
		}
		
		//getting product results
		if (get_option('num_products') > 0){
			$products = get_results($args,true);	
			if (!empty($products)){
				$output[] = array(
					'category'  => 'Products',
					'value' 	=> $products,
				);
			}
		}
		
		//getting posts results
		
		if (get_option('num_posts') > 0){
			
			$args['post_type'] = 'post';
			$posts = get_results($args);
			
			if (!empty($posts)){
				$output[] = array(
					'category'  => 'Posts',
					'value' 	=> $posts,
				);
			}
		}

		//getting pages results
		
		if (get_option('num_pages') > 0){
			$args['post_type'] = 'page';
			$pages = get_results($args);
			
			if (!empty($pages)){
				$output[] = array(
					'category'  => 'Pages',
					'value' 	=> $pages,
				);
			}
		}
		
		echo json_encode($output);
		die();
	}
}

if(!function_exists('get_results')){
	function get_results($args, $product = false) {
		
		//results returned for each category call
		
		$factory = null;
		if (class_exists('WC_Product_Factory')){
			$factory = new WC_Product_Factory();
		}

		if (!get_option('out_of_stock') and $product){
			$args['meta_query'][] =
			  array(
				'key' => '_stock_status',
				'value' => 'outofstock',
				'compare' => 'NOT IN'
			  );
		}
		
		$output = array();
		$results = new WP_Query( $args );
		
		while ( $results->have_posts() ) {
			
			$results->the_post();
			
			$title = html_entity_decode( get_the_title() );
			$content = get_post_field('post_content', $recent["ID"]);
			$content = strip_tags($content); //post content
			$add_to_output = true;
			
			if (!get_option('search_content')){
				//ignore results that came from post content
				$s = strtolower($args['s']);
				if ( strpos($content,$s) and !strpos(strtolower($title),$s) )
					$add_to_output = false;
			}
			
			if ($add_to_output){
				
				//additional information to attach for each result
				$display_cats = '';
				$display_tags = '';
				$display_sku = '';
				
				$cats = get_the_category($results->ID);
				if (get_option('display_cats') && !empty($cats)){
					$display_cats = '<div class="row"><span class="font-weight-bold pr-1">Categories:</span>';
					foreach ( $cats as $cat ) {
						$cat_link = get_category_link( $cat->term_id ); 
						$display_cats .= "<a href='{$cat_link}' title='{$cat->name} Tag' class='{$cat->slug}'>";
						$display_cats .= "{$cat->name}</a>&nbsp;";
					}
					$display_cats .= '</div>';
				}
				
				$tags = get_the_tags($results->ID);
				if (get_option('display_tags') && !empty($tags)){
					$display_tags = '<div class="row"><span class="font-weight-bold pr-1"> Tags: </span>';
					foreach ( $tags as $tag ) {
						$tag_link = get_tag_link( $tag->term_id ); 
						$display_tags .= "<a href='{$tag_link}' title='{$tag->name} Tag' class='{$tag->slug}'>";
						$display_tags .= "{$tag->name}</a>&nbsp;";
					}
					$display_tags .= '</div>';
				}
				
				$sku = ($factory!=null && $product) ? $factory->get_product( get_the_ID() )->get_sku() : array();
				if (get_option('display_sku') && !empty($sku)){
					$display_cats = '<div class="row"> <span class="font-weight-bold pr-1"> SKU: </span>'.$sku.'</div>';
				}
				$display = '<div class="pt-1">'.$display_sku.$display_cats.$display_tags.'</div>';
							
				$output[] = array(
						'title' => $title,
						'permalink' => get_the_permalink(),
						'thumbnail' => (get_option('display_images')) ? get_the_post_thumbnail( null, array(75,75), '' ) : '',
						'value'		=> ($factory!=null && $product && get_option('display_price')) ? $factory->get_product( get_the_ID() )->get_price_html() : '',
						'display'	=> array("html" => $display, "content" => (get_option('desc_count') > 0) ? substr($content,0, get_option('desc_count'))." ..." : ''),
					);
			}
		}
		return $output;
	}
}