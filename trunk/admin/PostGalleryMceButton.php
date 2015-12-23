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
    private $textdomain;

    public function __construct( $pluginName ) {
        $this->pluginName = $pluginName;
        $this->textdomain = $pluginName;

        add_action( 'init', array( $this, 'addSliderButton' ) );
        add_filter( 'tiny_mce_version', array( $this, 'refreshMce' ) );
        add_action( 'wp_ajax_postgalleryslider', array( $this, 'sliderGetOptionsWindow' ) );
        add_filter( 'admin_head', array( $this, 'addSliderList' ) );
    }

    /**
     * Adds the button
     */
    public function addSliderButton() {
        if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
            return;
        }

        if ( get_user_option( 'rich_editing' ) ) {
            add_filter( 'mce_external_plugins', array( $this, 'addSliderTinymcePlugin' ) );
            add_filter( 'mce_buttons', array( $this, 'registerSliderButton' ) );
        }
    }

    /**
     * Register the button
     *
     * @param $buttons
     * @return mixed
     */
    public function registerSliderButton( $buttons ) {
        array_push( $buttons, '|', 'PostGallerySlider' );

        return $buttons;
    }

    /**
     * Adds the js-plugin
     *
     * @param $pluginArray
     * @return mixed
     */
    public function addSliderTinymcePlugin( $pluginArray ) {
        $pluginArray['PostGallerySlider'] = POSTGALLERY_URL . '/admin/js/editor-plugin.js';
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
        $sliderList = array();
        $sliderList[] = array( 'text' => '', 'value' => '' );
        $sliders = get_posts( array(
            'post_type' => 'postgalleryslider',
        ) );

        foreach ( $sliders as $slider ) {
            $sliderList[] = array( 'text' => $slider->post_title, 'value' => $slider->ID );
        }

        echo '<script>var postgallerySliders = ' . json_encode( $sliderList ) . ';</script>';
    }

    /**
     * Adds the popup with selectable sliders (called via ajax)
     */
    public function sliderGetOptionsWindow() {
        // List all sliders
        $sliders = get_posts( array(
            'post_type' => 'postgalleryslider',
        ) );

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