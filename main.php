<?php
/*
Plugin Name: Cart SMS Notification-biz
Plugin URI: https://github.com/saurabhPRDXN/Cart-SMS-Notification-biz
Description: Add to WooCommerce SMS notifications to your clients for order status changes. Also you can receive an SMS message when the shop get a new order.
Version: 1.0
Author: Saurabh upadhyay
Author URI: NA
Text Domain: NA
Domain Path: https://wordpress.org/plugins/Cart-SMS-Notification-biz
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see http://www.gnu.org/licenses/

You can contact us at saurabh.upadhyay@webdew.in



*/


if ( !defined( 'ABSPATH' ) ) {
    exit();
}

define( 'DIRECCION_biz_sms', plugin_basename( __FILE__ ) );

//function file
include_once( 'includes/admin/functions-biz.php' );
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// WooCommerce plugins 
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

	include_once( 'includes/admin/biz-sms.php' );

	
	$wpml_activo = function_exists( 'icl_object_id' );
	
	
	function biz_registra_wpml( $biz_sms_settings ) {
		global $wpml_activo;
		
		
		if ( $wpml_activo && function_exists( 'icl_register_string' ) ) {
			icl_register_string( 'biz_sms', 'mensaje_pedido', $biz_sms_settings['mensaje_pedido'] );
			icl_register_string( 'biz_sms', 'mensaje_recibido', $biz_sms_settings['mensaje_recibido'] );
			icl_register_string( 'biz_sms', 'mensaje_procesando', $biz_sms_settings['mensaje_procesando'] );
			icl_register_string( 'biz_sms', 'mensaje_completado', $biz_sms_settings['mensaje_completado'] );
			icl_register_string( 'biz_sms', 'mensaje_nota', $biz_sms_settings['mensaje_nota'] );
		} else if ( $wpml_activo ) {
			do_action( 'wpml_register_single_string', 'biz_sms', 'mensaje_pedido', $biz_sms_settings['mensaje_pedido'] );
			do_action( 'wpml_register_single_string', 'biz_sms', 'mensaje_recibido', $biz_sms_settings['mensaje_recibido'] );
			do_action( 'wpml_register_single_string', 'biz_sms', 'mensaje_procesando', $biz_sms_settings['mensaje_procesando'] );
			do_action( 'wpml_register_single_string', 'biz_sms', 'mensaje_completado', $biz_sms_settings['mensaje_completado'] );
			do_action( 'wpml_register_single_string', 'biz_sms', 'mensaje_nota', $biz_sms_settings['mensaje_nota'] );
		}
	}
	
	
	function biz_sms_inicializacion() {
		global $biz_sms_settings;

		biz_registra_wpml( $biz_sms_settings );
	}
	add_action( 'init', 'biz_sms_inicializacion' );

	//SMS Array Data File
	function biz_sms_tab() {
		include( 'includes/admin/biz_sms_array_data.php' );
		include( 'includes/biz-template.php' );
	}

	//function for admin menue
	function biz_sms_admin_menu() {
		add_submenu_page( 'woocommerce', __( 'Biz SMS Notifications', 'woocommerce-biz-sms-notifications' ),  __( 'SMS Notifications', 'woocommerce-biz-sms-notifications' ) , 'manage_woocommerce', 'biz_sms', 'biz_sms_tab' );
	}
	add_action( 'admin_menu', 'biz_sms_admin_menu', 15 );

	//Woocomerce Screen Id function
	function biz_sms_screen_id( $woocommerce_screen_ids ) {
		$woocommerce_screen_ids[] = 'woocommerce_page_biz_sms';

		return $woocommerce_screen_ids;
	}
	add_filter( 'woocommerce_screen_ids', 'biz_sms_screen_id' );

	// Fun() For SMS Registration
	function biz_sms_registra_opciones() {
		global $biz_sms_settings;
	
		register_setting( 'biz_sms_settings_group', 'biz_sms_settings', 'biz_sms_update' );
		$biz_sms_settings = get_option( 'biz_sms_settings' );

		if ( isset( $biz_sms_settings['estados_personalizados'] ) && !empty( $biz_sms_settings['estados_personalizados'] ) ) { 
			foreach ( $biz_sms_settings['estados_personalizados'] as $estado ) {
				add_action( "woocommerce_order_status_{$estado}", 'biz_sms_procesa_estados', 10 );
			}
		}
	}
	add_action( 'admin_init', 'biz_sms_registra_opciones' );
	
	function biz_sms_update( $biz_sms_settings ) {
		biz_registra_wpml( $biz_sms_settings );
		
		return $biz_sms_settings;
	}

	//Function For Order Status
	function biz_sms_procesa_estados( $pedido, $notificacion = false ) {
		global $biz_sms_settings, $wpml_activo;
		
		$numero_de_pedido	= $pedido;
		$pedido				= new WC_Order( $numero_de_pedido );
		$estado				= is_callable( array( $pedido, 'get_status' ) ) ? $pedido->get_status() : $pedido->status;

		
		if ( isset( $biz_sms_settings['mensajes'] ) ) {
			if ( $estado == 'on-hold' && !array_intersect( array( "todos", "mensaje_pedido", "mensaje_recibido" ), $biz_sms_settings['mensajes'] ) ) {
				return;
			} else if ( $estado == 'processing' && !array_intersect( array( "todos", "mensaje_pedido", "mensaje_procesando" ), $biz_sms_settings['mensajes'] ) ) {
				return;
			} else if ( $estado == 'completed' && !array_intersect( array( "todos", "mensaje_completado" ), $biz_sms_settings['mensajes'] ) ) {
				return;
			}
		} else {
			return;
		}
		
		if ( !apply_filters( 'biz_sms_send_message', true, $pedido ) ) {
			return;
		}

		
		$billing_country		= is_callable( array( $pedido, 'get_billing_country' ) ) ? $pedido->get_billing_country() : $pedido->billing_country;
		$billing_phone			= is_callable( array( $pedido, 'get_billing_phone' ) ) ? $pedido->get_billing_phone() : $pedido->billing_phone;
		$shipping_country		= is_callable( array( $pedido, 'get_shipping_country' ) ) ? $pedido->get_shipping_country() : $pedido->shipping_country;
		$campo_envio			= get_post_meta( $numero_de_pedido, $biz_sms_settings['campo_envio'], false );
		$campo_envio			= ( isset( $campo_envio[0] ) ) ? $campo_envio[0] : '';
		$telefono				= biz_sms_tel_process( $pedido, $billing_phone, $biz_sms_settings['servicio'] );
		$telefono_envio			= biz_sms_tel_process( $pedido, $campo_envio, $biz_sms_settings['servicio'], false, true );
		$enviar_envio			= ( $telefono != $telefono_envio && isset( $biz_sms_settings['envio'] ) && $biz_sms_settings['envio'] == 1 ) ? true : false;
		$internacional			= ( $billing_country && ( WC()->countries->get_base_country() != $billing_country ) ) ? true : false;
		$internacional_envio	= ( $shipping_country && ( WC()->countries->get_base_country() != $shipping_country ) ) ? true : false;
		
		if ( strpos( $biz_sms_settings['telefono'], "|" ) ) {
			$administradores = explode( "|", $biz_sms_settings['telefono'] ); //Existe más de uno
		}
		if ( isset( $administradores ) ) {
			foreach( $administradores as $administrador ) {
				$telefono_propietario[]	= biz_sms_tel_process( $pedido, $administrador, $biz_sms_settings['servicio'], true );
			}
		} else {
			$telefono_propietario = biz_sms_tel_process( $pedido, $biz_sms_settings['telefono'], $biz_sms_settings['servicio'], true );	
		}
		
		// For WPML
		if ( function_exists( 'icl_register_string' ) || !$wpml_activo ) { //Versión anterior a la 3.2
			$mensaje_pedido		= ( $wpml_activo ) ? icl_translate( 'biz_sms', 'mensaje_pedido', $biz_sms_settings['mensaje_pedido'] ) : $biz_sms_settings['mensaje_pedido'];
			$mensaje_recibido	= ( $wpml_activo ) ? icl_translate( 'biz_sms', 'mensaje_recibido', $biz_sms_settings['mensaje_recibido'] ) : $biz_sms_settings['mensaje_recibido'];
			$mensaje_procesando	= ( $wpml_activo ) ? icl_translate( 'biz_sms', 'mensaje_procesando', $biz_sms_settings['mensaje_procesando'] ) : $biz_sms_settings['mensaje_procesando'];
			$mensaje_completado	= ( $wpml_activo ) ? icl_translate( 'biz_sms', 'mensaje_completado', $biz_sms_settings['mensaje_completado'] ) : $biz_sms_settings['mensaje_completado'];
		} else if ( $wpml_activo ) { //Versión 3.2 o superior
			$mensaje_pedido		= apply_filters( 'wpml_translate_single_string', $biz_sms_settings['mensaje_pedido'], 'biz_sms', 'mensaje_pedido' );
			$mensaje_recibido	= apply_filters( 'wpml_translate_single_string', $biz_sms_settings['mensaje_recibido'], 'biz_sms', 'mensaje_recibido' );
			$mensaje_procesando	= apply_filters( 'wpml_translate_single_string', $biz_sms_settings['mensaje_procesando'], 'biz_sms', 'mensaje_procesando' );
			$mensaje_completado	= apply_filters( 'wpml_translate_single_string', $biz_sms_settings['mensaje_completado'], 'biz_sms', 'mensaje_completado' );
		}
		
		//Including API Calling File
		include_once( 'includes/admin/api_calling_method.php' );
		//SMS
		switch( $estado){
			case 'on-hold': 
				if ( !!array_intersect( array( "todos", "mensaje_pedido" ), $biz_sms_settings['mensajes'] ) && isset( $biz_sms_settings['notificacion'] ) && $biz_sms_settings['notificacion'] == 1 && !$notificacion ) {
					if ( !is_array( $telefono_propietario ) ) {
						biz_sms_api_func( $biz_sms_settings, $telefono_propietario, biz_sms_vrl( $mensaje_pedido, $pedido, $biz_sms_settings['variables'] ) ); 
					} else {
						foreach( $telefono_propietario as $administrador ) {
							biz_sms_api_func( $biz_sms_settings, $administrador, biz_sms_vrl( $mensaje_pedido, $pedido, $biz_sms_settings['variables'] ) ); 
						}
					}
				}
				if ( !!array_intersect( array( "todos", "mensaje_recibido" ), $biz_sms_settings['mensajes'] ) ) {
					
					wp_clear_scheduled_hook( 'biz_sms_ejecuta_el_temporizador' );

					$mensaje = biz_sms_vrl( $mensaje_recibido, $pedido, $biz_sms_settings['variables'] ); 

					if ( isset( $biz_sms_settings['temporizador'] ) && $biz_sms_settings['temporizador'] > 0 ) {
						wp_schedule_single_event( time() + ( absint( $biz_sms_settings['temporizador'] ) * 60 * 60 ), 'biz_sms_ejecuta_el_temporizador' );
					}
				}
				break;
			case 'processing': //Order Processing 
				if ( !!array_intersect( array( "todos", "mensaje_pedido" ), $biz_sms_settings['mensajes'] ) && isset( $biz_sms_settings['notificacion'] ) && $biz_sms_settings['notificacion'] == 1 && $notificacion ) {
					if ( !is_array( $telefono_propietario ) ) {
						biz_sms_api_func( $biz_sms_settings, $telefono_propietario, biz_sms_vrl( $mensaje_pedido, $pedido, $biz_sms_settings['variables'] ) ); 
					} else {
						foreach( $telefono_propietario as $administrador ) {
							biz_sms_api_func( $biz_sms_settings, $administrador, biz_sms_vrl( $mensaje_pedido, $pedido, $biz_sms_settings['variables'] ) ); 
						}
					}
				}
				if ( !!array_intersect( array( "todos", "mensaje_procesando" ), $biz_sms_settings['mensajes'] ) ) {
					$mensaje = biz_sms_vrl( $mensaje_procesando, $pedido, $biz_sms_settings['variables'] );
				}
				break;
			case 'completed': 
				if ( !!array_intersect( array( "todos", "mensaje_completado" ), $biz_sms_settings['mensajes'] ) ) {
					$mensaje = biz_sms_vrl( $mensaje_completado, $pedido, $biz_sms_settings['variables'] );
				}
				break;
			default: 
				$mensaje = biz_sms_vrl( $biz_sms_settings[$estado], $pedido, $biz_sms_settings['variables'] );
		}

		if ( isset( $mensaje ) && ( !$internacional || ( isset( $biz_sms_settings['internacional'] ) && $biz_sms_settings['internacional'] == 1 ) ) && !$notificacion ) {
			biz_sms_api_func( $biz_sms_settings, $telefono, $mensaje ); 
			if ( $enviar_envio ) {
				biz_sms_api_func( $biz_sms_settings, $telefono_envio, $mensaje ); 
			}
		}
	}
	//ALL Order Hooks And Filter Action
	add_action( 'woocommerce_order_status_pending_to_on-hold_notification', 'biz_sms_procesa_estados', 10 ); 
	add_action( 'woocommerce_order_status_failed_to_on-hold_notification', 'biz_sms_procesa_estados', 10 );
	add_action( 'woocommerce_order_status_processing', 'biz_sms_procesa_estados', 10 ); 
	add_action( 'woocommerce_order_status_completed', 'biz_sms_procesa_estados', 10 ); 

	function biz_sms_notificacion( $pedido ) {
		biz_sms_procesa_estados( $pedido, true );
	}
	add_action( 'woocommerce_order_status_pending_to_processing_notification', 'biz_sms_notificacion', 10 ); 
	//SMS Notification Filter
	function biz_sms_temporizador() {
		global $biz_sms_settings;
		
		$pedidos = wc_get_orders( array(
			'limit'			=> -1,
			'date_created'	=> '<' . ( time() - ( absint( $biz_sms_settings['temporizador'] ) * 60 * 60 ) - 1 ),
			'status'		=> 'on-hold',
		) );

		if ( $pedidos ) {
			foreach ( $pedidos as $pedido ) {
				biz_sms_procesa_estados( is_callable( array( $pedido, 'get_id' ) ) ? $pedido->get_id() : $pedido->id, true );
			}
		}
	}
	add_action( 'biz_sms_ejecuta_el_temporizador', 'biz_sms_temporizador' );

	function biz_sms_proccess( $datos ) {
		global $biz_sms_settings, $wpml_activo;
		
		if ( isset( $biz_sms_settings['mensajes']) && !array_intersect( array( "todos", "mensaje_nota" ), $biz_sms_settings['mensajes'] ) ) {
			return;
		}
	
		
		$numero_de_pedido		= $datos['order_id'];
		$pedido					= new WC_Order( $numero_de_pedido );

		$billing_country		= is_callable( array( $pedido, 'get_billing_country' ) ) ? $pedido->get_billing_country() : $pedido->billing_country;
		$billing_phone			= is_callable( array( $pedido, 'get_billing_phone' ) ) ? $pedido->get_billing_phone() : $pedido->billing_phone;
		$shipping_country		= is_callable( array( $pedido, 'get_shipping_country' ) ) ? $pedido->get_shipping_country() : $pedido->shipping_country;	
		$campo_envio			= get_post_meta( $numero_de_pedido, $biz_sms_settings['campo_envio'], false );
		$campo_envio			= ( isset( $campo_envio[0] ) ) ? $campo_envio[0] : '';
		$telefono				= biz_sms_tel_process( $pedido, $billing_phone, $biz_sms_settings['servicio'] );
		$telefono_envio			= biz_sms_tel_process( $pedido, $campo_envio, $biz_sms_settings['servicio'], false, true );
		$enviar_envio			= ( isset( $biz_sms_settings['envio'] ) && $telefono != $telefono_envio && $biz_sms_settings['envio'] == 1 ) ? true : false;
		$internacional			= ( $billing_country && ( WC()->countries->get_base_country() != $billing_country ) ) ? true : false;
		$internacional_envio	= ( $shipping_country && ( WC()->countries->get_base_country() != $shipping_country ) ) ? true : false;
	
		$billing_country		= is_callable( array( $pedido, 'get_billing_country' ) ) ? $pedido->get_billing_country() : $pedido->billing_country;
		$billing_phone			= is_callable( array( $pedido, 'get_billing_phone' ) ) ? $pedido->get_billing_phone() : $pedido->billing_phone;
		$shipping_country		= is_callable( array( $pedido, 'get_shipping_country' ) ) ? $pedido->get_shipping_country() : $pedido->shipping_country;
		$campo_envio			= get_post_meta( $numero_de_pedido, $biz_sms_settings['campo_envio'], false );
		$campo_envio			= ( isset( $campo_envio[0] ) ) ? $campo_envio[0] : '';
		$telefono				= biz_sms_tel_process( $pedido, $billing_phone, $biz_sms_settings['servicio'] );
		$telefono_envio			= biz_sms_tel_process( $pedido, $campo_envio, $biz_sms_settings['servicio'], false, true );
		$enviar_envio			= ( $telefono != $telefono_envio && isset( $biz_sms_settings['envio'] ) && $biz_sms_settings['envio'] == 1 ) ? true : false;
		$internacional			= ( $billing_country && ( WC()->countries->get_base_country() != $billing_country ) ) ? true : false;
		$internacional_envio	= ( $shipping_country && ( WC()->countries->get_base_country() != $shipping_country ) ) ? true : false;

		//WPML
		if ( function_exists( 'icl_register_string' ) || !$wpml_activo ) { //Versión anterior a la 3.2
			$mensaje_nota		= ( $wpml_activo ) ? icl_translate( 'biz_sms', 'mensaje_nota', $biz_sms_settings['mensaje_nota'] ) : $biz_sms_settings['mensaje_nota'];
		} else if ( $wpml_activo ) { 
			$mensaje_nota		= apply_filters( 'wpml_translate_single_string', $biz_sms_settings['mensaje_nota'], 'biz_sms', 'mensaje_nota' );
		}
		
		// API File
		include_once( 'includes/admin/api_calling_method.php' );		
		
		if ( !$internacional || ( isset( $biz_sms_settings['internacional'] ) && $biz_sms_settings['internacional'] == 1 ) ) {
			biz_sms_api_func( $biz_sms_settings, $telefono, biz_sms_vrl( $mensaje_nota, $pedido, $biz_sms_settings['variables'], wptexturize( $datos['customer_note'] ) ) ); 
			if ( $enviar_envio ) {
				biz_sms_api_func( $biz_sms_settings, $telefono_envio, biz_sms_vrl( $mensaje_nota, $pedido, $biz_sms_settings['variables'], wptexturize( $datos['customer_note'] ) ) ); 
			}
		}
	}
	add_action( 'woocommerce_new_customer_note', 'biz_sms_proccess', 10 );
} else {
	add_action( 'admin_notices', 'biz_sms_requiere_wc' );
}


//Error Generate Function 
function biz_sms_requiere_wc() {
	global $biz_sms;
		
	echo '<div class="error fade" id="message"><h3>' . $biz_sms['plugin'] . '</h3><h4>' . __( "This plugin require WooCommerce active to run!", 'woocommerce-biz-sms-notifications' ) . '</h4></div>';
	deactivate_plugins( DIRECCION_biz_sms );
}
