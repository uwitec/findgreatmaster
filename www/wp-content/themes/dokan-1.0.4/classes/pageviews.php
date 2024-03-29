<?php

/**
 * Pageviews - for counting product post views.
 */
class Dokan_Pageviews {

    private $meta_key = 'pageview';

    public function __construct() {
        /* Registers the entry views extension scripts if we're on the correct page. */
        add_action( 'template_redirect', array($this, 'load_views') );

        /* Add the entry views AJAX actions to the appropriate hooks. */
        add_action( 'wp_ajax_dokan_pageview', array($this, 'update_ajax') );
        add_action( 'wp_ajax_nopriv_dokan_pageview', array($this, 'update_ajax') );
    }

    function load_scripts() {

        $nonce = wp_create_nonce( 'dokan_pageview' );

        echo '<script type="text/javascript">/* <![CDATA[ */ jQuery(document).ready( function($) { $.post( "' . admin_url( 'admin-ajax.php' ) . '", { action : "dokan_pageview", _ajax_nonce : "' . $nonce . '", post_id : ' . get_the_ID() . ' } ); } ); /* ]]> */</script>' . "\n";
    }

    function load_views() {

        if ( is_singular( 'product' ) ) {

            wp_enqueue_script( 'jquery' );

            add_action( 'wp_footer', array($this, 'load_scripts') );
        }
    }

    function update_view( $post_id = '' ) {

        if ( !empty( $post_id ) ) {

            $old_views = get_post_meta( $post_id, $this->meta_key, true );
            $new_views = absint( $old_views ) + 1;

            update_post_meta( $post_id, $this->meta_key, $new_views, $old_views );
        }
    }

    function update_ajax() {

        check_ajax_referer( 'dokan_pageview' );

        if ( isset( $_POST['post_id'] ) ) {
            $post_id = absint( $_POST['post_id'] );
        }

        if ( !empty( $post_id ) ) {
            $this->update_view( $post_id );
        }

        exit;
    }
}