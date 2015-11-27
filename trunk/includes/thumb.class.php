<?php
    /* * **********************************
     * Author: shennemann
     * Last changed: 19.11.2014 10:25
     * ****************************'***** */


    namespace Thumb;

    use MagicAdminPage\MagicAdminPage;

    set_time_limit( 0 );

    class Thumb
    {
        public $srv_dir = ABSPATH;
        public $cache_dir = '';
        public $default_settings = array (
            'scale'   => 1,
            'width'   => 1920,
            'height'  => 1080,
            'bw'      => false,
            'quality' => 1
        );
        public $pgOptions = null;

        /**
         * Instance of this class.
         *
         * @since ?.??
         *
         * @var object
         */
        protected static $instance = null;

        public function __construct()
        {
            // get folders
            $this->srv_dir = ABSPATH;

            // Load Options from PostGallery
            $this->pgOptions = MagicAdminPage::getOption('post-gallery');

            // create cachedir
            $upload_dir = wp_upload_dir();
            $this->cache_dir = $upload_dir[ 'basedir' ] . '/cache';
            if ( !file_exists( $this->cache_dir ) ) {
                @mkdir( $this->cache_dir );
                @chmod( $this->cache_dir, octdec( '0777' ) );
            }
        }

        /**
         * Checks und correct a given filepath
         *
         * @param type $path
         * @return type
         */
        public function check_path( $path )
        {
            if ( empty( $path ) || !is_string( $path ) ) {
                return false;
            }
            $path = str_replace( get_bloginfo( 'wpurl' ), $this->srv_dir, $path );
            $path = str_replace( '//', '/', $path );
            if ( !file_exists( $path ) ) {
                $path = $this->srv_dir . '/' . $path;
            }
            $path = str_replace( '//', '/', $path );
            $path = str_replace( '%20', ' ', $path );

            return $path;
        }

        /**
         * Checks if Imgick ist defined and calls the function to thumb an image
         *
         * @param type $args
         * @return type
         */
        public function get_thumb( $args )
        {
            if ( empty( $args[ 'path' ] ) || !is_string( $args[ 'path' ] ) ) {
                return false;
            }
            if ( strpos( $args[ 'path' ], 'http://' ) !== false
                && strpos( $args[ 'path' ], get_bloginfo( 'wpurl' ) ) === false
            ) {
                // external
                return array (
                    'path'  => $args[ 'path' ],
                    'url'   => $args[ 'path' ],
                    'thumb' => null,
                );
            }

            if ( class_exists( 'Imagick' ) ) {
                $thumb_result = $this->get_thumb_imagick( $args );
            } else {
                $thumb_result = $this->get_thumb_gd( $args );
            }
            return $thumb_result;
        }

        public function get_cache_filename( $path, $width, $height, $scale = 0, $bw = 0 )
        {
            // create cache-filename
            $path_parts = explode( "/", $path );
            $filename = array_pop( $path_parts );
            $filetime = filemtime( $path );
            $filename_parts = explode( ".", $filename );
            $cachefile_extension = array_pop( $filename_parts );

            $cachefile = str_replace( '.' . $cachefile_extension, '_' . $scale . '_' . $filetime . '_' . $width . '_' . $height . ( $bw ? '_bw' : '' ) . '.' . $cachefile_extension, $filename );
            return $cachefile;
        }

        /**
         *
         * @param type $args
         * @return array
         */
        public function get_thumb_imagick( $args )
        {
            $args = array_merge( $this->default_settings, $args );
            // Setting-Variables
            $scale = $args[ 'scale' ];
            $bw = $args[ 'bw' ];
            $width = $args[ 'width' ];
            $height = $args[ 'height' ];
            $content_type = 'image/jpeg';
            $stretchImages = !empty( $this->pgOptions[ 'stretch_images' ] );

            if ( $width == 'auto' || !is_numeric( $width ) ) {
                $width = 10000;
            }

            if ( $height == 'auto' || !is_numeric( $height ) ) {
                $height = 10000;
            }

            // Image-Path
            if ( !empty( $args[ 'path' ] ) ) {
                $path = $args[ 'path' ];
            } else if ( !empty( $args[ 'url' ] ) ) {
                $path = $args[ 'url' ];
            } else {
                return array ( 'error' => 'Filepath missed' );
            }
            $path = str_replace( '%20', ' ', $path );
            $path = $this->check_path( $path );

            if ( !file_exists( $path ) || is_dir( $path ) ) {
                return array ( 'error' => 'File not found' );
            }

            // create cache-filename
            $cachefile = $this->get_cache_filename( $path, $width, $height, $scale, $bw );
            if ( file_exists( $this->cache_dir . '/' . $cachefile ) &&
                empty( $_REQUEST[ 'force_new' ] ) &&
                filesize($this->cache_dir . '/' . $cachefile) > 0
            ) {
                $loadFromCache = true;
                $orgWidth = $width;
                $orgHeight = $height;
                $path = $this->cache_dir . '/' . $cachefile;
            } else {
                // Get imagedata
                $size = GetImageSize( $path );
                $orgWidth = $size[ 0 ];
                $orgHeight = $size[ 1 ];
            }

            if ( !empty($loadFromCache) || ( !$stretchImages && $orgWidth <= $width && $orgHeight <= $height && !$bw ) ) {
                // Load original or from cache (do nothing)
            } else {
                // crop images
                try {
                    $im = new \Imagick( $path );
                } catch ( Exception $e ) {
                    return array ( 'error' => 'Imagick fails' );
                }

                // calc height

                switch ( $scale ) {
                    case 0: // crop
                        $im->cropThumbnailImage( $width, $height );
                        break;

                    case 3: // use long edge, ignore short edge
                        if ( $width > $height ) {
                            $im->thumbnailimage( $width, 0 );
                        } else {
                            $ratio = $orgWidth / $orgHeight;
                            $im->thumbnailimage( 0, $height );
                        }
                        break;

                    case 2:
                    case 4:
                        $im->thumbnailimage( $width, $height );
                        break;

                    case 1: // scale 1:1 (height = maxheight, width=maxwidth)
                    case 5:
                    default:
                        if ( $width > $height ) {
                            $im->thumbnailimage( 0, $height );
                        } else {
                            $im->thumbnailimage( $width, 0 );
                        }
                        break;
                }

                // write image to cache
                $im->writeImage( $this->cache_dir . '/' . $cachefile );

                @chmod( $this->cache_dir . '/' . $cachefile, octdec( '0666' ) );
                $path = $this->cache_dir . '/' . $cachefile;
            }

            if ( empty( $args[ 'return_thumb' ] ) && empty( $_REQUEST[ 'return_thumb' ] ) ) {
                $thumbnail = null;
            } else {
                $thumbnail = file_get_contents( $path );
            }
            return array (
                'thumb'        => $thumbnail,
                'content-type' => $content_type,
                'path'         => $path,
                'url'          => str_replace( $this->srv_dir, get_bloginfo( 'wpurl' ) . '/', $path )
            );
        }

        public function get_thumb_gd( $args )
        {
            $args = array_merge( $this->default_settings, $args );
            // Setting-Variables
            $scale = $args[ 'scale' ];
            $bw = $args[ 'bw' ];
            $width = $args[ 'width' ];
            $height = $args[ 'height' ];
            $quality = $args[ 'quality' ];
            $stretchImages = !empty( $this->pgOptions[ 'stretch_images' ] );

            $return_array = array ();

            if ( $width == 'auto' || !is_numeric( $width ) ) {
                $width = 10000;
            }

            if ( $height == 'auto' || !is_numeric( $height ) ) {
                $height = 10000;
            }

            // Image-Path
            if ( !empty( $args[ 'path' ] ) ) {
                $path = $args[ 'path' ];
            } else if ( !empty( $args[ 'url' ] ) ) {
                $path = $args[ 'url' ];
            } else {
                return array ( 'error' => 'Filepath missed' );
            }

            $path = $this->check_path( $path );

            if ( !file_exists( $path ) ) {
                return array ( 'error' => 'File not found' );
            }

            // create cache-filename
            $cachefile = $this->get_cache_filename( $path, $width, $height, $scale, $bw );

            // check if cache-file already exists
            if ( file_exists( $this->cache_dir . '/' . $cachefile ) && empty( $_REQUEST[ 'force_new' ] ) ) {
                if ( !empty( $args[ 'return_thumb' ] ) ) {
                    $return_array[ 'thumb' ] = file_get_contents( $this->cache_dir . '/' . $cachefile );
                } else {
                    $return_array[ 'thumb' ] = null;
                }
                $return_array[ 'path' ] = $this->cache_dir . '/' . $cachefile;
                $return_array[ 'url' ] = str_replace( $this->srv_dir, get_bloginfo( 'wpurl' ) . '/', $return_array[ 'path' ] );

                return $return_array;
            }

            // Get imagedata
            $size = GetImageSize( $path );
            $orgWidth = $size[ 0 ];
            $orgHeight = $size[ 1 ];
            $newHeight = $height;
            $newWidth = $width;

            switch ( $scale ) {
                case 0: // crop images
                    $newHeight = $height;
                    break;

                case 1: // let aspect ratio, scale 1:1
                    switch ( $orgWidth > $orgHeight ) {
                        case true: // breitformat
                            if ( !$stretchImages && $newWidth > $orgWidth ) {
                                $newWidth = $orgWidth;
                                $newHeight = $orgHeight;
                            } else {
                                $newHeight = @intval( $newWidth * ( $orgHeight / $orgWidth ) );
                            }

                            if ( !$stretchImages && $newHeight > $height ) {
                                // if calc height is bigger then given height
                                $newHeight = $height;
                                $newWidth = @intval( $newHeight * ( $orgWidth / $orgHeight ) );
                            }

                            break;
                        case false: // Hochformat
                            if ( !$stretchImages && $newHeight > $orgHeight ) {
                                $newHeight = $orgHeight;
                                $newWidth = $orgWidth;
                            } else {
                                $newWidth = @intval( $newHeight * ( $orgWidth / $orgHeight ) );
                            }

                            if ( !$stretchImages && $newWidth > $width ) {
                                // if calc width is bigger then given width
                                $newWidth = $width;
                                $newHeight = @intval( $newWidth * ( $orgHeight / $orgWidth ) );
                            }

                            break;
                    }
                    break;
                case 3:  // Let aspect ratio, scale 1:1, if to wide, then crop
                    switch ( $orgWidth > $orgHeight ) {
                        case true: // landscape
                            if ( !$stretchImages && $newWidth > $orgWidth ) {
                                $scale = 0;
                                $newHeight = $height;
                            } else {
                                $newHeight = @intval( $newWidth * ( $orgHeight / $orgWidth ) );
                            }
                            // if calc height is bigger then given height
                            if ( $newHeight > $height ) {
                                $newWidth = @intval( $height * ( $orgWidth / $orgHeight ) );
                                $newHeight = $height;
                            }
                            break;

                        case false: //  portrait mode
                            if ( !$stretchImages && $newHeight > $orgHeight ) {
                                $scale = 0;
                                $newWidth = $width;
                            } else {
                                $newWidth = @intval( $newHeight * ( $orgWidth / $orgHeight ) );
                            }
                            // if calc width is bigger then given width
                            if ( $newWidth > $width ) {
                                $newHeight = @intval( $width * ( $orgWidth / $orgHeight ) );
                                $newWidth = $width;
                            }
                            break;
                    }

                    break;
                case 5:  //
                    switch ( $orgWidth > $orgHeight ) {
                        case true: // landscape
                            if ( !$stretchImages && $newWidth > $orgWidth ) {
                                $scale = 0;
                                $newHeight = $height;
                            } else {
                                $newWidth = @intval( $newHeight * ( $orgWidth / $orgHeight ) );
                            }
                            // if calc height is bigger then given height
                            if ( $newHeight > $height ) {
                                $newHeight = @intval( $width * ( $orgWidth / $orgHeight ) );
                                $newWidth = $width;
                            }
                            break;

                        case false: // portrait mode
                            if ( !$stretchImages && $newHeight > $orgHeight ) {
                                $scale = 0;
                                $newWidth = $width;
                            } else {
                                $newHeight = @intval( $newWidth * ( $orgHeight / $orgWidth ) );
                            }
                            // if calc width is bigger then given width
                            if ( $newWidth > $width ) {
                                $newWidth = @intval( $height * ( $orgWidth / $orgHeight ) );
                                $newHeight = $height;
                            }
                            break;
                    }

                    break;
                case 2:
                case 4: // ignore aspect ratio
                    $newHeight = $height;
                    break;
            }

            if ( !$stretchImages && $orgWidth <= $newWidth && $orgHeight <= $newHeight && !$bw ) {
                // Load original
                if ( !empty( $args[ 'return_thumb' ] ) ) {
                    $return_array[ 'thumb' ] = file_get_contents( $path );
                }
                $return_array[ 'url' ] = str_replace( $this->srv_dir, get_bloginfo( 'wpurl' ) . '/', $path );
            } else {
                // create cache-filename
                $cachefile = $this->get_cache_filename( $path, $width, $height, $scale, $bw );

                if ( !file_exists( $this->cache_dir . '/' . $cachefile ) || !empty( $_REQUEST[ 'force_new' ] ) ) {
                    // crop images
                    $newCalcHeight = $newHeight;
                    $newCalcWidth = $newWidth;
                    $sourceOffsetX = 0;
                    $sourceOffsetY = 0;

                    $newOffsetX = 0;
                    $newOffsetY = 0;


                    $widthRatio = $orgWidth / $newWidth;
                    $heightRatio = $orgHeight / $newHeight;

                    if ( $scale == 0 ) {
                        if ( $widthRatio < $heightRatio ) {
                            $newCalcHeight = round( $orgHeight / $widthRatio );

                            if ( $newCalcHeight > $newHeight ) {
                                $sourceOffsetY = ( ( $newCalcHeight - $newHeight ) / 2 ) * $widthRatio;
                                $orgHeight -= round( $sourceOffsetY * 2 );
                                $sourceOffsetY = round( $sourceOffsetY );
                            }
                        } else {
                            $newCalcWidth = round( $orgWidth / $heightRatio );

                            if ( $newCalcWidth > $newWidth ) {
                                $sourceOffsetX = ( ( $newCalcWidth - $newWidth ) / 2 ) * $heightRatio;
                                $orgWidth -= round( $sourceOffsetX * 2 );
                                $sourceOffsetX = round( $sourceOffsetX );
                            }
                        }
                    }


                    // thumb neu erzeugen
                    switch ( $size[ 2 ] ) {
                        case 1:
                            // GIF
                            $createFunction = 'imagecreatefromgif';
                            $return_array[ 'content-type' ] = 'image/gif';
                            break;

                        case 2:
                            // JPG
                            $createFunction = 'imagecreatefromjpeg';
                            $return_array[ 'content-type' ] = 'image/jpg';
                            break;

                        case 3:
                            // PNG
                            $createFunction = 'imagecreatefrompng';
                            $return_array[ 'content-type' ] = 'image/png';
                            break;

                        default: // other media

                            break;
                    }

                    // create thumb
                    $oldImage = @$createFunction( $path );

                    // Fallback -> create from string
                    if ( !$oldImage ) {
                        $oldImage = @imagecreatefromstring( file_get_contents( $path ) );
                    }

                    // if Fail, then load orginal image
                    if ( !$oldImage ) {
                        return array (
                            'content-type' => 'image/jpg',
                            'show_org'     => true,
                            'thumb'        => file_get_contents( $path ),
                            'path'         => $path,
                            'url'          => str_replace( $this->srv_dir, get_bloginfo( 'wpurl' ) . '/', $path )
                        );
                    }

                    $newImage = imagecreatetruecolor( $newWidth, $newHeight );
                    imageCopyResampled( $newImage, $oldImage, $newOffsetX, $newOffsetY, $sourceOffsetX, $sourceOffsetY, $newWidth, $newHeight, $orgWidth, $orgHeight );
                    if ( $bw ) {
                        imagefilter( $newImage, IMG_FILTER_GRAYSCALE );
                    }


                    // Save in cache
                    switch ( $size[ 2 ] ) {
                        case 1:
                            // GIF
                            $return_array[ 'thumb' ] = imagegif( $newImage, $this->cache_dir . '/' . $cachefile );
                            break;

                        case 2:
                            // JPG
                            $quality = '100';
                            $return_array[ 'thumb' ] = imagejpeg( $newImage, $this->cache_dir . '/' . $cachefile, $quality );
                            break;

                        case 3:
                            // PNG
                            $return_array[ 'thumb' ] = imagepng( $newImage, $this->cache_dir . '/' . $cachefile );
                            break;

                        default: // anderer Mediatyp

                            break;
                    }

                    @chmod( $this->cache_dir . '/' . $cachefile, octdec( '0666' ) );
                    imagedestroy( $oldImage );
                    imagedestroy( $newImage );
                }

                if ( !empty( $args[ 'return_thumb' ] ) ) {
                    $return_array[ 'thumb' ] = file_get_contents( $this->cache_dir . '/' . $cachefile );
                } else {
                    $return_array[ 'thumb' ] = null;
                }
                $return_array[ 'path' ] = $this->cache_dir . '/' . $cachefile;
                $return_array[ 'url' ] = str_replace( $this->srv_dir, get_bloginfo( 'wpurl' ) . '/', $return_array[ 'path' ] );
            }
            return $return_array;
        }

        /**
         * Echos the thumb and set header
         */
        public function print_thumb()
        {
            // Fix wrong keys (beginning with amp;)
            foreach ( $_GET as $key => $value ) {
                $key = str_replace( "amp;", "", $key );
                $_GET[ $key ] = $value;
            }

            if ( ( !empty( $_GET[ 'path' ] ) || !empty( $_GET[ 'url' ] ) ) ) {
                if ( empty( $_GET[ 'quality' ] ) ) {
                    $quality = 100;
                } else {
                    $quality = $_GET[ 'quality' ];
                }
                if ( isset( $_GET[ 'scale' ] ) ) {
                    $scale = $_GET[ 'scale' ];
                } else {
                    $scale = 1;
                }

                $bw = false;
                if ( isset( $_GET[ 'bw' ] ) ) {
                    $bw = true;
                }

                $thumb_result = $this->get_thumb( array (
                    'path'         => urldecode( $_GET[ 'path' ] ),
                    'width'        => ( isset( $_GET[ 'width' ] ) ? $_GET[ 'width' ] : 0 ),
                    'height'       => ( isset( $_GET[ 'height' ] ) ? $_GET[ 'height' ] : 0 ),
                    'scale'        => $scale,
                    'quality'      => $quality,
                    'bw'           => $bw,
                    'return_thumb' => ( !empty( $_GET[ 'return_thumb' ] ) ? true : false )
                ) );

                if ( empty( $thumb_result ) ) {
                    echo 'Fatal error! -> Empty result';
                } else if ( !empty( $thumb_result[ 'error' ] ) ) {
                    echo 'An Error occurred: ' . $thumb_result[ 'error' ];
                } else if ( empty( $thumb_result[ 'url' ] ) ) {
                    echo 'Fatal error! -> Thumb not found';
                } else if ( !empty( $_REQUEST[ 'return_thumb' ] ) ) {
                    header( "Content-type: image/jpeg" );
                    echo $thumb_result[ 'thumb' ];
                } else {
                    if ( empty( $_GET[ 'debug' ] ) ) {
                        header( 'Location: ' . $thumb_result[ 'url' ] );
                    } else {
                        var_dump( $thumb_result );
                    }
                }
            } else {
                echo 'Path is missing';
            }
        }

        /**
         * Static function for printing the thumb
         */
        public static function the_thumb()
        {
            $instance = new Thumb();
            $instance->print_thumb();
        }

        /**
         * Return an instance of this class.
         *
         * @since 0.1
         *
         * @return object A single instance of this class.
         */
        public static function get_instance()
        {
            // If the single instance hasn't been set, set it now.
            if ( null == self::$instance ) {
                self::$instance = new self;
            }
            return self::$instance;
        }
    }