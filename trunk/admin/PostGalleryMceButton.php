<?php

/* * **********************************
 * Autor: Sascha Hennemann
 * Erstellt am: 27.01.2013 16:31:23
 *
 * Zuletzt gändert
 * von:
 * ********************************** */

namespace Admin;

/**
 * Class MceButton
 *
 * Adds a button to tinymce
 *
 * @package Admin
 */
class PostGalleryMceButton {
    private $pluginName;

    public function __construct( $pluginName ) {
        $this->pluginName = $pluginName;

        add_action( 'init', [ $this, 'addSliderButton' ] );
        add_filter( 'tiny_mce_version', [ $this, 'refreshMce' ] );
        add_action( 'wp_ajax_postgalleryslider', [ $this, 'sliderGetOptionsWindow' ] );
        add_filter( 'admin_head', [ $this, 'addSliderList' ] );
        add_filter( 'admin_head', [ $this, 'addGalleryPostList' ] );
    }

    /**
     * Adds the button
     */
    public function addSliderButton() {
        if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
            return;
        }

        if ( get_user_option( 'rich_editing' ) ) {
            add_filter( 'mce_external_plugins', [ $this, 'addPostGalleryTinymcePlugins' ] );
            add_filter( 'mce_buttons', [ $this, 'registerPostGalleryButtons' ] );
        }
    }

    /**
     * Register the button
     *
     * @param $buttons
     * @return mixed
     */
    public function registerPostGalleryButtons( $buttons ) {
        array_push( $buttons, '|', 'PostGallerySlider' );
        array_push( $buttons, '|', 'PostGallery' );

        return $buttons;
    }

    /**
     * Adds the js-plugin
     *
     * @param $pluginArray
     * @return mixed
     */
    public function addPostGalleryTinymcePlugins( $pluginArray ) {
        $pluginArray['PostGallerySlider'] = POSTGALLERY_URL . '/admin/js/editor-slider-plugin.js';
        $pluginArray['PostGallery'] = POSTGALLERY_URL . '/admin/js/editor-plugin.js';
        return $pluginArray;
    }

    /**
     * Refresh tinymce to show button
     *
     * @param $ver
     * @return int
     */
    public function refreshMce( $ver ) {
        $ver += 3;
        return $ver;
    }

    /**
     * Adds a js-variable to head
     */
    public function addSliderList() {
        $list = [];
        $list[] = [ 'text' => '', 'value' => '' ];
        $items = get_posts( [
            'post_type' => 'postgalleryslider',
        ] );

        foreach ( $items as $item ) {
            $list[] = [ 'text' => $item->post_title, 'value' => $item->ID ];
        }

        echo '<script>var postgallerySliders = ' . json_encode( $list ) . ';</script>';
    }

    /**
     * Adds a js-variable to head
     */
    public function addGalleryPostList() {
        $list = [];
        $list[] = [ 'text' => '', 'value' => '' ];

        $postTypes = get_post_types(
        //array('exclude_from_search' => 'attachment,revision,nav_menu_item,wpcf7_contact_form,postgalleryslider')
            [ 'public' => true ]
        );
        unset( $postTypes['attachment'] );

        $items = get_posts( [
            'post_type' => $postTypes,
            'numberposts' => -1,
            'posts_per_page' => -1,
        ] );

        foreach ( $items as $item ) {
            $images = \Inc\PostGallery::getImages( $item->ID );
            if ( count( $images ) ) {
                $link = str_replace( get_bloginfo( 'wpurl' ) . '/', '', get_the_permalink( $item ) );
                $list[] = [ 'text' => $item->post_title . ' (' . $item->ID . ')', 'value' => $link ];
            }
        }

        echo '<script>var postgalleryPosts = ' . json_encode( $list ) . ';</script>';
    }

    /**
     * Adds the popup with selectable sliders (called via ajax)
     */
    public function sliderGetOptionsWindow() {
        // List all sliders
        $sliders = get_posts( [
            'post_type' => 'postgalleryslider',
        ] );

        ?>
        <select id="postgalleryslider-select">
            <?php foreach ( $sliders as $slider ): ?>
                <option value="<?php echo $slider->ID ?>"><?php echo $slider->post_title; ?></option>
            <?php endforeach; ?>
        </select>
        <input type="button" value="<?php _e( 'Add' ); ?>"/>

        <?php
        die();
    }
}