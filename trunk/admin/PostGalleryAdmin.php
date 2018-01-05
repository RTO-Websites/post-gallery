<?php namespace Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/crazypsycho
 * @since      1.0.0
 *
 * @package    PostGallery
 * @subpackage PostGallery/admin
 */

include_once( 'PostGalleryThemeCustomizer.php' );

use Admin\SliderShortcodeAdmin;
use Admin\PostGalleryMceButton;
use Inc\PostGallery;
use Thumb\Thumb;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    PostGallery
 * @subpackage PostGallery/admin
 * @author     crazypsycho <info@hennewelt.de>
 */
class PostGalleryAdmin {

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
     * @var      string $version The current version of this plugin.
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

    public $defaultTemplates;

    private $optionFields = null;

    private static $instance;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $pluginName The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct( $pluginName, $version ) {
        load_plugin_textdomain( $pluginName, false, '/postgallery/languages' );
        $this->textdomain = $pluginName;
        $this->pluginName = $pluginName;
        $this->version = $version;
        self::$instance = $this;

        $this->optionFields = array( 'postgalleryPosition' => array(
            'label' => __( 'Position', 'post-gallery' ),
            'type' => 'select',
            'value' => array(
                'global' => __( 'From global setting', $this->textdomain ),
                'bottom' => __( 'bottom', $this->textdomain ),
                'top' => __( 'top', $this->textdomain ),
                'custom' => __( 'custom', $this->textdomain ),
            ),
        ) );

        $this->defaultTemplates = array(
            'thumbs' => __( 'Thumb-List', $this->textdomain ),
            'slider' => __( 'Slider (with Owl-Carousel)', $this->textdomain ),
        );


        // add options to customizer
        add_action( 'customize_register', array( new \PostGalleryThemeCustomizer(), 'actionCustomizeRegister' ) );

        // add menu page to link to customizer
        add_action( 'admin_menu', function () {
            $returnUrl = urlencode( $_SERVER['REQUEST_URI'] );
            \add_menu_page(
                'PostGallery',
                'PostGallery',
                'edit_theme_options',
                'customize.php?return=' . $returnUrl . '&autofocus[panel]=postgallery-panel',
                null,
                'dashicons-format-gallery'
            );
        } );

        new SliderShortcodeAdmin( $pluginName, $version );
        new PostGalleryMceButton( $pluginName );

        add_action( 'add_meta_boxes', array( $this, 'registerPostSettings' ) );
        add_action( 'save_post', array( $this, 'savePostMeta' ), 10, 2 );

        // Register ajax
        add_action( 'wp_ajax_postgalleryUpload', array( $this, 'ajaxUpload' ) );
        add_action( 'wp_ajax_postgalleryDeleteimage', array( $this, 'ajaxDelete' ) );
        add_action( 'wp_ajax_postgalleryGetImageUpload', array( $this, 'ajaxGetImageUpload' ) );
        add_action( 'wp_ajax_postgalleryNewGallery', array( $this, 'ajaxCreateGallery' ) );

    }

    /**
     * Returns an array with all templates for litebox
     *
     * @return array
     */
    public function getLiteboxTemplates() {
        $templateList = array(
            'default-with-thumbs' => __( 'Default with thumbs', $this->textdomain ),
            'default' => __( 'Default', $this->textdomain ),
        );

        $customTemplates = array();

        $customTplPaths = array( get_stylesheet_directory() . '/litebox', get_stylesheet_directory() . '/plugins/litebox' );

        foreach ( $customTplPaths as $customTplPath ) {
            $customTplFiles = ( file_exists( $customTplPath ) ? scandir( $customTplPath ) : array() );
            foreach ( $customTplFiles as $file ) {
                if ( !is_dir( $customTplPath . '/' . $file ) ) {
                    $optionKey = str_replace( '.php', '', $file );
                    $option_title = ucfirst( str_replace( '_', ' ', $optionKey ) ) . __( ' (from Theme)', $this->textdomain );

                    $customTemplates[$optionKey] = $option_title;
                }
            }
        }
        $templateList = array_merge( $customTemplates, $templateList );

        return $templateList;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueueStyles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in PostGalleryLoader as all of the hooks are defined
         * in that particular class.
         *
         * The PostGalleryLoader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->pluginName, plugin_dir_url( __FILE__ ) . 'css/post-gallery-admin.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueueScripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in PostGalleryLoader as all of the hooks are defined
         * in that particular class.
         *
         * The PostGalleryLoader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        $pgUrl = plugin_dir_url( __FILE__ );

        wp_enqueue_script( $this->pluginName, $pgUrl . 'js/post-gallery-admin.js', array( 'jquery' ), $this->version, false );
        wp_enqueue_script( $this->pluginName . '-elementor', $pgUrl . 'js/post-gallery-elementor-admin.js', array( 'jquery' ), $this->version, false );
        wp_enqueue_script( $this->pluginName . '-fineuploader', $pgUrl . 'js/fileuploader.js', array( 'jquery' ), $this->version, false );
        wp_enqueue_script( $this->pluginName . '-uploadhandler', $pgUrl . 'js/upload-handler.js', array( 'jquery' ), $this->version, false );

        wp_localize_script( $this->pluginName, 'postgalleryLang', $this->getPostGalleryLang() );
    }


