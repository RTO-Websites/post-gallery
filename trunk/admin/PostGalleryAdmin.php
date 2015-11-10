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
class PostGalleryAdmin
{

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

    private $default_templates;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $pluginName The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct( $pluginName, $version )
    {
        load_plugin_textdomain( 'post-gallery', false, '/post-gallery/languages' );
        $this->textdomain = 'post-gallery';

        $this->pluginName = $pluginName;
        $this->version = $version;

        $this->default_templates = array (
            'thumbs'        => __( 'Thumbs (150x150)', $this->textdomain ),
            'list'          => __( 'List', $this->textdomain ),
            'slider'        => __( 'Slider', $this->textdomain ),
            'slider-thumbs' => __( 'Slider with Thumbs', $this->textdomain )
        );

        $postgalleryPage = new MagicAdminPage(
            'post-gallery',
            'PostGallery',
            'PostGallery'
        );

        $postgalleryPage->addFields( array (
            'main_settings' => array(
                'type' => 'headline',
                'title' => __('Main-Settings', $this->textdomain)
            ),

            'debugmode'        => array (
                'type'    => 'checkbox',
                'title'   => __( 'Debug-Mode', $this->textdomain ),
                'default' => false,
            ),
            'useOldOwl'   => array (
                'type'    => 'checkbox',
                'title'   => __( 'Use old owl-carousel (v1.3)', $this->textdomain ),
            ),

            'global_position'  => array (
                'title'   => __( 'Global position', $this->textdomain ),
                'type'    => 'select',
                'options' => array (
                    'bottom' => __( 'bottom', $this->textdomain ),
                    'top'    => __( 'top', $this->textdomain ),
                    'custom' => __( 'custom', $this->textdomain )
                )
            ),
            'global_template'  => array (
                'title'   => __( 'Global template', 'post-gallery' ),
                'type'    => 'select',
                'options' => array_merge( $this->get_custom_templates(), $this->default_templates )
            ),
            'stretch_images'   => array (
                'title' => __( 'Stretch small images (for watermark)', $this->textdomain ),
                'type'  => 'checkbox',
            ),


            'litebox_settings' => array(
                'type' => 'headline',
                'title' => __('Litebox-Settings', $this->textdomain)
            ),
            'enable'           => array (
                'type'    => 'checkbox',
                'title'   => __( 'Enable', 'post-gallery' ) . ' Litebox',
                'default' => true
            ),
            'litebox-template' => array (
                'type'    => 'select',
                'default' => 'default',
                'title'   => __( 'Litebox-Template', $this->textdomain ),
                'options' => $this->getLiteboxTemplates()
            ),

            'litebox_owl_settings' => array(
                'type' => 'headline',
                'title' => __('Litebox-Owl-Settings', $this->textdomain)
            ),
            'owlTheme' => array (
                'type'    => 'input',
                'default' => 'default',
                'title'   => __( 'Owl-Theme', $this->textdomain ),
                'list' => 'postgallery-owl-theme',
                'options' => array('default', 'green')
            ),
            'clickEvents'           => array (
                'type'    => 'checkbox',
                'title'   => __( 'Enable Click-Events', 'post-gallery' ),
                'default' => true
            ),
            'keyEvents'           => array (
                'type'    => 'checkbox',
                'title'   => __( 'Enable Keypress-Events', 'post-gallery' ),
                'default' => true
            ),

            'owlConfig'        => array (
                'type'        => 'textarea',
                'title'       => __( 'Owl-Litebox-Config', $this->textdomain ),
                'description' => '<b>' . __( 'Presets', $this->textdomain ) . '</b>:'
                    . '<select class="owl-slider-presets" data-lang="' . get_locale() . '" data-container="owlConfig">
                    <option value="">Slide (' . __( 'Default', $this->textdomain ) . ')</option>
                    <option value="fade">Fade</option>
                    <option value="slidevertical">SlideVertical</option>
                    <option value="zoominout">Zoom In/out</option>
                    </select>',
                'class'       => 'owl-slider-config',
                'default'     => 'items: 1,',
            ),

            'owlThumbConfig'        => array (
                'type'        => 'textarea',
                'title'       => __( 'Owl-Config for Thumbnail-Slider', $this->textdomain ),
                'description' => '<b>' . __( 'Presets', $this->textdomain ) . '</b>:'
                    . '<select class="owl-slider-presets" data-lang="' . get_locale() . '" data-container="owlThumbConfig">
                    <option value="">Slide (' . __( 'Default', $this->textdomain ) . ')</option>
                    <option value="fade">Fade</option>
                    <option value="slidevertical">SlideVertical</option>
                    <option value="zoominout">Zoom In/out</option>
                    </select>',
                'class'       => 'owl-slider-config',
            ),

            'owlDesc'          => array (
                'type'        => 'description',
                'title'       => __( 'Description', $this->textdomain ),
                'description' => __( 'You can use these options', $this->textdomain ) . ':<br />' .
                    '<a href="http://www.owlcarousel.owlgraphic.com/docs/api-options.html" target="_blank">
							OwlCarousel Options
						</a>
						<br />' .
                    __( 'You can use these animations', $this->textdomain ) . ':<br />
						<a href="http://daneden.github.io/animate.css/" target="_blank">
							Animate.css
						</a>
					</div>'
            ),
        ) );

        add_action( 'add_meta_boxes', array ( $this, 'register_post_settings' ) );
        add_action( 'save_post', array ( $this, 'save_post_meta' ), 10, 2 );

        // Register ajax
        add_action( 'wp_ajax_postgallery_upload', array ( $this, 'ajax_upload' ) );
        add_action( 'wp_ajax_postgallery_deleteimage', array ( $this, 'ajax_delete' ) );
    }


