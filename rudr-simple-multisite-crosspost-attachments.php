<?php
/*
 * Plugin name: Simple Multisite Crossposting – Attachments in Custom Fields
 * Author: Misha Rudrastyh
 * Author URI: https://rudrastyh.com
 * Description: Allows to crosspost media files from custom fields.
 * Version: 4.0
 * Plugin URI: https://rudrastyh.com/support/crossposting-attachments-from-post-meta
 * Network: true
 */

class Rudr_SMC_Attachments {

	function __construct() {

		add_filter( 'rudr_pre_crosspost_meta', array( $this, 'process_meta' ), 10, 3 );
		add_filter( 'rudr_pre_crosspost_termmeta', array( $this, 'process_meta' ), 10, 3 );

	}

	function process_meta( $meta_value, $meta_key, $object_id ) {

		if( ! class_exists( 'Rudr_Simple_Multisite_Crosspost' ) ) {
			return $meta_value;
		}

		// not an attachment custom field
		if( ! in_array( $meta_key, apply_filters( 'rudr_crosspost_attachment_meta_keys', array() ) ) ) {
			return $meta_value;
		}

		$meta_value = maybe_unserialize( $meta_value );
		// let's make it array anyway for easier processing
		$ids = is_array( $meta_value ) ? $meta_value : array( $meta_value );

		$new_blog_id = get_current_blog_id();
		restore_current_blog();

		$attachments_data = array();
		foreach( $ids as $id ) {
			$attachments_data[] = Rudr_Simple_Multisite_Crosspost::prepare_attachment_data( $id );
		}

		switch_to_blog( $new_blog_id );

		$attachment_ids = array();
		foreach( $attachments_data as $attachment_data ) {
			$upload = Rudr_Simple_Multisite_Crosspost::maybe_copy_image( $attachment_data );
			if( isset( $upload[ 'id' ] ) && $upload[ 'id' ] ) {
				$attachment_ids[] = $upload[ 'id' ];
			}
		}

		return is_array( $meta_value ) ? maybe_serialize( $attachment_ids ) : ( $attachment_ids ? reset( $attachment_ids ) : null );

	}


}

new Rudr_SMC_Attachments;