    /**
     * Admin-ajax for image upload
     */
    public function ajaxUpload() {
        include( POSTGALLERY_DIR . '/includes/imageupload.inc.php' );
        exit();
    }

    /**
     * Admin-ajax for image delete
     */
    public function ajaxDelete() {
        include( POSTGALLERY_DIR . '/includes/deleteimage.inc.php' );
        exit();
    }


    public function ajaxGetImageUpload() {
        $postid = filter_input( INPUT_GET, 'post' );
        $post = get_post( $postid );
        $this->addGalleryPictures( $post );
        exit();
    }

    /**
     * Create new gallery via ajax
     */
    public function ajaxCreateGallery() {
        global $user_ID;
        $title = filter_input( INPUT_GET, 'title' );
        $new_post = array(
            'post_title' => $title,
            'post_content' => '',
            'post_status' => 'publish',
            'post_date' => date( 'Y-m-d H:i:s' ),
            'post_author' => $user_ID,
            'post_type' => 'gallery',
            'post_category' => array( 0 ),
        );
        $post_id = wp_insert_post( $new_post );

        echo json_encode( get_post( $post_id ) );
        exit();
    }

    /**
     * Register the Metaboxes for Gallery-Settings and Images
     *
     * @return boolean
     */
    public function registerPostSettings() {
        $postTypes = get_post_types();
        foreach ( $postTypes as $postType ) {
            add_meta_box( 'post-gallery-pictures', __( 'Gallery-Pictures', $this->textdomain ), array( $this, 'addGalleryPictures' ), $postType, 'normal', 'high' );
            if ( $postType !== 'postgalleryslider' ) {
                add_meta_box( 'post-gallery-settings', __( 'Gallery-Settings', $this->textdomain ), array( $this, 'addGallerySettings' ), $postType, 'normal', 'high' );
            }
        }
        return false;
    }

    /**
     * Add a metabox for gallery-settings (position, template)
     *
     * @param type $post
     */
    public function addGallerySettings( $post ) {
        $orgPost = PostGallery::getOrgPost( $post->ID );
        $curLangPost = $post;
        if ( !empty( $orgPost ) ) {
            $post = $orgPost;
        }

        echo '<table class="form-table">';

        if ( !empty( $this->optionFields ) ) {
            // Loop Post-Options and generate inputs
            foreach ( $this->optionFields as $key => $option ) {
                echo '<tr valign="top">';
                // Generate Label
                echo '<th scope="row"><label class="theme_options_label" for="' . $key . '">' . $option['label'] . '</label></th>';
                echo '<td>';

                switch ( $option['type'] ) {
                    case 'select':
                        // Generate select
                        echo '<select class="theme_options_input" name="' . $key . '" id="' . $key . '">';
                        if ( !empty( $option['value'] ) && is_array( $option['value'] ) ) {
                            foreach ( $option['value'] as $optionKey => $optionTitle ) {
                                $selected = '';
                                //echo '<br/>Key'.$option_key.'-'.get_post_meta($post->ID, $key, true);
                                if ( $optionKey == get_post_meta( $curLangPost->ID, $key, true ) ) {
                                    $selected = ' selected="selected"';
                                }
                                echo '<option value="' . $optionKey . '"' . $selected . '>' . $optionTitle . '</option>';
                            }
                        }
                        echo '</select>';
                        break;
                }
                echo '</td></tr>';
            }
        }

        // Template list
        echo '<tr valign="top">';
        $key = 'postgalleryTemplate';
        echo '<th scope="row"><label class="field-label" for="' . $key . '">' . __( 'Template', $this->textdomain ) . '</label></th>';
        echo '<td>';
        echo '<select class="field-input" name="' . $key . '" id="' . $key . '">';

        echo '<option value="global">' . __( 'From global setting', $this->textdomain ) . '</option>';
        // get templates from tpl-dir
        $this->getCustomTemplateDirOptions( get_post_meta( $curLangPost->ID, $key, true ) );

        // get templates from plugin
        $currentValue = get_post_meta( $curLangPost->ID, $key, true );
        $this->getPluginDirOptions( $currentValue );

        echo '</select></td></tr>';


        echo '</table>';
    }

