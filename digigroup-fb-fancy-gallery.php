<?php
/* Plugin Name: Digigroup FB Fancy Gallery
 * Version: 0.01
 * Description: Photos of a public fan page displayed as a gallery fancybox
 * Author: Digigroup
 * Constributors:
 * Plugin URI: https://github.com/digigroup/Digigroup-Fb-Fancy-Gallery
 */
class Digigroup_Fb_Fancy_Gallery {

	public function Digigroup_Fb_Fancy_Gallery(){
		
		register_activation_hook( __FILE__, array(&$this, 'dgffg_activation') );
		add_action( 'wp_enqueue_scripts', array( &$this, 'dgffg_custom_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'dgffg_custom_styles') );
		add_action( 'wp_ajax_nopriv_more_albums', array(&$this, 'dgffg_more_albums') );
		add_action( 'wp_ajax_more_albums', array(&$this, 'dgffg_more_albums') );
		add_shortcode( 'dgfbfancygallery', array( &$this, 'dgffg_sc1_dgfbfancygallery') );
		
	}

	public function dgffg_activation(){
		// Exibir mensagem para configurar shortcode...
	}

	public function dgffg_custom_scripts(){
		
		wp_deregister_script( 'jquery' );
   		wp_register_script( 'jquery', 'http://code.jquery.com/jquery-1.8.0.min.js');
    	wp_enqueue_script( 'jquery' );
    	wp_enqueue_script( 'jquery.mousewheel-3.0.6.pack', plugins_url( 'js/jquery.mousewheel-3.0.6.pack.js', __FILE__ ), array('jquery'),null, true );
		wp_enqueue_script( 'jquery.fancybox', plugins_url( 'js/fancybox/jquery.fancybox.js', __FILE__ ), array('jquery'),null, true );    	
		wp_enqueue_script( 'jquery.fancybox-buttons', plugins_url( 'js/fancybox/helpers/jquery.fancybox-buttons.js', __FILE__ ), array('jquery'),null, true );
    	wp_enqueue_script( 'jquery.fancybox-thumbs', plugins_url( 'js/fancybox/helpers/jquery.fancybox-thumbs.js', __FILE__ ), array('jquery'),null, true );
    	wp_enqueue_script( 'jquery.fancybox-media', plugins_url( 'js/fancybox/helpers/jquery.fancybox-media.js', __FILE__ ), array('jquery'),null, true );
    	wp_enqueue_script( 'digigroup-fb-fancy-gallery', plugins_url( 'js/digigroup-fb-fancy-gallery.min.js', __FILE__ ), array('jquery'),null, true );
    	wp_localize_script( 'digigroup-fb-fancy-gallery', 'digigroup_fb_fancy_gallery', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) ); 

	}

	public function dgffg_custom_styles(){
		
		wp_register_style( 'jquery.fancybox', plugins_url('js/fancybox/jquery.fancybox.css', __FILE__) );
		wp_register_style( 'jquery.fancybox-buttons', plugins_url('js/fancybox/helpers/jquery.fancybox-buttons.css', __FILE__) );
		wp_register_style( 'jquery.fancybox-thumbs', plugins_url('js/fancybox/helpers/jquery.fancybox-thumbs.css', __FILE__) );
		wp_register_style( 'digigroup-fb-fancy-gallery', plugins_url('css/digigroup-fb-fancy-gallery.min.css', __FILE__) );
        wp_enqueue_style( array('jquery.fancybox', 'jquery.fancybox-buttons', 'jquery.fancybox-thumbs',  'digigroup-fb-fancy-gallery') );

	}

	public function dgffg_more_albums(){

		//$format_cover_image =  'https://graph.facebook.com/%s/?fields=picture';
		$format_cover_image =  'https://graph.facebook.com/%s';
		$result = wp_remote_retrieve_body( wp_remote_get($_GET['next']));
		$width_thumbs = intval($_GET['width_thumbs']);
		$result_as_array = json_decode($result, true);
		
		/*@todo: Optimize */
		foreach ( $result_as_array['data'] as $i => $value ) {	
			$result_cover_image = wp_remote_retrieve_body( wp_remote_get(sprintf($format_cover_image, $value['cover_photo'])));
			$result_cover_image_as_array = json_decode($result_cover_image, true);
			$result_as_array['data'][$i]['cover_photo'] = $result_cover_image_as_array['images'][$width_thumbs]['source'];
		}
		wp_die(json_encode($result_as_array));
	}
	
	public function dgffg_init(){}

	public function dgffg_sc1_dgfbfancygallery( $atts, $content = null ){
		
		extract( shortcode_atts( array( 
			'albums_per_page' =>  '8',
			'show_all_albums' => true,
			'show_album_limit' => 0,
			'fanpage_id' => '110387505694717',
			'gallery_container' => 'ul',
			'gallery_container_class' => '',
			'album_item_tag' => 'li',
			'album_name_tag' => 'span',
			'hide_wall_photos_album' => true,
			'width_thumbs' => 6
		), $atts ) );

		$format_albums = 'https://graph.facebook.com/%s/albums?limit=%s&fields=id,from,name,cover_photo,count,type';
		//$format_cover_image =  'https://graph.facebook.com/%s/?fields=picture';
		$format_cover_image =  'https://graph.facebook.com/%s';
		$html_output = '<div id="dgffg_details_album"></div>';
		$html_output .= '<div id="dgffg_main_container">';
		$request = wp_remote_retrieve_body( wp_remote_get(sprintf($format_albums, $fanpage_id, $albums_per_page)));
		$result_as_array = json_decode($request, true);
		
		foreach ( $result_as_array['data'] as $i => $value ) {	
			$result_cover_image	=  wp_remote_retrieve_body( wp_remote_get(sprintf($format_cover_image, $value['cover_photo'])));
			$result_cover_image_as_array = json_decode($result_cover_image, true);
			$result_as_array['data'][$i]['cover_photo'] = $result_cover_image_as_array['images'][$width_thumbs]['source'];	
		}
		
		$html_output .= sprintf(
			__('<h1>Álbuns de %s</h1>', 'dgffg_plugin_textdomain'),
			$result_as_array['data'][0]['from']['name']
		);

		$html_output .= '<input type="hidden" name="dgffg_albums_per_page" id="dgffg_albums_per_page" value="'.$albums_per_page.'" />';
		$html_output .= '<input type="hidden" name="dgffg_gallery_container" id="dgffg_gallery_container" value="'.$gallery_container.'" />';
		$html_output .= '<input type="hidden" name="dgffg_gallery_container_class" id="dgffg_gallery_container_class" value="'.$gallery_container_class.'" />';
		$html_output .= '<input type="hidden" name="dgffg_album_item_tag" id="dgffg_album_item_tag" value="'.$album_item_tag.'" />';
		$html_output .= '<input type="hidden" name="dgffg_album_name_tag" id="dgffg_album_name_tag" value="'.$album_name_tag.'" />';		
		$html_output .= '<input type="hidden" name="dgffg_width_thumbs" id="dgffg_width_thumbs" value="'.$width_thumbs.'" />';		

		$html_output .= '<'. $gallery_container ;
		if( !empty( $gallery_container_class ) )
			$html_output .= ' class="'.$gallery_container_class.'"';
		$html_output .= ' id="dgffg_gallery_container_items">';

		foreach( $result_as_array['data'] as $item ) {
			
			if( isset($item['count']) && $item['type'] === "normal" ){
				$html_output .= '<'. $album_item_tag .'>';
				$html_output .= '<div class="dgffg_picture_container">'; // Controlar shortcode...
				$html_output .= '<img src="'. $item['cover_photo']. '"/>';
				$html_output .= '</div>'; // controlar no shortcode ...
				$html_output .= '<a href="https://graph.facebook.com/'.$item['id'].'/photos" class="dgffg_album_link">'.$item['name'].'</a>';
				$html_output .= '</'. $album_item_tag .'>';		
			}
				
		}

		$html_output .= '</'.$gallery_container. '>' ;

		if( isset( $result_as_array['paging']['next'] ) ){
			$loading_src = plugins_url('img/loading.gif', __FILE__);
			$html_output .= '<a class="dgffg_more_albums" href="'.$result_as_array['paging']['next'].'">Mais Álbuns</a>';
			$html_output .= '<div class="dgffg_loading" style="display:none;"><img src="'.$loading_src.'" alt="Carregando" /></div> ';
		}
		$html_output .= "</div>";
		
		return $html_output;
	}
}
new Digigroup_Fb_Fancy_Gallery;
