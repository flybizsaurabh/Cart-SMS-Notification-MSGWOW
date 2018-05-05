<?php
global $MSGWOW_sms_settings, $wpml_activo;

//Control 
$tab = 1;

//WPML
if ( function_exists( 'icl_register_string' ) || !$wpml_activo ) { //Versión 
	$mensaje_pedido		= ( $wpml_activo ) ? icl_translate( 'MSGWOW_sms', 'mensaje_pedido', $MSGWOW_sms_settings['mensaje_pedido'] ) : $MSGWOW_sms_settings['mensaje_pedido'];
	$mensaje_recibido	= ( $wpml_activo ) ? icl_translate( 'MSGWOW_sms', 'mensaje_recibido', $MSGWOW_sms_settings['mensaje_recibido'] ) : $MSGWOW_sms_settings['mensaje_recibido'];
	$mensaje_procesando	= ( $wpml_activo ) ? icl_translate( 'MSGWOW_sms', 'mensaje_procesando', $MSGWOW_sms_settings['mensaje_procesando'] ) : $MSGWOW_sms_settings['mensaje_procesando'];
	$mensaje_completado	= ( $wpml_activo ) ? icl_translate( 'MSGWOW_sms', 'mensaje_completado', $MSGWOW_sms_settings['mensaje_completado'] ) : $MSGWOW_sms_settings['mensaje_completado'];
	$mensaje_nota		= ( $wpml_activo ) ? icl_translate( 'MSGWOW_sms', 'mensaje_nota', $MSGWOW_sms_settings['mensaje_nota'] ) : $MSGWOW_sms_settings['mensaje_nota'];
} else if ( $wpml_activo ) { 
	$mensaje_pedido		= apply_filters( 'wpml_translate_single_string', $MSGWOW_sms_settings['mensaje_pedido'], 'MSGWOW_sms', 'mensaje_pedido' );
	$mensaje_recibido	= apply_filters( 'wpml_translate_single_string', $MSGWOW_sms_settings['mensaje_recibido'], 'MSGWOW_sms', 'mensaje_recibido' );
	$mensaje_procesando	= apply_filters( 'wpml_translate_single_string', $MSGWOW_sms_settings['mensaje_procesando'], 'MSGWOW_sms', 'mensaje_procesando' );
	$mensaje_completado	= apply_filters( 'wpml_translate_single_string', $MSGWOW_sms_settings['mensaje_completado'], 'MSGWOW_sms', 'mensaje_completado' );
	$mensaje_nota		= apply_filters( 'wpml_translate_single_string', $MSGWOW_sms_settings['mensaje_nota'], 'MSGWOW_sms', 'mensaje_nota' );
}


$listado_de_proveedores = array(
	"msg91" 			=> "MSG91", 
	"msgwow"			=> "MSGWOW",
);
asort( $listado_de_proveedores, SORT_NATURAL | SORT_FLAG_CASE ); 


$campos_de_proveedores = array( 
    "msg91" 			=> array( 
		"clave_msg91" 						=> __( 'authentication key', 'woocommerce-MSGWOW-sms-notifications' ),
		"identificador_msg91" 				=> __( 'sender ID', 'woocommerce-MSGWOW-sms-notifications' ),
		"ruta_msg91" 						=> __( 'route', 'woocommerce-MSGWOW-sms-notifications' ),
	),

	"msgwow" 			=> array( 
		"clave_msgwow"						=> __( 'key', 'woocommerce-MSGWOW-sms-notifications' ),
		"identificador_msgwow"				=> __( 'sender ID', 'woocommerce-MSGWOW-sms-notifications' ),
		"ruta_msgwow" 						=> __( 'route', 'woocommerce-MSGWOW-sms-notifications' ),
		"servidor_msgwow"					=> __( 'host', 'woocommerce-MSGWOW-sms-notifications' ),
	),
);

$opciones_de_proveedores = array(
		"ruta_msg91"		=> array(
		"default"				=> __( 'Default', 'woocommerce-MSGWOW-sms-notifications' ), 
		1						=> 1, 
		4						=> 4,
	),
	"ruta_msgwow"		=> array(
		1						=> 1, 
		4						=> 4,
	),
	"servidor_msgwow"	=> array(
		"0"						=> __( 'International', 'woocommerce-MSGWOW-sms-notifications' ), 
		"1"						=> __( 'USA', 'woocommerce-MSGWOW-sms-notifications' ), 
		"91"					=> __( 'India', 'woocommerce-MSGWOW-sms-notifications' ), 
	),
);


$listado_de_estados				= wc_get_order_statuses();
$listado_de_estados_temporal	= array();
$estados_originales				= array( 
	'pending',
	'failed',
	'on-hold',
	'processing',
	'completed',
	'refunded',
	'cancelled',
);
foreach( $listado_de_estados as $clave => $estado ) {
	$nombre_de_estado = str_replace( "wc-", "", $clave );
	if ( !in_array( $nombre_de_estado, $estados_originales ) ) {
		$listado_de_estados_temporal[$estado] = $nombre_de_estado;
	}
}
$listado_de_estados = $listado_de_estados_temporal;


$listado_de_mensajes = array(
	'todos'					=> __( 'All messages', 'woocommerce-MSGWOW-sms-notifications' ),
	'mensaje_pedido'		=> __( 'Owner custom message', 'woocommerce-MSGWOW-sms-notifications' ),
	'mensaje_recibido'		=> __( 'Order on-hold custom message', 'woocommerce-MSGWOW-sms-notifications' ),
	'mensaje_procesando'	=> __( 'Order processing custom message', 'woocommerce-MSGWOW-sms-notifications' ),
	'mensaje_completado'	=> __( 'Order completed custom message', 'woocommerce-MSGWOW-sms-notifications' ),
	'mensaje_nota'			=> __( 'Notes custom message', 'woocommerce-MSGWOW-sms-notifications' ),
);

