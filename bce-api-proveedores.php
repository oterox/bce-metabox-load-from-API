<?php
/*
Plugin Name:  BCE API proveedores
Description:  Muestra la lista de proveedores para seleccionar
Version:      0.1.0
Author:       Javier Otero
Author URI:   http://citricamente.com/
*/

add_action( 'init', 'ox_register_meta_fields' );
function ox_register_meta_fields() {

  register_meta( 'post',
               'ox_proveedor',
               [
                 'description'      => _x( 'Provider', 'meta description', 'barcelo' ),
                 'single'           => true,
                 'sanitize_callback' => 'sanitize_text_field',
                 'auth_callback'     => 'ox_custom_fields_auth_callback'
               ]
  );
  
}


function ox_custom_fields_auth_callback( $allowed, $meta_key, $post_id, $user_id, $cap, $caps ) {
  
  if( 'post' == get_post_type( $post_id ) && current_user_can( 'edit_post', $post_id ) ) {
    $allowed = true;
  } else {
    $allowed = false;
  }

  return $allowed;

}

add_action( 'add_meta_boxes', 'ox_meta_boxes' );
function ox_meta_boxes() {
    add_meta_box( 'ox-meta-box', __( 'Providers', 'barcelo' ), 'ox_meta_box_callback', 'post' );
}

function ox_meta_box_callback( $post ) {
     
    wp_nonce_field( 'ox_meta_box', 'ox_meta_box_noncename' );

    $current_value = get_post_meta( $post->ID, 'ox_proveedor', true );

	$response = wp_remote_get( 'http://18.217.60.106:8080/users' );
	
	if ( is_array( $response ) ) {

		$body = wp_remote_retrieve_body( $response );
	  
		$data = json_decode( $body );
		if( ! empty( $data ) ) {
			?>
			<p>
				<label class="label" for="ox_proveedor"><?php  _e( 'Select provider', 'barcelo' ); ?></label>
				<select name="ox_proveedor" id="ox_proveedor">
					<option value="0"><?php _e( 'Select provider', 'barcelo' ); ?></option>
					<?php
					foreach( $data->objects as $product ) { ?>
						<option value="<?php echo $product->id; ?>" <?php selected( $current_value, $product->id); ?>><?php echo $product->email; ?></option>
						<?php
					} 
					?>
	        	</select>
		    </p>
			<?php
		}

	}
}

add_action( 'save_post', 'ox_save_custom_fields', 10, 2 );
function ox_save_custom_fields( $post_id, $post ) {
    
    // Primero, comprobamos el nonce como medida de segurida
    if ( ! isset( $_POST['ox_meta_box_noncename'] ) || ! wp_verify_nonce( $_POST['ox_meta_box_noncename'], 'ox_meta_box' ) ) {
        return;
    }
              
    if( isset( $_POST['ox_proveedor'] ) && $_POST['ox_proveedor'] != "" ) {
        update_post_meta( $post_id, 'ox_proveedor', $_POST['ox_proveedor'] );
    } else {
        delete_post_meta( $post_id, 'ox_proveedor' );
    }

}
