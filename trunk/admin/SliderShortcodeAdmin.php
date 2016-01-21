<?php
namespace Admin;

use Inc\PostGallery;
use MagicAdminPage\MagicAdminPage;
use Thumb\Thumb;


class SliderShortcodeAdmin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $pluginName The ID of this plugin.
     */
    private $pluginName;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin..
     */
    private $version;


    /**
     * Textdomain of the plugin
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $textdomain;


    private $optionFields;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $pluginName The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct( $pluginName, $version ) {
        $this->textdomain = $pluginName;
        $this->pluginName = $pluginName;
        $this->version = $version;

        $this->optionFields = array(
            'sliderWidth' => array(
                'type' => 'text',
                'label' => __( 'Width', $this->textdomain ),
            ),
            'sliderHeight' => array(
                'type' => 'text',
                'label' => __( 'Height', $this->textdomain ),
            ),
            'sliderScale' => array(
                'type' => 'select',
                'label' => __( 'Image-Scale', $this->textdomain ),
                'options' => array(
                    0 => __( 'crop', $this->textdomain ),
                    1 => __( 'Keep proportions (long edge)', $this->textdomain ),
                    2 => __( 'Keep proportions (short edge)', $this->textdomain ),
                    3 => __( 'Ignore proportions', $this->textdomain ),
                ),
            ),

            'sliderImageWidth' => array(
                'type' => 'text',
                'label' => __( 'Image-Width', $this->textdomain ),
            ),
            'sliderImageHeight' => array(
                'type' => 'text',
                'label' => __( 'Image-Height', $this->textdomain ),
            ),

            'sliderLoadFrom' => array(
                'type' => 'select',
                'label' => __( 'Load images from', $this->textdomain ),
                'options' => '',
                'multiple' => true,
            ),
            'sliderOwlConfig' => array(
                'type' => 'textarea',
                'label' => __( 'Owl-Config', $this->textdomain ),
                'descTop' => '<b>' . __( 'Presets', $this->textdomain ) . '</b>:'
                    . '<select class="owl-slider-presets" data-lang="' . get_locale() . '" data-container="sliderOwlConfig">
                    <option value="">Slide (' . __( 'Default', $this->textdomain ) . ')</option>
                    <option value="fade">Fade</option>
                    <option value="slidevertical">SlideVertical</option>
                    <option value="zoominout">Zoom In/out</option>
                    </select>',

                'desc' => '<br />' . __( 'You can use these options', $this->textdomain ) . ':<br />' .
                    '<a href="http://www.owlcarousel.owlgraphic.com/docs/api-options.html" target="_blank">
                                        OwlCarousel Options
                                    </a>
                                    <br />' .
                    __( 'You can use these animations', $this->textdomain ) . ':<br />
                                    <a href="http://daneden.github.io/animate.css/" target="_blank">
                                        Animate.css
                                    </a>
                                </div>',
                'inputClass' => 'owl-post-config',
                'cols' => 50,
                'rows' => 10,
                'default' => 'items: 1',
            ),
            'sliderNoLazy' => array(
                'type' => 'checkbox',
                'label' => __( 'Disable lazy load', $this->textdomain ),
            ),
        );

        add_action( 'init', array( $this, '_createPostTypes' ) );
        add_action( 'add_meta_boxes', array( $this, '_registerPostSettings' ) );
        add_action( 'save_post', array( $this, 'savePostMeta' ), 10, 2 );
    }

    /**
     * Fills the array with option-fields
     * Needs to be filled after all post-types are registered
     */
    public function _setPostList() {
        $postTypes = get_post_types();
        unset( $postTypes['revision'] );
        unset( $postTypes['nav_menu_item'] );

        $postlist = array( '' );

        $allPosts = get_posts( array(
            'post_type' => $postTypes,
            'exclude' => $GLOBALS['post']->ID,
        ) );
        foreach ( $allPosts as $wPost ) {
            $postlist[$wPost->ID] = $wPost->post_title;
        }

        $this->optionFields['sliderLoadFrom']['options'] = $postlist;
    }

    /**
     * Create slider post-type
     */
    public function _createPostTypes() {
        register_post_type( 'postgalleryslider', array(
                'labels' => array(
                    'name' => __( 'Slider', $this->textdomain ),
                    'singular_name' => __( 'Slider', $this->textdomain ),
                ),
                'menu_icon' => 'dashicons-images-alt',
                'public' => true,
                'has_archiv' => false,
                'show_ui' => true,
                'capability_type' => 'post',
                'hierarchical' => false,
                'supports' => array( 'title' ),
                'exclude_from_search' => true,
                'publicly_queryable' => true,
                'excerpt' => false,
            )
        );
    }

    /**
     * Register meta-boxes
     *
     * @return bool
     */
    public function _registerPostSettings() {
        add_meta_box( 'post-gallery-slider-shortcode', __( 'Slider-Shortcode', $this->textdomain ), array( $this, '_addSliderShortcodeView' ), 'postgalleryslider' );
        add_meta_box( 'post-gallery-slider-settings', __( 'Slider-Settings', $this->textdomain ), array( $this, '_addSliderSettings' ), 'postgalleryslider' );

        return false;
    }

    /**
     * Show a metabox with the shortcode for slider
     *
     * @param $post
     */
    public function _addSliderShortcodeView( $post ) {
        echo '<input type="text" value="[slider ' . $post->ID . ']" onClick="this.select();" /> &nbsp;';
        echo '<a href="https://github.com/crazypsycho/post-gallery/blob/master/trunk/docs/slider.md" target="_blank">Slider Documentation</a><br />';
    }

    /**
     * Add meta-box with slider-options
     *
     * @param $post
     */
    public function _addSliderSettings( $post ) {
        $this->_setPostList();
        $this->createFields( $post, $this->optionFields );
    }

    /**
     * Creates fields from property optionFields
     */
    private function createFields( $post, $fields ) {
        echo '<table class="form-table">';

        if ( !empty( $fields ) ) {
            // Loop Post-Options and generate inputs
            foreach ( $fields as $key => $option ) {
                $trClass = !empty( $option['trClass'] ) ? $option['trClass'] : '';
                $inputClass = !empty( $option['inputClass'] ) ? $option['inputClass'] : '';

                $value = get_post_meta( $post->ID, $key, true );

                if ( !empty( $option['isJson'] ) ) {
                    $value = json_encode( $value );
                }

                echo '<tr valign="top" class="input-type-' . $option['type'] . ' ' . $trClass . '">';
                // Generate Label
                echo '<th scope="row"><label class="field-label" for="' . $key . '">' . $option['label'] . '</label></th>';
                echo '<td>';

                if ( !empty( $option['descTop'] ) ) {
                    echo $option['descTop'] . '<br />';
                }

                switch ( $option['type'] ) {
                    case 'select':
                        // Generate select
                        $multiple = !empty( $option['multiple'] ) ? ' multiple ' : '';
                        $selectKey = !empty( $option['multiple'] ) ? $key . '[]' : $key;
                        echo '<select class="field-input" name="' . $selectKey . '" ' . $inputClass . ' id="' . $key . '" ' . $multiple . '>';
                        if ( !empty( $option['options'] ) && is_array( $option['options'] ) ) {
                            foreach ( $option['options'] as $optionKey => $optionTitle ) {
                                $selected = '';
                                echo "\nk:" . $optionKey . '-' . $value;
                                if ( $optionKey == $value ||
                                    is_array( $value ) && in_array( $optionKey, $value )
                                ) {
                                    $selected = ' selected="selected"';
                                }
                                echo '<option value="' . $optionKey . '"' . $selected . '>' . $optionTitle . '</option>';
                            }
                        }
                        echo '</select>';
                        break;

                    case 'input':
                        // Generate text-input
                        echo '<input class="field-input ' . $inputClass . '" type="text" name="' . $key . '" id="' . $key . '" value="'
                            . $value . '" />';
                        break;

                    case 'checkbox':
                        // Generate checkbox
                        echo '<input class="field-input ' . $inputClass . '" type="checkbox" name="' . $key . '" id="' . $key . '" value="1" '
                            . ( !empty( $value ) ? 'checked="checked"' : '' ) . '" />';
                        break;

                    case 'textarea':
                        // Generate textarea
                        $cols = !empty( $option['cols'] ) ? ' cols="' . $option['cols'] . '"' : '';
                        $rows = !empty( $option['rows'] ) ? ' rows="' . $option['rows'] . '"' : '';
                        echo '<textarea class="field-input ' . $inputClass . '" name="' . $key . '" id="' . $key . '" ' . $rows . $cols . '>'
                            . $value .
                            '</textarea>';
                        break;

                    case 'background':
                        echo '<span class="sublabel">' . __( 'Image', $this->textdomain ) . '</span>
                                <input class="field-input ' . $inputClass . ' upload-field" type="hidden" name="' . $key . '-image" id="'
                            . $key . '-image" value=\''
                            . $value . '\' />
                            <input class="field-button ' . $inputClass . ' upload-button" type="button" name="' . $key . '-image-button" id="'
                            . $key . '-image-button" value=\''
                            . __( 'Select image', $this->textdomain ) . '\' />
                            <img src="" alt="" id="' . $key . '-image-img" class=" upload-preview-image" />
                            <br />';
                        echo '<span class="sublabel">' . __( 'Color', $this->textdomain ) . '</span>
                                <input class="field-input ' . $inputClass . '" type="text" name="' . $key . '-color" id="'
                            . $key . '-color" value=\''
                            . $value . '\' /><br />';
                        echo '<span class="sublabel">Repeat</span>
                                <input class="field-input ' . $inputClass . '" type="text" name="' . $key . '-repeat" id="'
                            . $key . '-repeat" value=\''
                            . $value . '\' /><br />';
                        echo '<span class="sublabel">Position</span>
                                <input class="field-input ' . $inputClass . '" type="text" name="' . $key . '-position" id="'
                            . $key . '-position" value=\''
                            . $value . '\' /><br />';
                        echo '<span class="sublabel">Size</span>
                                <input class="field-input ' . $inputClass . '" type="text" name="' . $key . '-size" id="'
                            . $key . '-size" value=\''
                            . $value . '\' /><br />';
                        break;

                    case 'hidden':
                    case 'number':
                    case 'text':
                        // Generate text-input
                        echo '<input class="field-input ' . $inputClass . '" type="' . $option['type'] . '" name="' . $key . '" id="' . $key . '" value=\''
                            . $value . '\' />';
                        break;
                }
                if ( !empty( $option['desc'] ) ) {
                    echo '<br />' . $option['desc'];
                }

                echo '</td></tr>';
            }
        }

        echo '</table>';
    }

    /**
     * Method to save Post-Meta
     *
     * @global type $post_options
     * @param type $postId
     * @param type $post
     * @return type
     */
    public function savePostMeta( $postId, $post ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( !filter_has_var( INPUT_POST, 'post_type' ) ) {
            return;
        }

        $postType = filter_input( INPUT_POST, 'post_type' );
        if ( $postType == 'page' ) {
            if ( !current_user_can( 'edit_page', $postId ) ) {
                return;
            }
        } else {
            if ( !current_user_can( 'edit_post', $postId ) ) {
                return;
            }
        }

        // Save form-fields
        if ( !empty( $this->optionFields ) ) {
            foreach ( $this->optionFields as $key => $postOption ) {
                if ( isset( $_POST[$key] ) && is_array( $_POST[$key] ) ) {
                    // multiselect
                    $value = array();
                    foreach ( $_POST[$key] as $aKey => $aValue ) {
                        $value[] = filter_var( $aValue );
                    }
                } else {
                    // single field
                    $value = filter_input( INPUT_POST, $key );
                }

                if ( !empty( $postOption['isJson'] ) ) {
                    $value = json_decode( $value );
                }
                update_post_meta( $postId, $key, $value );
            }
        }
    }
}
