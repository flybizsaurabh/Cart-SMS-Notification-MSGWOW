<?php
//Plugin Information Varianels
$MSGWOW_sms = array( 	
	'plugin' 		=> 'MSGWOW SMS Notification Plugin for Woocommerce', 
	'plugin_uri' 	=> 'https://github.com/flyMSGWOWsaurabh/Cart-SMS-Notification-MSGWOW', 
	'donacion' 		=> '#',
	'soporte' 		=> '#',
	'plugin_url' 	=> 'https://wordpress.org/plugins/MSGWOW SMS Notification Plugin for Woocommerce', 
	'ajustes' 		=> 'admin.php?page=MSGWOW_sms', 
	'puntuacion' 	=> '#' 
);

load_plugin_textdomain( 'woocommerce-MSGWOW-sms-notifications', null, dirname( DIRECCION_MSGWOW_sms ) . '/languages' );

$MSGWOW_sms_settings = get_option( 'MSGWOW_sms_settings' );


function MSGWOW_sms_innervrbl( $enlaces, $archivo ) {
	global $MSGWOW_sms;
	return $enlaces;
}
add_filter( 'plugin_row_meta', 'MSGWOW_sms_innervrbl', 10, 2 );

function MSGWOW_sms_adjust( $enlaces ) { 
	global $MSGWOW_sms;

	$enlaces_de_ajustes = array( 
		'<a href="' . $MSGWOW_sms['ajustes'] . '" title="' . __( 'Settings of ', 'woocommerce-MSGWOW-sms-notifications' ) . $MSGWOW_sms['plugin'] .'">' . __( 'Settings', 'woocommerce-MSGWOW-sms-notifications' ) . '</a>', 
		'<a href="' . $MSGWOW_sms['soporte'] . '" title="' . __( 'Support of ', 'woocommerce-MSGWOW-sms-notifications' ) . $MSGWOW_sms['plugin'] .'">' . __( 'Support', 'woocommerce-MSGWOW-sms-notifications' ) . '</a>' 
	);
	foreach( $enlaces_de_ajustes as $enlace_de_ajustes )	{
		array_unshift( $enlaces, $enlace_de_ajustes );
	}

	return $enlaces; 
}
$plugin = DIRECCION_MSGWOW_sms; 
add_filter( "plugin_action_links_$plugin", 'MSGWOW_sms_adjust' );

//Plugin Slug Name Setting
function MSGWOW_sms_plugin( $nombre ) {
	global $MSGWOW_sms;
	
	$argumentos	= ( object ) array( 
		'slug'		=> $nombre 
	);
	$consulta	= array( 
		'action'	=> 'plugin_information', 
		'timeout'	=> 15, 
		'request'	=> serialize( $argumentos )
	);
	$respuesta	= get_transient( 'MSGWOW_sms_plugin' );
	if ( false === $respuesta ) {
		$respuesta = wp_remote_post( 'https://api.wordpress.org/plugins/info/1.0/', array( 
			'body'	=> $consulta
		) );
		set_transient( 'MSGWOW_sms_plugin', $respuesta, 24 * HOUR_IN_SECONDS );
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

	return '<a title="' . sprintf( __( 'Please, rate %s:', 'woocommerce-MSGWOW-sms-notifications' ), $MSGWOW_sms['plugin'] ) . '" href="' . $MSGWOW_sms['puntuacion'] . '?rate=5#postform" class="estrellas">' . $estrellas . '</a>';
}

function MSGWOW_sms_same() {
	global $MSGWOW_sms;
	
	echo '<div class="error fade" id="message"><h3>' . $MSGWOW_sms['plugin'] . '</h3><h4>' . sprintf( __( "Please, update your %s. It's very important!", 'woocommerce-MSGWOW-sms-notifications' ), '<a href="' . $MSGWOW_sms['ajustes'] . '" title="' . __( 'Settings', 'woocommerce-MSGWOW-sms-notifications' ) . '">' . __( 'settings', 'woocommerce-MSGWOW-sms-notifications' ) . '</a>' ) . '</h4></div>';
}
//StyleSheet Enque Functions
function MSGWOW_sms_stylesheet() {
	global $MSGWOW_sms_settings;
	wp_register_style( 'stylecss', plugins_url( 'assets/css/style.css', DIRECCION_MSGWOW_sms ) ); 
	wp_enqueue_style( 'stylecss' ); 
	wp_register_style( 'bootstrap-css', plugins_url( 'assets/css/bootstrap.min.css', DIRECCION_MSGWOW_sms ) ); 
	wp_enqueue_style( 'bootstrap-css' );
}
add_action( 'admin_init', 'MSGWOW_sms_stylesheet' );


function MSGWOW_sms_dsc() {
	delete_option( 'MSGWOW_sms_settings' );
	delete_transient( 'MSGWOW_sms_plugin' );
}
register_uninstall_hook( __FILE__, 'MSGWOW_sms_dsc' );