    /**
     * Print the template-options from template_dir
     */
    public function getCustomTemplateDirOptions( $currentValue = '' ) {
        $templates = $this->getCustomTemplates();
        foreach ( $templates as $key => $title ) {
            $selected = '';
            if ( $key == $currentValue ) {
                $selected = ' selected="selected"';
            }
            echo '<option value="' . $key . '"' . $selected . '>' . $title . '</option>';
        }
    }

    /**
     * Returns an array with all templates from template-dir
     *
     */
    public function getCustomTemplates() {
        $output = array();
        // scan theme-dir for templates
        $customTemplateDirs = array(
            get_stylesheet_directory() . '/post-gallery',
            get_stylesheet_directory() . '/plugins/post-gallery',
            get_stylesheet_directory() . '/postgallery',
        );

        foreach ( $customTemplateDirs as $customTemplateDir ) {
            if ( file_exists( $customTemplateDir ) && is_dir( $customTemplateDir ) ) {
                $dir = scandir( $customTemplateDir );
                foreach ( $dir as $file ) {
                    if ( !is_dir( $customTemplateDir . '/' . $file ) ) {
                        $file = str_replace( '.php', '', $file );
                        $title = ucfirst( str_replace( '_', ' ', $file ) ) . __( ' (from Theme)', $this->textdomain );
                        $output[$file] = $title;
                    }
                }
            }
        }

        return $output;
    }

    /**
     * Print the template-options from plugin_dir
     *
     * @param type $cur_lang_post
     */
    public function getPluginDirOptions( $currentValue = '' ) {
        // list default-gallery-templates
        foreach ( $this->defaultTemplates as $optionKey => $optionTitle ) {
            $selected = '';

            if ( $optionKey == $currentValue ) {
                $selected = ' selected="selected"';
            }
            echo '<option value="' . $optionKey . '"' . $selected . '>' . $optionTitle . '</option>';
        }
    }