    /**
     * Returns an array with all templates for litebox
     *
     * @return array
     */
    public function getLiteboxTemplates()
    {
        $template_list = array (
            'default'                      => __( 'Default', $this->textdomain ),
            'default-with-thumbs'          => __( 'Default with thumbs', $this->textdomain ),
            'default-with-thumbs-vertical' => __( 'Default with thumbs', $this->textdomain ) . ' - Vertical',
        );

        $custom_templates = array ();

        $custom_tpl_paths = array ( get_stylesheet_directory() . '/litebox', get_stylesheet_directory() . '/plugins/litebox' );

        foreach ( $custom_tpl_paths as $custom_tpl_path ) {
            $custom_tpl_files = ( file_exists( $custom_tpl_path ) ? scandir( $custom_tpl_path ) : array () );
            foreach ( $custom_tpl_files as $file ) {
                if ( !is_dir( $custom_tpl_path . '/' . $file ) ) {
                    $option_key = str_replace( '.php', '', $file );
                    $option_title = ucfirst( str_replace( '_', ' ', $option_key ) ) . __( ' (from Theme)', $this->textdomain );

                    $custom_templates[ $option_key ] = $option_title;
                }
            }
        }
        $template_list = array_merge( $custom_templates, $template_list );

        return $template_list;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueueStyles()
    {

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

        wp_enqueue_style( $this->pluginName, plugin_dir_url( __FILE__ ) . 'css/post-gallery-admin.css', array (), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueueScripts()
    {

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

        wp_enqueue_script( $this->pluginName, $pgUrl . 'js/post-gallery-admin.js', array ( 'jquery' ), $this->version, false );
        wp_enqueue_script( $this->pluginName . '-fineuploader', $pgUrl . 'js/fileuploader.js', array ( 'jquery' ), $this->version, false );
        wp_enqueue_script( $this->pluginName . '-uploadhandler', $pgUrl . 'js/upload_handler.js', array ( 'jquery' ), $this->version, false );

    }


    /**
     * Admin-ajax for image upload
     */
    public function ajax_upload()
    {
        include( POSTGALLERY_DIR . '/includes/imageupload.inc.php' );
        exit();
    }

    /**
     * Admin-ajax for image delete
     */
    public function ajax_delete()
    {
        include( POSTGALLERY_DIR . '/includes/deleteimage.inc.php' );
        exit();
    }


    /**
     * Register the Metaboxes for Gallery-Settings and Images
     *
     * @return boolean
     */
    public function register_post_settings()
    {
        $post_types = get_post_types();
        foreach ( $post_types as $post_type ) {
            add_meta_box( 'post-gallery-settings', __( 'Gallery-Settings', 'post-gallery' ), array ( $this, 'add_gallery_settings' ), $post_type );
            add_meta_box( 'post-gallery-pictures', __( 'Gallery-Pictures', 'post-gallery' ), array ( $this, 'add_gallery_pictures' ), $post_type );
        }
        return false;
    }

    /**
     * Add a metabox for gallery-settings (position, template)
     *
     * @param type $post
     */
    public function add_gallery_settings( $post )
    {
        $org_post = PostGallery::get_org_post( $post->ID );
        $cur_lang_post = $post;
        if ( !empty( $org_post ) ) {
            $post = $org_post;
        }

        echo '<table class="form-table">';

        if ( !empty( $this->option_fields ) ) {
            // Loop Post-Options and generate inputs
            foreach ( $this->option_fields as $key => $option ) {
                echo '<tr valign="top">';
                // Generate Label
                echo '<th scope="row"><label class="theme_options_label" for="' . $key . '">' . $option[ 'label' ] . '</label></th>';
                echo '<td>';
                switch ( $option[ 'type' ] ) {
                    case 'select':
                        // Generate select
                        echo '<select class="theme_options_input" name="' . $key . '" id="' . $key . '">';
                        if ( !empty( $option[ 'value' ] ) && is_array( $option[ 'value' ] ) ) {
                            foreach ( $option[ 'value' ] as $option_key => $option_title ) {
                                $selected = '';
                                //echo '<br/>Key'.$option_key.'-'.get_post_meta($post->ID, $key, true);
                                if ( $option_key == get_post_meta( $cur_lang_post->ID, $key, true ) ) {
                                    $selected = ' selected="selected"';
                                }
                                echo '<option value="' . $option_key . '"' . $selected . '>' . $option_title . '</option>';
                            }
                        }
                        echo '</select>';
                        break;

                    case 'input':
                        // Generate text-input
                        echo '<input class="theme_options_input" type="text" name="' . $key . '" id="' . $key . '" value="' . get_post_meta( $cur_lang_post->ID, $key, true ) . '" />';
                        break;

                    case 'textarea':
                        // Generate textarea
                        echo '<textarea class="theme_options_input" name="' . $key . '" id="' . $key . '">' . get_post_meta( $cur_lang_post->ID, $key, true ) . '</textarea>';
                        break;
                }
                echo '</td></tr>';
            }
        }

        // Template list
        echo '<tr valign="top">';
        $key = 'postgallery_template';
        echo '<th scope="row"><label class="theme_options_label" for="' . $key . '">' . __( 'Template', 'post-gallery' ) . '</label></th>';
        echo '<td>';
        echo '<select class="theme_options_input" name="' . $key . '" id="' . $key . '">';

        echo '<option value="global">' . __( 'From global setting', 'post-gallery' ) . '</option>';
        // get templates from tpl-dir
        $this->get_custom_template_dir_options( get_post_meta( $cur_lang_post->ID, $key, true ) );

        // get templates from plugin
        $current_value = get_post_meta( $cur_lang_post->ID, $key, true );
        if ( empty( $current_value ) ) {
            // for compatibility with old version
            $current_value = get_post_meta( $cur_lang_post->ID, 'gallery-template', true );
        }

        $this->get_plugin_dir_options( $current_value );

        echo '</select>';


        echo '</table>';
    }

    /**
     * Print the template-options from template_dir
     *
     * @param type $cur_lang_post
     */
    public function get_custom_template_dir_options( $current_value = '' )
    {
        $templates = $this->get_custom_templates();
        foreach ( $templates as $key => $title ) {
            $selected = '';
            if ( $key == $current_value ) {
                $selected = ' selected="selected"';
            }
            echo '<option value="' . $key . '"' . $selected . '>' . $title . '</option>';
        }
    }

    /**
     * Returns an array with all templates from template-dir
     *
     */
    public function get_custom_templates()
    {
        $output = array ();
        // scan theme-dir for templates
        $custom_template_dirs = array ( get_stylesheet_directory() . '/post-gallery', get_stylesheet_directory() . '/plugins/post-gallery' );

        foreach ( $custom_template_dirs as $custom_template_dir ) {
            if ( file_exists( $custom_template_dir ) && is_dir( $custom_template_dir ) ) {
                $dir = scandir( $custom_template_dir );
                foreach ( $dir as $file ) {
                    if ( !is_dir( $custom_template_dir . '/' . $file ) ) {
                        $file = str_replace( '.php', '', $file );
                        $title = ucfirst( str_replace( '_', ' ', $file ) ) . __( ' (from Theme)', 'post-gallery' );
                        $output[ $file ] = $title;
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
    public function get_plugin_dir_options( $current_value = '' )
    {
        // list default-gallery-templates
        foreach ( $this->default_templates as $option_key => $option_title ) {
            $selected = '';

            if ( $option_key == $current_value ) {
                $selected = ' selected="selected"';
            }
            echo '<option value="' . $option_key . '"' . $selected . '>' . $option_title . '</option>';
        }
    }

    /**
     * Adds a metabox with the gallery-pictures and a file-upload
     *
     * @param type $post
     */
    public function add_gallery_pictures( $post )
    {
        $org_post = PostGallery::get_org_post( $post->ID );
        $cur_lang_post = $post;

        if ( !empty( $org_post ) ) {
            $post = $org_post;
        }
        //$image_dir = strtolower(str_replace('http://', '', esc_url($post->post_title)));
        $image_dir = PostGallery::get_image_dir( $post );
        $uploads = wp_upload_dir();
        $upload_dir = $uploads[ 'basedir' ] . '/gallery/' . $image_dir;
        $upload_url = $uploads[ 'baseurl' ] . '/gallery/' . $image_dir;
        $upload_url = str_replace( get_bloginfo( 'wpurl' ), '', $upload_url );
        $sort = get_post_meta( $cur_lang_post->ID, 'postgallery_imagesort', true );

        if ( empty( $sort ) ) {
            // for compatibility with old version
            $sort = get_post_meta( $cur_lang_post->ID, 'imagesort', true );
        }

        // Create folders if not exists
        if ( !file_exists( $uploads[ 'basedir' ] . '/cache' ) ) {
            @mkdir( $uploads[ 'basedir' ] . '/cache' );
            @chmod( $uploads[ 'basedir' ] . '/cache', octdec( '0777' ) );
        }
        if ( !file_exists( $uploads[ 'basedir' ] . '/gallery' ) ) {
            @mkdir( $uploads[ 'basedir' ] . '/gallery' );
            @chmod( $uploads[ 'basedir' ] . '/gallery', octdec( '0777' ) );
        }
        if ( !file_exists( $upload_dir ) ) {
            @mkdir( $upload_dir );
            @chmod( $upload_dir, octdec( '0777' ) );
        }

        // Load image titles and description
        $titles = get_post_meta( $cur_lang_post->ID, 'postgallery_titles', true );
        $descs = get_post_meta( $cur_lang_post->ID, 'postgallery_descs', true );
        $alt_attributes = get_post_meta( $cur_lang_post->ID, 'postgallery_alt_attributes', true );

        if ( empty( $image_dir ) ) {
            echo __( 'You have to save the post to upload images.', 'post-gallery' );
            return;
        }

        //var_dump($post);
        echo '
			<div id="imageupload_image" data-uploadfolder="' . $image_dir . '" data-pluginurl="' . WP_PLUGIN_URL . '/post-gallery' . '"></div>
			<div id="upload_error"></div>
		';;

        $images = array ();
        if ( file_exists( $upload_dir ) && is_dir( $upload_dir ) ) {
            $thumb_instance = Thumb::get_instance();
            $dir = scandir( $upload_dir );

            echo '<div id="del_button" class="button" onclick="deleteImages(\'' . $image_dir . '\');">Alle löschen</div>';

            echo '<ul id="sortable_pics">';

            if ( !empty( $dir ) ) {
                foreach ( $dir as $file ) {
                    if ( !is_dir( $upload_dir . '/' . $file ) ) {
                        $thumb = $thumb_instance->get_thumb( array (
                            'path'   => $upload_url . '/' . $file,
                            'width'  => 150,
                            'height' => 150,
                            'scale'  => 0
                        ) );

                        $images[ $file ] = '<li>';
                        $images[ $file ] .= '<img style="" data-src="' . $file . '" src="' . $thumb[ 'url' ] . '" alt="" />';
                        $images[ $file ] .= '<div class="img_title">' . $file . '</div>';
                        $images[ $file ] .= '<div class="del" onclick="deleteImage(this.parentNode, \'' . $image_dir . '/' . $file . '\');">x</div>';
                        $images[ $file ] .= '<div class="edit_details" onclick="PGtoggleDetails(this);"></div>';
                        $images[ $file ] .= '<div class="details">';
                        $images[ $file ] .= '<div class="title"><input type="text" placeholder="' . __( 'Title' ) . '" name="postgallery_titles[' . $file . ']" value="' . ( !empty( $titles[ $file ] ) ? $titles[ $file ] : '' ) . '" /></div>';
                        $images[ $file ] .= '<div class="desc"><textarea placeholder="' . __( 'Description' ) . '" name="postgallery_descs[' . $file . ']">' . ( !empty( $descs[ $file ] ) ? $descs[ $file ] : '' ) . '</textarea></div>';
                        $images[ $file ] .= '<div class="alt_attribute"><input type="text" placeholder="' . __( 'Alt-Attribut' ) . '" name="postgallery_alt_attributes[' . $file . ']" value="' . ( !empty( $alt_attributes[ $file ] ) ? $alt_attributes[ $file ] : '' ) . '" /></div>';
                        $images[ $file ] .= '</div>';
                        $images[ $file ] .= '</li>';
                    }
                }
                $sortimages = PostGallery::sort_images( $images, $post->ID );
                echo implode( '', $sortimages );
            }

            echo '</ul>';
        }

        // hidden-input contains the image-order
        echo '<input type="hidden" name="postgallery_imagesort" id="postgallery_imagesort" value="' . $sort . '" />';

        // hidden-input contains the current-slug
        echo '<input type="hidden" name="current_imagedir" value="' . $image_dir . '" />';

        // hidden-input contains the id of main-lang-post
        echo '<input type="hidden" name="postgallery_mainlang_id" id="postgallery_mainlang_id" value="' . $post->ID . '" />';

        // Javascript for drag&drop-sorting
        echo '<script type="text/javascript">jQuery(function () { jQuery("#sortable_pics").sortable(); });
				var input = jQuery("#postgallery_imagesort");
				jQuery("#sortable_pics").on("sortupdate", function(event, ui) {
					PGcloseDetails();
					var value = [];
					var count = 0;
					jQuery("#sortable_pics > li > img").each(function(index, element) {
						value[count] = jQuery(element).data("src");
						count+=1;
					});
					input.val(value.join(","));
				});
				checkForUpload();

				function deleteImages(path) {
					var answer = confirm("' . __( 'Are you sure you want to delete all pictures?', 'post-gallery' ) . '");
					PGcloseDetails();

					// Check if user confirmed the deletion of all images
					if (answer) {
						jQuery.post( ajaxurl + "?action=postgallery_deleteimage&path=" + path,
							function(data) {
								jQuery("#sortable_pics").remove();
							});
					}
				}
				function deleteImage(element, path) {
					PGcloseDetails();
					jQuery.post( ajaxurl + "?action=postgallery_deleteimage&path=" + path,
						function(data, textStatus) {
							deleteImageComplete(data, textStatus, element);
						});
				}
				function deleteImageComplete(result, status, element) {
					if (result == 1) {
						jQuery(element.remove());
					}
				}
				jQuery(".qq-upload-drop-area span").html("' . __( 'Move files here.', 'post-gallery' ) . '");
				jQuery(".qq-upload-button").addClass("button");

				function PGtoggleDetails(buttonElement) {
					var detailElement = jQuery(buttonElement).parent().find(\'.details\');
					var allDetailElements = jQuery(\'#sortable_pics .details\');
					if (detailElement.hasClass(\'active\')) {
						allDetailElements.removeClass(\'active\');
					} else {
						allDetailElements.removeClass(\'active\');
						detailElement.addClass(\'active\');
					}
				}
				function PGcloseDetails() {
					var allDetailElements = jQuery(\'#sortable_pics .details\');
					allDetailElements.removeClass(\'active\');
				}

			</script>';

        // style
        echo '<style type="text/css">
				#post-gallery-pictures { text-align:center; }
				#del_button { margin-top:20px; }
				#sortable_pics li { width:150px;height:150px;display:inline-block;position:relative;margin-right:10px; }
				#sortable_pics .img_title { position:absolute;bottom:0px;left:0px;right:0px;font-size:11px;text-align:center;background-color:#fff;background-color:rgba(255,255,255,0.7); }
				#imageupload_image { background-repeat:no-repeat;background-position:center;border:4px dashed #ddd; position:relative; height:150px;line-height:150px;font-size:24px;text-align:center;cursor:default;width:100%; }
				.qq-upload-list { display: none; }
				.qq-upload-button { top:50px; z-index:1000; }
				#sortable_pics .del { position: absolute; top:4px;right:4px;background-color:#fff;background-color:rgba(255,255,255,0.7);cursor:pointer;padding:0px 5px;color:#000; }
				.qq-upload-drop-area { display: block !important; position:absolute; width:100%; height:100%; z-index:1000; }
				#sortable_pics .edit_details { position: absolute; top:4px;left:4px;background-color:#fff;background-color:rgba(255,255,255,0.7);cursor:pointer;
					width:20px; height:20px; background-image:url(../wp-includes/images/wpicons.png);background-position:220px 20px;}
				#sortable_pics .details {
					position:absolute; padding:5px;left:25px; top:25px; min-width:400px;display:none; background-color:#000; background-color:rgba(0,0,0,0.7); text-align:center; z-index:1000;
				}
				#sortable_pics .details input { width:100%; }
				#sortable_pics .details textarea { width:100%; height:120px; }
				#sortable_pics .details.active { display:block; }
			</style>';
    }

    /**
     * Method to save Post-Meta
     *
     * @global type $post_options
     * @param type $post_id
     * @param type $post
     * @return type
     */
    public function save_post_meta( $post_id, $post )
    {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( !isset( $_POST[ 'post_type' ] ) ) {
            return;
        }

        $cur_lang_post = $post;
        $cur_lang_post_id = $post_id;

        if ( !empty( $_POST[ 'postgallery_mainlang_id' ] ) && $post_id !== $_POST[ 'postgallery_mainlang_id' ] ) {
            $post_id = $_POST[ 'postgallery_mainlang_id' ];
            $post = get_post( $post_id );
        }

        if ( $_POST[ 'post_type' ] == 'page' ) {
            if ( !current_user_can( 'edit_page', $post_id ) ) {
                return;
            }
        } else {
            if ( !current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }
        // Save form-fields
        if ( !empty( $this->option_fields ) ) {
            foreach ( $this->option_fields as $key => $post_option ) {
                //update_post_meta($post_id, $key, $_POST[$key]);
                update_post_meta( $cur_lang_post_id, $key, filter_input( INPUT_POST, $key ) );
            }
        }
        // Save templates
        if ( !empty( $_POST[ 'postgallery_template' ] ) ) {
            update_post_meta( $cur_lang_post_id, 'postgallery_template', filter_input( INPUT_POST, 'postgallery_template' ) );
        }
        // save sort
        if ( !empty( $_POST[ 'postgallery_imagesort' ] ) ) {
            update_post_meta( $post_id, 'postgallery_imagesort', filter_input( INPUT_POST, 'postgallery_imagesort' ) );
        }
        // save image titles
        if ( !empty( $_POST[ 'postgallery_titles' ] ) ) {
            update_post_meta( $post_id, 'postgallery_titles', $_POST[ 'postgallery_titles' ] );
        }
        // save image descriptions
        if ( !empty( $_POST[ 'postgallery_descs' ] ) ) {
            update_post_meta( $post_id, 'postgallery_descs', $_POST[ 'postgallery_descs' ] );
        }
        // save image alt
        if ( !empty( $_POST[ 'postgallery_alt_attributes' ] ) ) {
            update_post_meta( $post_id, 'postgallery_alt_attributes', $_POST[ 'postgallery_alt_attributes' ] );
        }

        $image_dir = PostGallery::get_image_dir( $post );

        // if post-title change, then move pictures
        if ( !empty( $_POST[ 'current_imagedir' ] ) && $_POST[ 'current_imagedir' ] !== $image_dir ) {
            $uploads = wp_upload_dir();
            $upload_dir = $uploads[ 'basedir' ] . '/gallery/' . $_POST[ 'current_imagedir' ];
            $upload_dir_new = $uploads[ 'basedir' ] . '/gallery/' . $image_dir;
            if ( file_exists( $upload_dir ) ) {
                $files = scandir( $upload_dir );
                @mkdir( $upload_dir_new );
                @chmod( $upload_dir_new, octdec( '0777' ) );

                foreach ( $files as $file ) {
                    if ( !is_dir( $upload_dir . '/' . $file ) ) {
                        copy( $upload_dir . '/' . $file, $upload_dir_new . '/' . $file );
                        unlink( $upload_dir . '/' . $file );
                    }
                }
                @rmdir( $upload_dir );
            }
        }
    }

}
