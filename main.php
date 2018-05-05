<?php
/*
Plugin Name: MSGWOW SMS Notification Plugin for Woocommerce
Plugin URI: https://github.com/flybizsaurabh/Cart-SMS-Notification-MSGWOW
Description:  MSGWOW SMS Notification Plugin is Specially for eCommerce Stores hosted with WooCommerce. Where users and admin both get SMS Notifications for New Order, Order Status Change, Login OTP Verification, with 20+ Notifications triggers. 
Version: 1.0
Author: MSGWOW
Author URI: www.msgwow.com
Text Domain: www.msgwow.com
Domain Path: https://wordpress.org/plugins/MSGWOW SMS Notification Plugin for Woocommerce
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Support URL: msgwow.com/support
Contact Mail: support@msgwow.com

This plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see http://www.gnu.org/licenses/

You can contact us at support@msgwow.com



*/


if ( !defined( 'ABSPATH' ) ) {
    exit();
}

define( 'DIRECCION_MSGWOW_sms', plugin_basename( __FILE__ ) );

//function file
include_once( 'includes/admin/functions-MSGWOW.php' );
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// WooCommerce plugins 
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

	include_once( 'includes/admin/MSGWOW-sms.php' );

	
	$wpml_activo = function_exists( 'icl_object_id' );
	
	
	function MSGWOW_registra_wpml( $MSGWOW_sms_settings ) {
		global $wpml_activo;
		
		
		if ( $wpml_activo && function_exists( 'icl_register_string' ) ) {
			icl_register_string( 'MSGWOW_sms', 'mensaje_pedido', $MSGWOW_sms_settings['mensaje_pedido'] );
			icl_register_string( 'MSGWOW_sms', 'mensaje_recibido', $MSGWOW_sms_settings['mensaje_recibido'] );
			icl_register_string( 'MSGWOW_sms', 'mensaje_procesando', $MSGWOW_sms_settings['mensaje_procesando'] );
			icl_register_string( 'MSGWOW_sms', 'mensaje_completado', $MSGWOW_sms_settings['mensaje_completado'] );
			icl_register_string( 'MSGWOW_sms', 'mensaje_nota', $MSGWOW_sms_settings['mensaje_nota'] );
		} else if ( $wpml_activo ) {
			do_action( 'wpml_register_single_string', 'MSGWOW_sms', 'mensaje_pedido', $MSGWOW_sms_settings['mensaje_pedido'] );
			do_action( 'wpml_register_single_string', 'MSGWOW_sms', 'mensaje_recibido', $MSGWOW_sms_settings['mensaje_recibido'] );
			do_action( 'wpml_register_single_string', 'MSGWOW_sms', 'mensaje_procesando', $MSGWOW_sms_settings['mensaje_procesando'] );
			do_action( 'wpml_register_single_string', 'MSGWOW_sms', 'mensaje_completado', $MSGWOW_sms_settings['mensaje_completado'] );
			do_action( 'wpml_register_single_string', 'MSGWOW_sms', 'mensaje_nota', $MSGWOW_sms_settings['mensaje_nota'] );
		}
	}
	
	
	function MSGWOW_sms_inicializacion() {
		global $MSGWOW_sms_settings;

		MSGWOW_registra_wpml( $MSGWOW_sms_settings );
	}
	add_action( 'init', 'MSGWOW_sms_inicializacion' );

	//SMS Array Data File
	function MSGWOW_sms_tab() {
		include( 'includes/admin/MSGWOW_sms_array_data.php' );
		include( 'includes/MSGWOW-template.php' );
	}

	//function for admin menue
	function MSGWOW_sms_admin_menu() {
		add_submenu_page( 'woocommerce', __( 'MSGWOW SMS Notifications', 'woocommerce-MSGWOW-sms-notifications' ),  __( 'SMS Notifications', 'woocommerce-MSGWOW-sms-notifications' ) , 'manage_woocommerce', 'MSGWOW_sms', 'MSGWOW_sms_tab' );
	}
	add_action( 'admin_menu', 'MSGWOW_sms_admin_menu', 15 );

	//Woocomerce Screen Id function
	function MSGWOW_sms_screen_id( $woocommerce_screen_ids ) {
		$woocommerce_screen_ids[] = 'woocommerce_page_MSGWOW_sms';

		return $woocommerce_screen_ids;
	}
	add_filter( 'woocommerce_screen_ids', 'MSGWOW_sms_screen_id' );

	// Fun() For SMS Registration
	function MSGWOW_sms_registra_opciones() {
		global $MSGWOW_sms_settings;
	
		register_setting( 'MSGWOW_sms_settings_group', 'MSGWOW_sms_settings', 'MSGWOW_sms_update' );
		$MSGWOW_sms_settings = get_option( 'MSGWOW_sms_settings' );

		if ( isset( $MSGWOW_sms_settings['estados_personalizados'] ) && !empty( $MSGWOW_sms_settings['estados_personalizados'] ) ) { 
			foreach ( $MSGWOW_sms_settings['estados_personalizados'] as $estado ) {
				add_action( "woocommerce_order_status_{$estado}", 'MSGWOW_sms_procesa_estados', 10 );
			}
		}
	}
	add_action( 'admin_init', 'MSGWOW_sms_registra_opciones' );
	
	function MSGWOW_sms_update( $MSGWOW_sms_settings ) {
		MSGWOW_registra_wpml( $MSGWOW_sms_settings );
		
		return $MSGWOW_sms_settings;
	}

	//Function For Order Status
	function MSGWOW_sms_procesa_estados( $pedido, $notificacion = false ) {
		global $MSGWOW_sms_settings, $wpml_activo;
		
		$numero_de_pedido	= $pedido;
		$pedido				= new WC_Order( $numero_de_pedido );
		$estado				= is_callable( array( $pedido, 'get_status' ) ) ? $pedido->get_status() : $pedido->status;

		
		if ( isset( $MSGWOW_sms_settings['mensajes'] ) ) {
			if ( $estado == 'on-hold' && !array_intersect( array( "todos", "mensaje_pedido", "mensaje_recibido" ), $MSGWOW_sms_settings['mensajes'] ) ) {
				return;
			} else if ( $estado == 'processing' && !array_intersect( array( "todos", "mensaje_pedido", "mensaje_procesando" ), $MSGWOW_sms_settings['mensajes'] ) ) {
				return;
			} else if ( $estado == 'completed' && !array_intersect( array( "todos", "mensaje_completado" ), $MSGWOW_sms_settings['mensajes'] ) ) {
				return;
			}
		} else {
			return;
		}
		
		if ( !apply_filters( 'MSGWOW_sms_send_message', true, $pedido ) ) {
			return;
		}

		
		$billing_country		= is_callable( array( $pedido, 'get_billing_country' ) ) ? $pedido->get_billing_country() : $pedido->billing_country;
		$billing_phone			= is_callable( array( $pedido, 'get_billing_phone' ) ) ? $pedido->get_billing_phone() : $pedido->billing_phone;
		$shipping_country		= is_callable( array( $pedido, 'get_shipping_country' ) ) ? $pedido->get_shipping_country() : $pedido->shipping_country;
		$campo_envio			= get_post_meta( $numero_de_pedido, $MSGWOW_sms_settings['campo_envio'], false );
		$campo_envio			= ( isset( $campo_envio[0] ) ) ? $campo_envio[0] : '';
		$telefono				= MSGWOW_sms_tel_process( $pedido, $billing_phone, $MSGWOW_sms_settings['servicio'] );
		$telefono_envio			= MSGWOW_sms_tel_process( $pedido, $campo_envio, $MSGWOW_sms_settings['servicio'], false, true );
		$enviar_envio			= ( $telefono != $telefono_envio && isset( $MSGWOW_sms_settings['envio'] ) && $MSGWOW_sms_settings['envio'] == 1 ) ? true : false;
		$internacional			= ( $billing_country && ( WC()->countries->get_base_country() != $billing_country ) ) ? true : false;
		$internacional_envio	= ( $shipping_country && ( WC()->countries->get_base_country() != $shipping_country ) ) ? true : false;
		
		if ( strpos( $MSGWOW_sms_settings['telefono'], "|" ) ) {
			$administradores = explode( "|", $MSGWOW_sms_settings['telefono'] ); //Existe más de uno
		}
		if ( isset( $administradores ) ) {
			foreach( $administradores as $administrador ) {
				$telefono_propietario[]	= MSGWOW_sms_tel_process( $pedido, $administrador, $MSGWOW_sms_settings['servicio'], true );
			}
		} else {
			$telefono_propietario = MSGWOW_sms_tel_process( $pedido, $MSGWOW_sms_settings['telefono'], $MSGWOW_sms_settings['servicio'], true );	
		}
		
		// For WPML
		if ( function_exists( 'icl_register_string' ) || !$wpml_activo ) { //Versión anterior a la 3.2
			$mensaje_pedido		= ( $wpml_activo ) ? icl_translate( 'MSGWOW_sms', 'mensaje_pedido', $MSGWOW_sms_settings['mensaje_pedido'] ) : $MSGWOW_sms_settings['mensaje_pedido'];
			$mensaje_recibido	= ( $wpml_activo ) ? icl_translate( 'MSGWOW_sms', 'mensaje_recibido', $MSGWOW_sms_settings['mensaje_recibido'] ) : $MSGWOW_sms_settings['mensaje_recibido'];
			$mensaje_procesando	= ( $wpml_activo ) ? icl_translate( 'MSGWOW_sms', 'mensaje_procesando', $MSGWOW_sms_settings['mensaje_procesando'] ) : $MSGWOW_sms_settings['mensaje_procesando'];
			$mensaje_completado	= ( $wpml_activo ) ? icl_translate( 'MSGWOW_sms', 'mensaje_completado', $MSGWOW_sms_settings['mensaje_completado'] ) : $MSGWOW_sms_settings['mensaje_completado'];
		} else if ( $wpml_activo ) { //Versión 3.2 o superior
			$mensaje_pedido		= apply_filters( 'wpml_translate_single_string', $MSGWOW_sms_settings['mensaje_pedido'], 'MSGWOW_sms', 'mensaje_pedido' );
			$mensaje_recibido	= apply_filters( 'wpml_translate_single_string', $MSGWOW_sms_settings['mensaje_recibido'], 'MSGWOW_sms', 'mensaje_recibido' );
			$mensaje_procesando	= apply_filters( 'wpml_translate_single_string', $MSGWOW_sms_settings['mensaje_procesando'], 'MSGWOW_sms', 'mensaje_procesando' );
			$mensaje_completado	= apply_filters( 'wpml_translate_single_string', $MSGWOW_sms_settings['mensaje_completado'], 'MSGWOW_sms', 'mensaje_completado' );
		}
		
		//Including API Calling File
		include_once( 'includes/admin/api_calling_method.php' );
		//SMS
		switch( $estado){
			case 'on-hold': 
				if ( !!array_intersect( array( "todos", "mensaje_pedido" ), $MSGWOW_sms_settings['mensajes'] ) && isset( $MSGWOW_sms_settings['notificacion'] ) && $MSGWOW_sms_settings['notificacion'] == 1 && !$notificacion ) {
					if ( !is_array( $telefono_propietario ) ) {
						MSGWOW_sms_api_func( $MSGWOW_sms_settings, $telefono_propietario, MSGWOW_sms_vrl( $mensaje_pedido, $pedido, $MSGWOW_sms_settings['variables'] ) ); 
					} else {
						foreach( $telefono_propietario as $administrador ) {
							MSGWOW_sms_api_func( $MSGWOW_sms_settings, $administrador, MSGWOW_sms_vrl( $mensaje_pedido, $pedido, $MSGWOW_sms_settings['variables'] ) ); 
						}
					}
				}
				if ( !!array_intersect( array( "todos", "mensaje_recibido" ), $MSGWOW_sms_settings['mensajes'] ) ) {
					
					wp_clear_scheduled_hook( 'MSGWOW_sms_ejecuta_el_temporizador' );

					$mensaje = MSGWOW_sms_vrl( $mensaje_recibido, $pedido, $MSGWOW_sms_settings['variables'] ); 

					if ( isset( $MSGWOW_sms_settings['temporizador'] ) && $MSGWOW_sms_settings['temporizador'] > 0 ) {
						wp_schedule_single_event( time() + ( absint( $MSGWOW_sms_settings['temporizador'] ) * 60 * 60 ), 'MSGWOW_sms_ejecuta_el_temporizador' );
					}
				}
				break;
			case 'processing': //Order Processing 
				if ( !!array_intersect( array( "todos", "mensaje_pedido" ), $MSGWOW_sms_settings['mensajes'] ) && isset( $MSGWOW_sms_settings['notificacion'] ) && $MSGWOW_sms_settings['notificacion'] == 1 && $notificacion ) {
					if ( !is_array( $telefono_propietario ) ) {
						MSGWOW_sms_api_func( $MSGWOW_sms_settings, $telefono_propietario, MSGWOW_sms_vrl( $mensaje_pedido, $pedido, $MSGWOW_sms_settings['variables'] ) ); 
					} else {
						foreach( $telefono_propietario as $administrador ) {
							MSGWOW_sms_api_func( $MSGWOW_sms_settings, $administrador, MSGWOW_sms_vrl( $mensaje_pedido, $pedido, $MSGWOW_sms_settings['variables'] ) ); 
						}
					}
				}
				if ( !!array_intersect( array( "todos", "mensaje_procesando" ), $MSGWOW_sms_settings['mensajes'] ) ) {
					$mensaje = MSGWOW_sms_vrl( $mensaje_procesando, $pedido, $MSGWOW_sms_settings['variables'] );
				}
				break;
			case 'completed': 
				if ( !!array_intersect( array( "todos", "mensaje_completado" ), $MSGWOW_sms_settings['mensajes'] ) ) {
					$mensaje = MSGWOW_sms_vrl( $mensaje_completado, $pedido, $MSGWOW_sms_settings['variables'] );
				}
				break;
			default: 
				$mensaje = MSGWOW_sms_vrl( $MSGWOW_sms_settings[$estado], $pedido, $MSGWOW_sms_settings['variables'] );
		}

		if ( isset( $mensaje ) && ( !$internacional || ( isset( $MSGWOW_sms_settings['internacional'] ) && $MSGWOW_sms_settings['internacional'] == 1 ) ) && !$notificacion ) {
			MSGWOW_sms_api_func( $MSGWOW_sms_settings, $telefono, $mensaje ); 
			if ( $enviar_envio ) {
				MSGWOW_sms_api_func( $MSGWOW_sms_settings, $telefono_envio, $mensaje ); 
			}
		}
	}
	//ALL Order Hooks And Filter Action
	add_action( 'woocommerce_order_status_pending_to_on-hold_notification', 'MSGWOW_sms_procesa_estados', 10 ); 
	add_action( 'woocommerce_order_status_failed_to_on-hold_notification', 'MSGWOW_sms_procesa_estados', 10 );
	add_action( 'woocommerce_order_status_processing', 'MSGWOW_sms_procesa_estados', 10 ); 
	add_action( 'woocommerce_order_status_completed', 'MSGWOW_sms_procesa_estados', 10 ); 

	function MSGWOW_sms_notificacion( $pedido ) {
		MSGWOW_sms_procesa_estados( $pedido, true );
	}
	add_action( 'woocommerce_order_status_pending_to_processing_notification', 'MSGWOW_sms_notificacion', 10 ); 
	//SMS Notification Filter
	function MSGWOW_sms_temporizador() {
		global $MSGWOW_sms_settings;
		
		$pedidos = wc_get_orders( array(
			'limit'			=> -1,
			'date_created'	=> '<' . ( time() - ( absint( $MSGWOW_sms_settings['temporizador'] ) * 60 * 60 ) - 1 ),
			'status'		=> 'on-hold',
		) );

		if ( $pedidos ) {
			foreach ( $pedidos as $pedido ) {
				MSGWOW_sms_procesa_estados( is_callable( array( $pedido, 'get_id' ) ) ? $pedido->get_id() : $pedido->id, true );
			}
		}
	}
	add_action( 'MSGWOW_sms_ejecuta_el_temporizador', 'MSGWOW_sms_temporizador' );

	function MSGWOW_sms_proccess( $datos ) {
		global $MSGWOW_sms_settings, $wpml_activo;
		
		if ( isset( $MSGWOW_sms_settings['mensajes']) && !array_intersect( array( "todos", "mensaje_nota" ), $MSGWOW_sms_settings['mensajes'] ) ) {
			return;
		}
	
		
		$numero_de_pedido		= $datos['order_id'];
		$pedido					= new WC_Order( $numero_de_pedido );

		$billing_country		= is_callable( array( $pedido, 'get_billing_country' ) ) ? $pedido->get_billing_country() : $pedido->billing_country;
		$billing_phone			= is_callable( array( $pedido, 'get_billing_phone' ) ) ? $pedido->get_billing_phone() : $pedido->billing_phone;
		$shipping_country		= is_callable( array( $pedido, 'get_shipping_country' ) ) ? $pedido->get_shipping_country() : $pedido->shipping_country;	
		$campo_envio			= get_post_meta( $numero_de_pedido, $MSGWOW_sms_settings['campo_envio'], false );
		$campo_envio			= ( isset( $campo_envio[0] ) ) ? $campo_envio[0] : '';
		$telefono				= MSGWOW_sms_tel_process( $pedido, $billing_phone, $MSGWOW_sms_settings['servicio'] );
		$telefono_envio			= MSGWOW_sms_tel_process( $pedido, $campo_envio, $MSGWOW_sms_settings['servicio'], false, true );
		$enviar_envio			= ( isset( $MSGWOW_sms_settings['envio'] ) && $telefono != $telefono_envio && $MSGWOW_sms_settings['envio'] == 1 ) ? true : false;
		$internacional			= ( $billing_country && ( WC()->countries->get_base_country() != $billing_country ) ) ? true : false;
		$internacional_envio	= ( $shipping_country && ( WC()->countries->get_base_country() != $shipping_country ) ) ? true : false;
	
		$billing_country		= is_callable( array( $pedido, 'get_billing_country' ) ) ? $pedido->get_billing_country() : $pedido->billing_country;
		$billing_phone			= is_callable( array( $pedido, 'get_billing_phone' ) ) ? $pedido->get_billing_phone() : $pedido->billing_phone;
		$shipping_country		= is_callable( array( $pedido, 'get_shipping_country' ) ) ? $pedido->get_shipping_country() : $pedido->shipping_country;
		$campo_envio			= get_post_meta( $numero_de_pedido, $MSGWOW_sms_settings['campo_envio'], false );
		$campo_envio			= ( isset( $campo_envio[0] ) ) ? $campo_envio[0] : '';
		$telefono				= MSGWOW_sms_tel_process( $pedido, $billing_phone, $MSGWOW_sms_settings['servicio'] );
		$telefono_envio			= MSGWOW_sms_tel_process( $pedido, $campo_envio, $MSGWOW_sms_settings['servicio'], false, true );
		$enviar_envio			= ( $telefono != $telefono_envio && isset( $MSGWOW_sms_settings['envio'] ) && $MSGWOW_sms_settings['envio'] == 1 ) ? true : false;
		$internacional			= ( $billing_country && ( WC()->countries->get_base_country() != $billing_country ) ) ? true : false;
		$internacional_envio	= ( $shipping_country && ( WC()->countries->get_base_country() != $shipping_country ) ) ? true : false;

		//WPML
		if ( function_exists( 'icl_register_string' ) || !$wpml_activo ) { //Versión anterior a la 3.2
			$mensaje_nota		= ( $wpml_activo ) ? icl_translate( 'MSGWOW_sms', 'mensaje_nota', $MSGWOW_sms_settings['mensaje_nota'] ) : $MSGWOW_sms_settings['mensaje_nota'];
		} else if ( $wpml_activo ) { 
			$mensaje_nota		= apply_filters( 'wpml_translate_single_string', $MSGWOW_sms_settings['mensaje_nota'], 'MSGWOW_sms', 'mensaje_nota' );
		}
		
		// API File
		include_once( 'includes/admin/api_calling_method.php' );		
		
		if ( !$internacional || ( isset( $MSGWOW_sms_settings['internacional'] ) && $MSGWOW_sms_settings['internacional'] == 1 ) ) {
			MSGWOW_sms_api_func( $MSGWOW_sms_settings, $telefono, MSGWOW_sms_vrl( $mensaje_nota, $pedido, $MSGWOW_sms_settings['variables'], wptexturize( $datos['customer_note'] ) ) ); 
			if ( $enviar_envio ) {
				MSGWOW_sms_api_func( $MSGWOW_sms_settings, $telefono_envio, MSGWOW_sms_vrl( $mensaje_nota, $pedido, $MSGWOW_sms_settings['variables'], wptexturize( $datos['customer_note'] ) ) ); 
			}
		}
	}
	add_action( 'woocommerce_new_customer_note', 'MSGWOW_sms_proccess', 10 );
} else {
	add_action( 'admin_notices', 'MSGWOW_sms_requiere_wc' );
}


//Error Generate Function 
function MSGWOW_sms_requiere_wc() {
	global $MSGWOW_sms;
		
	echo '<div class="error fade" id="message"><h3>' . $MSGWOW_sms['plugin'] . '</h3><h4>' . __( "This plugin require WooCommerce active to run!", 'woocommerce-MSGWOW-sms-notifications' ) . '</h4></div>';
	deactivate_plugins( DIRECCION_MSGWOW_sms );
}