    /**
     * Adds a metabox with the gallery-pictures and a file-upload
     *
     * @param type $post
     */
    public function addGalleryPictures( $post ) {
        $orgPost = PostGallery::getOrgPost( $post->ID );
        $currentLangPost = $post;

        if ( !empty( $orgPost ) ) {
            $post = $orgPost;
        }
        //$imageDir = strtolower(str_replace('http://', '', esc_url($post->post_title)));
        $imageDir = PostGallery::getImageDir( $post );
        $uploads = wp_upload_dir();
        $uploadDir = $uploads['basedir'] . '/gallery/' . $imageDir;
        $uploadUrl = $uploads['baseurl'] . '/gallery/' . $imageDir;
        $uploadUrl = str_replace( get_bloginfo( 'wpurl' ), '', $uploadUrl );
        $sort = get_post_meta( $currentLangPost->ID, 'postgalleryImagesort', true );

        // Create folders if not exists
        if ( !file_exists( $uploads['basedir'] . '/cache' ) ) {
            @mkdir( $uploads['basedir'] . '/cache' );
            @chmod( $uploads['basedir'] . '/cache', octdec( '0777' ) );
        }
        if ( !file_exists( $uploads['basedir'] . '/gallery' ) ) {
            @mkdir( $uploads['basedir'] . '/gallery' );
            @chmod( $uploads['basedir'] . '/gallery', octdec( '0777' ) );
        }
        if ( !file_exists( $uploadDir ) ) {
            @mkdir( $uploadDir );
            @chmod( $uploadDir, octdec( '0777' ) );
        }

        // Load image titles and description
        $titles = get_post_meta( $currentLangPost->ID, 'postgalleryTitles', true );
        $descs = get_post_meta( $currentLangPost->ID, 'postgalleryDescs', true );
        $imageOptions = get_post_meta( $currentLangPost->ID, 'postgalleryImageOptions', true );
        $altAttributes = get_post_meta( $currentLangPost->ID, 'postgalleryAltAttributes', true );


        if ( !is_array( $titles ) ) {
            $titles = json_decode( json_encode( $titles ), true );
        }
        if ( !is_array( $descs ) ) {
            $descs = json_decode( json_encode( $descs ), true );
        }
        if ( !is_array( $altAttributes ) ) {
            $altAttributes = json_decode( json_encode( $altAttributes ), true );
        }
        if ( !is_array( $imageOptions ) ) {
            $imageOptions = json_decode( json_encode( $imageOptions ), true );
        }

        if ( empty( $imageDir ) ) {
            echo __( 'You have to save the post to upload images.', $this->textdomain );
            return;
        }

        echo '
			<div class="imageupload-image" data-uploadfolder="' . $imageDir . '" data-pluginurl="' . POSTGALLERY_URL . '"></div>
			<div class="postgallery-upload-error"></div>
		';;

        $images = array();
        if ( file_exists( $uploadDir ) && is_dir( $uploadDir ) ) {
            $thumbInstance = Thumb::getInstance();
            $dir = scandir( $uploadDir );

            echo '<div class="postgallery-del-button button" onclick="deleteImages(\'' . $imageDir . '\');">Alle löschen</div>';

            echo '<ul class="sortable-pics">';

            if ( !empty( $dir ) ) {
                foreach ( $dir as $file ) {
                    if ( !is_dir( $uploadDir . '/' . $file ) ) {
                        $thumb = $thumbInstance->getThumb( array(
                            'path' => $uploadUrl . '/' . $file,
                            'width' => 150,
                            'height' => 150,
                            'scale' => 0,
                        ) );

                        $images[$file] = '<li>';
                        $images[$file] .= '<img style="" data-src="' . $file . '" src="' . $thumb['url'] . '" alt="" />';
                        $images[$file] .= '<div class="img-title">' . $file . '</div>';
                        $images[$file] .= '<div class="del" onclick="deleteImage(this.parentNode, \'' . $imageDir . '/' . $file . '\');">x</div>';
                        $images[$file] .= '<div class="edit-details" onclick="pgToggleDetails(this);"></div>';
                        $images[$file] .= '<div class="details">';
                        $images[$file] .= '<div class="title"><input type="text" placeholder="' . __( 'Title' ) . '" name="postgalleryTitles[' . $file . ']" value="' . ( !empty( $titles[$file] ) ? $titles[$file] : '' ) . '" /></div>';
                        $images[$file] .= '<div class="desc"><textarea placeholder="' . __( 'Description' ) . '" name="postgalleryDescs[' . $file . ']">' . ( !empty( $descs[$file] ) ? $descs[$file] : '' ) . '</textarea></div>';
                        $images[$file] .= '<div class="image-options"><textarea placeholder="' . __( 'Options' ) . '" name="postgalleryImageOptions[' . $file . ']">' . ( !empty( $imageOptions[$file] ) ? $imageOptions[$file] : '' ) . '</textarea></div>';
                        $images[$file] .= '<div class="alt-attribute"><input type="text" placeholder="' . __( 'Alt-Attribut' ) . '" name="postgalleryAltAttributes[' . $file . ']" value="' . ( !empty( $altAttributes[$file] ) ? $altAttributes[$file] : '' ) . '" /></div>';
                        $images[$file] .= '</div>';
                        $images[$file] .= '</li>';
                    }
                }
                $sortimages = PostGallery::sortImages( $images, $post->ID );
                echo implode( '', $sortimages );
            }

            echo '</ul>';
        }

        // hidden-input contains the image-order
        echo '<input type="hidden" name="postgalleryImagesort" id="postgalleryImagesort" value="' . $sort . '" />';

        // hidden-input contains the current-slug
        echo '<input type="hidden" name="currentImagedir" value="' . $imageDir . '" />';

        // hidden-input contains the id of main-lang-post
        echo '<input type="hidden" name="postgalleryMainlangId" id="postgalleryMainlangId" value="' . $post->ID . '" />';
    }