//SMS Service Setting
function MSGWOW_sms_providers_setting( $listado_de_proveedores ) {
	global $MSGWOW_sms_settings;
	
	foreach ( $listado_de_proveedores as $valor => $proveedor ) {
		$chequea = ( isset( $MSGWOW_sms_settings['servicio'] ) && $MSGWOW_sms_settings['servicio'] == $valor ) ? ' selected="selected"' : '';
		echo '<option value="' . $valor . '"' . $chequea . '>' . $proveedor . '</option>' . PHP_EOL;
	}
}

//Function for MSGWOW SMS Service Providers
function MSGWOW_sms_provider_cmp( $listado_de_proveedores, $campos_de_proveedores, $opciones_de_proveedores ) {
	global $MSGWOW_sms_settings, $tab;
	
	foreach ( $listado_de_proveedores as $valor => $proveedor ) {
		foreach ( $campos_de_proveedores[$valor] as $valor_campo => $campo ) {
			if ( array_key_exists( $valor_campo, $opciones_de_proveedores ) ) { 
				echo '
  <tr valign="top" class="' . $valor . '"><!-- ' . $proveedor . ' -->
	<th scope="row" class="titledesc"> <label for="MSGWOW_sms_settings[' . $valor_campo . ']">' .ucfirst( $campo ) . ':' . '</label>
	  <span class="woocommerce-help-tip" data-tip="' . sprintf( __( 'The %s for your account in %s', 'woocommerce-MSGWOW-sms-notifications' ), $campo, $proveedor ) . '" /> </th>
	<td class="forminp forminp-number"><select class="wc-enhanced-select" id="MSGWOW_sms_settings[' . $valor_campo . ']" name="MSGWOW_sms_settings[' . $valor_campo . ']" tabindex="' . $tab++ . '">
				';
				foreach ( $opciones_de_proveedores[$valor_campo] as $valor_opcion => $opcion ) {
					$chequea = ( isset( $MSGWOW_sms_settings[$valor_campo] ) && $MSGWOW_sms_settings[$valor_campo] == $valor_opcion ) ? ' selected="selected"' : '';
					echo '<option value="' . $valor_opcion . '"' . $chequea . '>' . $opcion . '</option>' . PHP_EOL;
				}
				echo '          </select></td>
  </tr>
				';
			} else { 
				echo '
  <tr valign="top" class="' . $valor . '"><!-- ' . $proveedor . ' -->
	<th scope="row" class="titledesc"> <label for="MSGWOW_sms_settings[' . $valor_campo . ']">' . ucfirst( $campo ) . ':' . '</label>
	  <span class="woocommerce-help-tip" data-tip="' . sprintf( __( 'The %s for your account in %s', 'woocommerce-MSGWOW-sms-notifications' ), $campo, $proveedor ) . '" /> </th>
	<td class="forminp forminp-number"><input type="text" id="MSGWOW_sms_settings[' . $valor_campo . ']" name="MSGWOW_sms_settings[' . $valor_campo . ']" size="50" value="' . ( isset( $MSGWOW_sms_settings[$valor_campo] ) ? $MSGWOW_sms_settings[$valor_campo] : '' ) . '" tabindex="' . $tab++ . '" /></td>
  </tr>
				';
			}
		}
	}
}

//country 
function MSGWOW_sms_country_st() {
	global $MSGWOW_sms_settings;

	$pais					= new WC_Countries();
	$campos					= $pais->get_address_fields( $pais->get_base_country(), 'shipping_' ); 
	$campos_personalizados	= apply_filters( 'woocommerce_checkout_fields', array() );
	if ( isset( $campos_personalizados['shipping'] ) ) {
		$campos += $campos_personalizados['shipping'];
	}
	foreach ( $campos as $valor => $campo ) {
		$chequea = ( isset( $MSGWOW_sms_settings['campo_envio'] ) && $MSGWOW_sms_settings['campo_envio'] == $valor ) ? ' selected="selected"' : '';
		if ( isset( $campo['label'] ) ) {
			echo '<option value="' . $valor . '"' . $chequea . '>' . $campo['label'] . '</option>' . PHP_EOL;
		}
	}
}


function MSGWOW_sms_list_no( $listado_de_estados ) {
	global $MSGWOW_sms_settings;

	foreach( $listado_de_estados as $nombre_de_estado => $estado ) {
		$chequea = '';
		if ( isset( $MSGWOW_sms_settings['estados_personalizados'] ) ) {
			foreach ( $MSGWOW_sms_settings['estados_personalizados'] as $estado_personalizado ) {
				if ( $estado_personalizado == $estado ) {
					$chequea = ' selected="selected"';
				}
			}
		}
		echo '<option value="' . $estado . '"' . $chequea . '>' . $nombre_de_estado . '</option>' . PHP_EOL;
	}
}


function MSGWOW_sms_msg_list_op( $listado_de_mensajes ) {
	global $MSGWOW_sms_settings;
	
	$chequeado = false;
	foreach ( $listado_de_mensajes as $valor => $mensaje ) {
		if ( isset( $MSGWOW_sms_settings['mensajes'] ) && in_array( $valor, $MSGWOW_sms_settings['mensajes'] ) ) {
			$chequea	= ' selected="selected"';
			$chequeado	= true;
		} else {
			$chequea	= '';
		}
		$texto = ( !isset( $MSGWOW_sms_settings['mensajes'] ) && $valor == 'todos' && !$chequeado ) ? ' selected="selected"' : '';
		echo '<option value="' . $valor . '"' . $chequea . $texto . '>' . $mensaje . '</option>' . PHP_EOL;
	}
}
