<?php
use \Elementor\Control_Base;
class PostGalleryElementorControl extends Control_Base
{

    public function get_type()
    {
        return 'postgallerycontrol';
    }

    public function content_template() {
        global $post;
        echo '<div class="wp-core-ui">';
        \Admin\PostGalleryAdmin::getInstance()->addGalleryPictures($post);
        echo '</div>';
    }
}

add_action('elementor/controls/controls_registered', function () {
    \Elementor\Plugin::instance()->controls_manager->get_controls();
    \Elementor\Plugin::instance()->controls_manager->register_control('postgallerycontrol', new PostGalleryElementorControl());
});