    /**
     * @return array
     */
    public function getPostGalleryLang() {
        $scriptLanguage = array(
            'moveHere' => __( 'Move files here.', $this->textdomain ),
            'askDeleteAll' => __( 'Are you sure you want to delete all pictures?', $this->textdomain ),
        );

        // Javascript for language
        return $scriptLanguage;
        //return '<script type="text/javascript">window.postgalleryLang = ' . json_encode( $scriptLanguage ) . ';</script>';
    }

    /**
     * Method to save Post-Meta
     *
     * @global type $post_options
     * @param int $postId
     * @param object $post
     * @return null
     */
    public function savePostMeta( $postId, $post ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( !filter_has_var( INPUT_POST, 'post_type' ) ) {
            return;
        }

        $curLangPost = $post;
        $curLangPostId = $postId;


        $postgalleryMainlangId = filter_input( INPUT_POST, 'postgalleryMainlangId' );
        if ( !empty( $postgalleryMainlangId ) && $postId !== $postgalleryMainlangId ) {
            $postId = $postgalleryMainlangId;
            $post = get_post( $postId );
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
                if ( !filter_has_var( INPUT_POST, $key ) ) {
                    continue;
                }
                update_post_meta( $curLangPostId, $key, filter_input( INPUT_POST, $key ) );
            }
        }
        // Save templates
        if ( filter_has_var( INPUT_POST, 'postgalleryTemplate' ) ) {
            update_post_meta( $curLangPostId, 'postgalleryTemplate', filter_input( INPUT_POST, 'postgalleryTemplate' ) );
        }
        // save sort
        if ( filter_has_var( INPUT_POST, 'postgalleryImagesort' ) ) {
            update_post_meta( $postId, 'postgalleryImagesort', filter_input( INPUT_POST, 'postgalleryImagesort' ) );
        }
        // save image titles
        if ( filter_has_var( INPUT_POST, 'postgalleryTitles' ) ) {
            update_post_meta( $postId, 'postgalleryTitles', filter_input( INPUT_POST, 'postgalleryTitles', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) );
        }
        // save image descriptions
        if ( filter_has_var( INPUT_POST, 'postgalleryDescs' ) ) {
            update_post_meta( $postId, 'postgalleryDescs', filter_input( INPUT_POST, 'postgalleryDescs', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) );
        }
        // save image alt
        if ( filter_has_var( INPUT_POST, 'postgalleryAltAttributes' ) ) {
            update_post_meta( $postId, 'postgalleryAltAttributes', filter_input( INPUT_POST, 'postgalleryAltAttributes', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) );
        }
        // save image alt
        if ( filter_has_var( INPUT_POST, 'postgalleryImageOptions' ) ) {
            update_post_meta( $postId, 'postgalleryImageOptions', filter_input( INPUT_POST, 'postgalleryImageOptions', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) );
        }

        $imageDir = PostGallery::getImageDir( $post );
        $currentImageDir = filter_input( INPUT_POST, 'currentImagedir' );

        // if post-title change, then move pictures
        if ( !empty( $currentImageDir ) && $currentImageDir !== $imageDir ) {
            $uploads = wp_upload_dir();
            $uploadDir = $uploads['basedir'] . '/gallery/' . $currentImageDir;
            $uploadDirNew = $uploads['basedir'] . '/gallery/' . $imageDir;
            if ( file_exists( $uploadDir ) ) {
                $files = scandir( $uploadDir );
                @mkdir( $uploadDirNew );
                @chmod( $uploadDirNew, octdec( '0777' ) );

                foreach ( $files as $file ) {
                    if ( !is_dir( $uploadDir . '/' . $file ) ) {
                        copy( $uploadDir . '/' . $file, $uploadDirNew . '/' . $file );
                        unlink( $uploadDir . '/' . $file );
                    }
                }
                @rmdir( $uploadDir );
            }
        }
    }


    static function getInstance() {
        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}
