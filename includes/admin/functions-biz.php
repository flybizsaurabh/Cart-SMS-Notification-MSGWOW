<?php
//Plugin Information Varianels
$biz_sms = array( 	
	'plugin' 		=> 'Cart SMS Notification(biz)', 
	'plugin_uri' 	=> 'woocommerce-biz-sms-notifications', 
	'donacion' 		=> '#',
	'soporte' 		=> '#',
	'plugin_url' 	=> '#', 
	'ajustes' 		=> 'admin.php?page=biz_sms', 
	'puntuacion' 	=> '#' 
);

load_plugin_textdomain( 'woocommerce-biz-sms-notifications', null, dirname( DIRECCION_biz_sms ) . '/languages' );

$biz_sms_settings = get_option( 'biz_sms_settings' );


function biz_sms_innervrbl( $enlaces, $archivo ) {
	global $biz_sms;
	return $enlaces;
}
add_filter( 'plugin_row_meta', 'biz_sms_innervrbl', 10, 2 );

function biz_sms_adjust( $enlaces ) { 
	global $biz_sms;

	$enlaces_de_ajustes = array( 
		'<a href="' . $biz_sms['ajustes'] . '" title="' . __( 'Settings of ', 'woocommerce-biz-sms-notifications' ) . $biz_sms['plugin'] .'">' . __( 'Settings', 'woocommerce-biz-sms-notifications' ) . '</a>', 
		'<a href="' . $biz_sms['soporte'] . '" title="' . __( 'Support of ', 'woocommerce-biz-sms-notifications' ) . $biz_sms['plugin'] .'">' . __( 'Support', 'woocommerce-biz-sms-notifications' ) . '</a>' 
	);
	foreach( $enlaces_de_ajustes as $enlace_de_ajustes )	{
		array_unshift( $enlaces, $enlace_de_ajustes );
	}

	return $enlaces; 
}
$plugin = DIRECCION_biz_sms; 
add_filter( "plugin_action_links_$plugin", 'biz_sms_adjust' );

//Plugin Slug Name Setting
function biz_sms_plugin( $nombre ) {
	global $biz_sms;
	
	$argumentos	= ( object ) array( 
		'slug'		=> $nombre 
	);
	$consulta	= array( 
		'action'	=> 'plugin_information', 
		'timeout'	=> 15, 
		'request'	=> serialize( $argumentos )
	);
	$respuesta	= get_transient( 'biz_sms_plugin' );
	if ( false === $respuesta ) {
		$respuesta = wp_remote_post( 'https://api.wordpress.org/plugins/info/1.0/', array( 
			'body'	=> $consulta
		) );
		set_transient( 'biz_sms_plugin', $respuesta, 24 * HOUR_IN_SECONDS );
	}
	if ( !is_wp_error( $respuesta ) ) {
		$plugin = get_object_vars( unserialize( $respuesta['body'] ) );
	} else {
		$plugin['rating'] = 100;
	}

	$rating = array(
	   'rating'		=> $plugin['rating'],
	   'type'		=> 'percent',
	   'number'		=> $plugin['num_ratings'],
	);
	ob_start();
	wp_star_rating( $rating );
	$estrellas = ob_get_contents();
	ob_end_clean();

	return '<a title="' . sprintf( __( 'Please, rate %s:', 'woocommerce-biz-sms-notifications' ), $biz_sms['plugin'] ) . '" href="' . $biz_sms['puntuacion'] . '?rate=5#postform" class="estrellas">' . $estrellas . '</a>';
}

function biz_sms_same() {
	global $biz_sms;
	
	echo '<div class="error fade" id="message"><h3>' . $biz_sms['plugin'] . '</h3><h4>' . sprintf( __( "Please, update your %s. It's very important!", 'woocommerce-biz-sms-notifications' ), '<a href="' . $biz_sms['ajustes'] . '" title="' . __( 'Settings', 'woocommerce-biz-sms-notifications' ) . '">' . __( 'settings', 'woocommerce-biz-sms-notifications' ) . '</a>' ) . '</h4></div>';
}
//StyleSheet Enque Functions
function biz_sms_stylesheet() {
	global $biz_sms_settings;
	wp_register_style( 'stylecss', plugins_url( 'assets/css/style.css', DIRECCION_biz_sms ) ); 
	wp_enqueue_style( 'stylecss' ); 
	wp_register_style( 'bootstrap-css', plugins_url( 'assets/css/bootstrap.min.css', DIRECCION_biz_sms ) ); 
	wp_enqueue_style( 'bootstrap-css' );
}
add_action( 'admin_init', 'biz_sms_stylesheet' );


function biz_sms_dsc() {
	delete_option( 'biz_sms_settings' );
	delete_transient( 'biz_sms_plugin' );
}
register_uninstall_hook( __FILE__, 'biz_sms_dsc' );


