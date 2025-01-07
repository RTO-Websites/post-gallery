<?php
namespace Lib\Controls;

use \Elementor\Base_Control;
class PostGalleryElementorControl extends Base_Control
{

    public function get_type()
    {
        return 'postgallerycontrol';
    }

    public function content_template() {
        global $post;
        echo '<div class="wp-core-ui pg-image-container">';
        // gallery-images will be added with javascript
        echo '</div>';
    }
}
