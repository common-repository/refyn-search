<style>
	#nav button{
		border-style:none;
		font-size:18px;
		font-weight: 500;
		background-color: transparent;
		width: 10%;
	}
	#nav button:hover{
		font-weight: 700;
	}
	.slidecontainer {
		width: 100%;
	}
</style>

<script>
	function navigate(name){
		let table = document.getElementById("nav_table").children;
		Object.keys(table).forEach(function(child){
			child = table[child];
			child.style.display = "none";
			if (child.id == name.id) 
				child.style.display = "block";
		});
	}
</script>

<div class="wrap">
	<?php
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
	?>
	<?php if (empty(get_option('api_key'))){ ?>
	<div class="pdate-nag notice notice-warning inline" style="padding:12px">Refyn Search missing API key, please update you API key to use Refyn Search!</div>
	<?php } elseif (!json_decode(file_get_contents("https://api.refyn.org/get_suggestions.php", false, $context),true)["ok"]){ ?>
	<div class="notice notice-error is-dismissible" style="padding:12px">Your API key is not found in REFYN database, please put a working API key!</div>
	<?php } if (isset($_POST)){ ?>
	<div class=" notice notice-success is-dismissible" style="padding:12px">You successfully updated REFYN settings!</div>
	<?php } ?>
</div>

