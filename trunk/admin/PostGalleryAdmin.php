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

use Admin\SliderShortcodeAdmin;
use Admin\PostGalleryMceButton;
use Inc\PostGallery;
use MagicAdminPage\MagicAdminPage;
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

    private $defaultTemplates;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $pluginName The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct( $pluginName, $version ) {
        load_plugin_textdomain( $pluginName, false, '/post-gallery/languages' );
        $this->textdomain = $pluginName;
        $this->pluginName = $pluginName;
        $this->version = $version;

        $this->defaultTemplates = array(
            'thumbs' => __( 'Thumbs (150x150)', $this->textdomain ),
            'list' => __( 'List', $this->textdomain ),
            'slider' => __( 'Slider', $this->textdomain ),
            'slider-thumbs' => __( 'Slider with Thumbs', $this->textdomain ),
        );

        $postgalleryPage = new MagicAdminPage(
            'post-gallery',
            'PostGallery',
            'PostGallery',
            null,
            'dashicons-format-gallery'
        );

        $postgalleryPage->addFields( array(
            'mainSettings' => array(
                'type' => 'headline',
                'title' => __( 'Main-Settings', $this->textdomain ),
            ),

            'debugmode' => array(
                'type' => 'checkbox',
                'title' => __( 'Debug-Mode', $this->textdomain ),
                'default' => false,
            ),
            'useOldOwl' => array(
                'type' => 'checkbox',
                'title' => __( 'Use old owl-carousel (v1.3)', $this->textdomain ),
            ),

            'globalPosition' => array(
                'title' => __( 'Global position', $this->textdomain ),
                'type' => 'select',
                'options' => array(
                    'bottom' => __( 'bottom', $this->textdomain ),
                    'top' => __( 'top', $this->textdomain ),
                    'custom' => __( 'custom', $this->textdomain ),
                ),
            ),
            'globalTemplate' => array(
                'title' => __( 'Global template', $this->textdomain ),
                'type' => 'select',
                'options' => array_merge( $this->getCustomTemplates(), $this->defaultTemplates ),
            ),
            'stretchImages' => array(
                'title' => __( 'Stretch small images (for watermark)', $this->textdomain ),
                'type' => 'checkbox',
            ),


            'liteboxSettings' => array(
                'type' => 'headline',
                'title' => __( 'Litebox-Settings', $this->textdomain ),
            ),
            'enable' => array(
                'type' => 'checkbox',
                'title' => __( 'Enable', $this->textdomain ) . ' Litebox',
                'default' => true,
            ),
            'liteboxTemplate' => array(
                'type' => 'select',
                'default' => 'default',
                'title' => __( 'Litebox-Template', $this->textdomain ),
                'options' => $this->getLiteboxTemplates(),
            ),

            'liteboxOwlSettings' => array(
                'type' => 'headline',
                'title' => __( 'Litebox-Owl-Settings', $this->textdomain ),
            ),
            'owlTheme' => array(
                'type' => 'input',
                'default' => 'default',
                'title' => __( 'Owl-Theme', $this->textdomain ),
                'list' => 'postgallery-owl-theme',
                'options' => array( 'default', 'green' ),
            ),
            'clickEvents' => array(
                'type' => 'checkbox',
                'title' => __( 'Enable Click-Events', $this->textdomain ),
                'default' => true,
            ),
            'keyEvents' => array(
                'type' => 'checkbox',
                'title' => __( 'Enable Keypress-Events', $this->textdomain ),
                'default' => true,
            ),

            'owlConfig' => array(
                'type' => 'textarea',
                'title' => __( 'Owl-Litebox-Config', $this->textdomain ),
                'description' => '<b>' . __( 'Presets', $this->textdomain ) . '</b>:'
                    . '<select class="owl-slider-presets" data-lang="' . get_locale() . '" data-container="owlConfig">
                    <option value="">Slide (' . __( 'Default', $this->textdomain ) . ')</option>
                    <option value="fade">Fade</option>
                    <option value="slidevertical">SlideVertical</option>
                    <option value="zoominout">Zoom In/out</option>
                    </select>',
                'class' => 'owl-slider-config',
                'default' => 'items: 1,',
            ),

            'owlThumbConfig' => array(
                'type' => 'textarea',
                'title' => __( 'Owl-Config for Thumbnail-Slider', $this->textdomain ),
                'description' => '<b>' . __( 'Presets', $this->textdomain ) . '</b>:'
                    . '<select class="owl-slider-presets" data-lang="' . get_locale() . '" data-container="owlThumbConfig">
                    <option value="">Slide (' . __( 'Default', $this->textdomain ) . ')</option>
                    <option value="fade">Fade</option>
                    <option value="slidevertical">SlideVertical</option>
                    <option value="zoominout">Zoom In/out</option>
                    </select>',
                'class' => 'owl-slider-config',
            ),

            'owlDesc' => array(
                'type' => 'description',
                'title' => __( 'Description', $this->textdomain ),
                'description' => __( 'You can use these options', $this->textdomain ) . ':<br />' .
                    '<a href="http://www.owlcarousel.owlgraphic.com/docs/api-options.html" target="_blank">
							OwlCarousel Options
						</a>
						<br />' .
                    __( 'You can use these animations', $this->textdomain ) . ':<br />
						<a href="http://daneden.github.io/animate.css/" target="_blank">
							Animate.css
						</a>
					</div>',
            ),
        ) );


        new SliderShortcodeAdmin( $pluginName, $version );
        new PostGalleryMceButton( $pluginName );

        add_action( 'add_meta_boxes', array( $this, 'registerPostSettings' ) );
        add_action( 'save_post', array( $this, 'savePostMeta' ), 10, 2 );

        // Register ajax
        add_action( 'wp_ajax_postgalleryUpload', array( $this, 'ajaxUpload' ) );
        add_action( 'wp_ajax_postgalleryDeleteimage', array( $this, 'ajaxDelete' ) );
    }


    /**
     * Returns an array with all templates for litebox
     *
     * @return array
     */
    public function getLiteboxTemplates() {
        $templateList = array(
            'default' => __( 'Default', $this->textdomain ),
            'default-with-thumbs' => __( 'Default with thumbs', $this->textdomain ),
            'default-with-thumbs-vertical' => __( 'Default with thumbs', $this->textdomain ) . ' - Vertical',
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
        wp_enqueue_script( $this->pluginName . '-fineuploader', $pgUrl . 'js/fileuploader.js', array( 'jquery' ), $this->version, false );
        wp_enqueue_script( $this->pluginName . '-uploadhandler', $pgUrl . 'js/upload-handler.js', array( 'jquery' ), $this->version, false );

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


    /**
     * Register the Metaboxes for Gallery-Settings and Images
     *
     * @return boolean
     */
    public function registerPostSettings() {
        $postTypes = get_post_types();
        foreach ( $postTypes as $postType ) {
            if ( $postType !== 'postgalleryslider' ) {
                add_meta_box( 'post-gallery-settings', __( 'Gallery-Settings', $this->textdomain ), array( $this, 'addGallerySettings' ), $postType );
            }
            add_meta_box( 'post-gallery-pictures', __( 'Gallery-Pictures', $this->textdomain ), array( $this, 'addGalleryPictures' ), $postType );
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
        $customTemplateDirs = array( get_stylesheet_directory() . '/post-gallery', get_stylesheet_directory() . '/plugins/post-gallery' );

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
        $altAttributes = get_post_meta( $currentLangPost->ID, 'postgalleryAltAttributes', true );

        if ( empty( $imageDir ) ) {
            echo __( 'You have to save the post to upload images.', $this->textdomain );
            return;
        }

        echo '
			<div class="imageupload-image" data-uploadfolder="' . $imageDir . '" data-pluginurl="' . WP_PLUGIN_URL . '/post-gallery' . '"></div>
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

        $scriptLanguage = array(
            'moveHere' => __( 'Move files here.', $this->textdomain ),
            'askDeleteAll' => __( 'Are you sure you want to delete all pictures?', $this->textdomain ),
        );

        // Javascript for language
        echo '<script type="text/javascript">window.postgalleryLang = ' . json_encode( $scriptLanguage ) . ';</script>';
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
                update_post_meta( $curLangPostId, $key, filter_input( INPUT_POST, $key ) );
            }
        }
        // Save templates
        if ( filter_has_var( 'postgalleryTemplate' ) ) {
            update_post_meta( $curLangPostId, 'postgalleryTemplate', filter_input( INPUT_POST, 'postgalleryTemplate' ) );
        }
        // save sort
        if ( filter_has_var( 'postgalleryImagesort' ) ) {
            update_post_meta( $postId, 'postgalleryImagesort', filter_input( INPUT_POST, 'postgalleryImagesort' ) );
        }
        // save image titles
        if ( filter_has_var( 'postgalleryTitles' ) ) {
            update_post_meta( $postId, 'postgalleryTitles', filter_input( INPUT_POST, 'postgalleryTitles' ) );
        }
        // save image descriptions
        if ( filter_has_var( 'postgalleryDescs' ) ) {
            update_post_meta( $postId, 'postgalleryDescs', filter_input( INPUT_POST, 'postgalleryDescs' ) );
        }
        // save image alt
        if ( filter_has_var( 'postgalleryAltAttributes' ) ) {
            update_post_meta( $postId, 'postgalleryAltAttributes', filter_input( INPUT_POST, 'postgalleryAltAttributes' ) );
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
}