<div class="wrap">
	<p>
		<a href="https://www.refyn.org">
			<img alt="Refyn" src="https://i0.wp.com/refyn.org/wp-content/uploads/2016/06/logo-refyn-210x48.png" style="width:155px">
		</a>
	</p> <br>
	<div>
		<span> REFYN is a new Artificial Intelligence (AI) algorithm that can search a site using only features. </span> <br>
		<span style="font-weight:700;"> No keywords, no training, and no index are required! </span> <br> 
		<p> E.g. Query of "4x4" will fetch jeep and SUV even if "4x4" is not in DB at all. It will fetch all images with a certain shape, color, and so on without scanning pixels - Just by cognitively understanding that toilet paper has a shape of a cylinder. </p>
		<p> For more information and settings for your account, login to 
			<a href="https://dash.refyn.org"> https://dash.refyn.org </a> 
			<br> To get your API key, sign up at
			<a href="https://refyn.org/api/"> https://refyn.org/api/ </a> 
		</p>
	</div>
	<br> <hr>
	<div id="nav">
		<button onclick="navigate(main_settings)" > General </button>
		<button onclick="navigate(search_settings)" > Search </button>
		<button onclick="navigate(ai_settings)" > AI </button>
		<button onclick="navigate(results_settings)" > Results </button>
		<button onclick="navigate(style_settings)" > Style </button>
		<button onclick="navigate(shortcodes_settings)" > Shortcodes </button>
	</div>
	<hr>
	<form method="post" action="options.php">
		<?php
			$data = array('url' => esc_url( home_url( '' ) ), 'key' => get_option('api_key'), 'r' => 'member');
			$options = array(
				'http' => array(
					'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
					'method'  => 'POST',
					'content' => http_build_query($data)
				)
			);
			$context  = stream_context_create($options);
			$member = json_decode(file_get_contents("https://api.refyn.org/get_suggestions.php", false, $context),true)["ok"];
			settings_fields( 'refyn_settings' );
			do_settings_sections( 'refyn_settings' );
		?>
		<table id="nav_table" class="form-table" role="presentation">
		
			<tbody id="main_settings">
				<tr>
					<th scope="row"><label for="api_key">API KEY</label></th>
					<td><input name="api_key" type="text" id="api_key" value="<?php echo get_option('api_key'); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row"><label for="trigger">CSS Trigger</label></th>
					<td>
						<input name="trigger" type="text" id="trigger" value="<?php echo get_option('trigger'); ?>" class="regular-text">
						<p>This is the CSS that will trigger the Refyn Search plugin.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="use_refyn">Use Refyn Search</label></th>
					<td><input name="use_refyn" type="checkbox" id="use_refyn" value="1" <?php checked(get_option('use_refyn')); ?>></td>
				</tr>
				<tr>
					<th scope="row"><label for="use_popup">Display As A Pop-Up</label></th>
					<td><input name="use_popup" type="checkbox" id="use_popup" value="1" <?php checked(get_option('use_popup')); ?>></td>
				</tr>
				<tr>
					<th scope="row"><label for="refyn_page">Install Refyn Page</label></th>
					<td><input name="refyn_page" type="checkbox" id="refyn_page" value="1" <?php checked(get_option('refyn_page')); ?>></td>
				</tr>
				<tr>
					<th scope="row"><label for="page_title">Refyn Page Link</label></th>
					<td><input name="page_title" type="text" id="page_title" value="<?php echo get_option('page_title'); ?>" class="regular-text"></td>
				</tr>
			</tbody>

			<tbody id="search_settings" style="display:none;">
				<tr>
					<th scope="row"><label for="search_placeholder">Text Placeholder</label></th>
					<td><input name="search_placeholder" type="text" id="search_placeholder" value="<?php echo get_option('search_placeholder'); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row"><label for="no_found">Nothing Found Error Message</label></th>
					<td><input name="no_found" type="text" id="no_found" value="<?php echo get_option('no_found'); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row"><label for="out_of_stock">Include Out Of Stock</label></th>
					<td><input name="out_of_stock" type="checkbox" id="out_of_stock" value="1" <?php checked(get_option('out_of_stock')); ?>></td>
				</tr>
				<tr>
					<th scope="row"><label for="sku_search">Enable SKU Search</label></th>
					<td><input name="sku_search" type="checkbox" id="sku_search" value="1" <?php checked(get_option('sku_search')); ?>></td>
				</tr>
				<tr>
					<th scope="row"><label for="search_content">Search In Post Content</label></th>
					<td><input name="search_content" type="checkbox" id="search_content" value="1" <?php checked(get_option('search_content')); ?>></td>
				</tr>
				<tr>
					<th scope="row"><label for="num_suggestions">Number Of Suggestions</label></th>
					<td>
						<div class="slidecontainer"><input name="num_suggestions" type="range" min="0" max="20" value="<?php echo get_option('num_suggestions'); ?>"></div>
						<p>Number of Refyn AI suggestions if nothing found.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="num_categories">Number Of Categories</label></th>
					<td><div class="slidecontainer"><input name="num_categories" type="range" min="0" max="20" value="<?php echo get_option('num_categories'); ?>"></div></td>
				</tr>
				<tr>
					<th scope="row"><label for="num_tags">Number Of Tags</label></th>
					<td><div class="slidecontainer"><input name="num_tags" type="range" min="0" max="20" value="<?php echo get_option('num_tags'); ?>"></div></td>
				</tr>
				<tr>
					<th scope="row"><label for="num_products">Number Of Products</label></th>
					<td><div class="slidecontainer"><input name="num_products" type="range" min="0" max="20" value="<?php echo get_option('num_products'); ?>"></div></td>
				</tr>
				<tr>
					<th scope="row"><label for="num_posts">Number Of Posts</label></th>
					<td><div class="slidecontainer"><input name="num_posts" type="range" min="0" max="20" value="<?php echo get_option('num_posts'); ?>"></div></td>
				</tr>
				<tr>
					<th scope="row"><label for="num_page">Number Of Pages</label></th>
					<td><div class="slidecontainer"><input name="num_pages" type="range" min="0" max="20" value="<?php echo get_option('num_pages'); ?>"></div></td>
				</tr>
			</tbody>
		
			<tbody id="ai_settings" style="display:none;">
				<tr>
					<th scope="row"><label for="cutoff_count">Cutoff Character Count</label></th>
					<td>
						<div class="slidecontainer"><input name="cutoff_count" type="range" min="0" max="20" value="<?php echo get_option('cutoff_count'); ?>"></div>
						<p>If the AI can't find anything, it will start removing characters from the text until it found something.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="use_stopwords">Enable Stopwords</label></th>
					<td>
						<input name="use_stopwords" type="checkbox" id="use_stopwords" value="1" <?php checked(get_option('use_stopwords')); ?>>
						<p>Enabling stopwords will allow filtering out unimportant text, you can edit them at </a href="dash.refyn.org">dash.refyn.org</a>.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="use_spellcheck">Enable Spellcheck</label></th>
					<td>
						<input <?php if (!$member) { echo "disabled = true"; } ?> name="use_spellcheck" type="checkbox" id="use_spellcheck" value="1" <?php if ($member) { checked(get_option('use_spellcheck')); } ?>>
						<p>Enabling spellcheck will allow the AI to automatically correct misspelled words. <b>This feature is only unlocked for premium users only.</b></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="use_sound">Enable Sound Like</label></th>
					<td>
						<input name="use_sound" type="checkbox" id="use_sound" value="1" <?php checked(get_option('use_sound')); ?>>
						<p>Enabling Sound Like will allow the AI to find words that sound similar to what the user is trying to find.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="use_ai">Enable AI Search</label></th>
					<td><input name="use_ai" type="checkbox" id="use_ai" value="1" <?php checked(get_option('use_ai')); ?>></td>
				</tr>
				<tr>
					<th scope="row"><label for="use_history">Enable History Search</label></th>
					<td>
						<input name="use_history" type="checkbox" id="use_history" value="1" <?php checked(get_option('use_history')); ?>>
						<p>Enabling History Search will allow the AI to find suggestions based on previous user searches, you can edit them at </a href="dash.refyn.org">dash.refyn.org</a>.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="use_scraping">Enable Google Scraping</label></th>
					<td>
						<input  <?php if (!$member) { echo "disabled = true"; } ?> name="use_scraping" type="checkbox" id="use_scraping" value="1" <?php if ($member) { checked(get_option('use_scraping')); } ?>>
						<p>Enabling Google Scrap will allow the AI to find solutions from Google. <b> This feature is only unlocked for premire users only. </b></p>
					</td>
				</tr>
			</tbody>
		
			<tbody id="results_settings" style="display:none;">
				<tr>
					<th scope="row"><label for="desc_count">Description Character Count</label></th>
					<td><div class="slidecontainer"><input name="desc_count" type="range" min="0" max="200" value="<?php echo get_option('desc_count'); ?>"></div></td>
				</tr>
				<tr>
					<th scope="row"><label for="display_cats">Display Product Categories</label></th>
					<td><input name="display_cats" type="checkbox" id="display_cats" value="1" <?php checked(get_option('display_cats')); ?>></td>
				</tr>
				<tr>
					<th scope="row"><label for="display_tags">Display Product Tags</label></th>
					<td><input name="display_tags" type="checkbox" id="display_tags" value="1" <?php checked(get_option('display_tags')); ?>></td>
				</tr>
				<tr>
					<th scope="row"><label for="display_images">Display Images</label></th>
					<td><input name="display_images" type="checkbox" id="display_images" value="1" <?php checked(get_option('display_images')); ?>></td>
				</tr>
				<tr>
					<th scope="row"><label for="display_sku">Display SKU</label></th>
					<td><input name="display_sku" type="checkbox" id="display_sku" value="1" <?php checked(get_option('display_sku')); ?>></td>
				</tr>
				<tr>
					<th scope="row"><label for="display_price">Display Price</label></th>
					<td><input name="display_price" type="checkbox" id="display_price" value="1" <?php checked(get_option('display_price')); ?>></td>
				</tr>
			</tbody>

			<tbody id="style_settings" style="display:none;">
				<tr>
					<th scope="row"><label for="background_color">Background Color</label></th>
					<td><input type="color" id="background_color" name="background_color" value="<?php echo get_option('background_color'); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="highlight_color">Highlight Color</label></th>
					<td><input type="color" id="highlight_color" name="highlight_color" value="<?php echo get_option('highlight_color'); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="primary_color">Primary Color</label></th>
					<td><input type="color" id="primary_color" name="primary_color" value="<?php echo get_option('primary_color'); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="second_color">Secondary Color</label></th>
					<td><input type="color" id="second_color" name="second_color" value="<?php echo get_option('second_color'); ?>"></td>
				</tr>
			</tbody>

			<tbody id="shortcodes_settings" style="display:none;">
				<tr>
					<th style="width:100%">
						<h4> [refyn-search]: </h4>
						<p>
						This shortcode will allow you to display Refyn suggestions for the user on the page, it can only be triggered on search pages
						</p>
						Example: <a href='<?php echo esc_url( home_url( '' ) ); ?>/?s=4x4'><?php echo esc_url( home_url( '' ) ); ?>?s=4x4</a>
					</th>
				</tr>
			</tbody>
			
		</table>
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
			<!--input type="submit" name="reset" id="reset" class="button button-primary" value="Reset Settings"-->
		</p>
	</form>
</div>