<?php
/* * **********************************
 * Author: shennemann
 * Last changed: 19.11.2014 10:25
 * ****************************'***** */


namespace Thumb;

use MagicAdminPage\MagicAdminPage;

set_time_limit( 0 );

class Thumb {
    public $srvDir = ABSPATH;
    public $cacheDir = '';
    public $defaultSettings = array(
        'scale' => 1,
        'width' => 1920,
        'height' => 1080,
        'bw' => false,
        'quality' => 1,
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

    public function __construct() {
        // get folders
        $this->srvDir = ABSPATH;

        // Load Options from PostGallery
        $this->pgOptions = MagicAdminPage::getOption( 'post-gallery' );

        // create cachedir
        $uploadDir = wp_upload_dir();
        $this->cacheDir = $uploadDir['basedir'] . '/cache';
        if ( !file_exists( $this->cacheDir ) ) {
            @mkdir( $this->cacheDir );
            @chmod( $this->cacheDir, octdec( '0777' ) );
        }
    }

    /**
     * Checks und correct a given filepath
     *
     * @param type $path
     * @return type
     */
    public function checkPath( $path ) {
        if ( empty( $path ) || !is_string( $path ) ) {
            return false;
        }
        $path = str_replace( get_bloginfo( 'wpurl' ), $this->srvDir, $path );
        $path = str_replace( '//', '/', $path );
        if ( !file_exists( $path ) ) {
            $path = $this->srvDir . '/' . $path;
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
    public function getThumb( $args ) {
        if ( empty( $args['path'] ) || !is_string( $args['path'] ) ) {
            return false;
        }
        if ( strpos( $args['path'], 'http://' ) !== false
            && strpos( $args['path'], get_bloginfo( 'wpurl' ) ) === false
        ) {
            // external
            return array(
                'path' => $args['path'],
                'url' => $args['path'],
                'thumb' => null,
            );
        }

        if ( class_exists( 'Imagick' ) && empty( $_GET['forceGd'] ) ) {
            $thumbResult = $this->getThumbImagick( $args );
        } else {
            $thumbResult = $this->getThumbGd( $args );
        }
        return $thumbResult;
    }

    /**
     * Generate filename for cache
     *
     * @param $path
     * @param $width
     * @param $height
     * @param int $scale
     * @param int $bw
     * @return mixed
     */
    public function getCacheFilename( $path, $width, $height, $scale = 0, $bw = 0 ) {
        // create cache-filename
        $pathParts = explode( "/", $path );
        $filename = array_pop( $pathParts );
        $filetime = filemtime( $path );
        $filenameParts = explode( ".", $filename );
        $cachefileExtension = array_pop( $filenameParts );

        $cachefile = str_replace( '.' . $cachefileExtension, '_' . $scale . '_' . $filetime
            . '_' . $width . '_' . $height . ( $bw ? '_bw' : '' ) . '.'
            . $cachefileExtension, $filename );

        return $cachefile;
    }

    /**
     *
     * @param type $args
     * @return array
     */
    public function getThumbImagick( $args ) {
        $args = array_merge( $this->defaultSettings, $args );
        // Setting-Variables
        $scale = $args['scale'];
        $bw = $args['bw'];
        $width = isset( $args['width'] ) ? $args['width'] : 10000;
        $height = isset( $args['height'] ) ? $args['height'] : 10000;
        $contentType = 'image/jpeg';
        $stretchImages = !empty( $this->pgOptions['stretchImages'] );

        if ( $width == 'auto' || !is_numeric( $width ) ) {
            $width = 10000;
        }

        if ( $height == 'auto' || !is_numeric( $height ) ) {
            $height = 10000;
        }

        // Image-Path
        if ( !empty( $args['path'] ) ) {
            $path = $args['path'];
        } else if ( !empty( $args['url'] ) ) {
            $path = $args['url'];
        } else {
            return array( 'error' => 'Filepath missed' );
        }
        $path = str_replace( '%20', ' ', $path );
        $path = $this->checkPath( $path );

        if ( !file_exists( $path ) || is_dir( $path ) ) {
            return array( 'error' => 'File not found' );
        }

        // Get imagedata
        $size = GetImageSize( $path );

        $orgWidth = $size[0];
        $orgHeight = $size[1];

        if ( !$stretchImages && $orgWidth <= $width && $orgHeight <= $height && !$bw ) {
            // Load original (do nothing)
        } else {
            // create cache-filename
            $cachefile = $this->getCacheFilename( $path, $width, $height, $scale, $bw );

            if ( file_exists( $this->cacheDir . '/' . $cachefile ) &&
                empty( $_REQUEST['forceNew'] ) &&
                filesize( $this->cacheDir . '/' . $cachefile ) > 0
            ) {
                // load from cache (do nothing)
            } else {
                try {
                    $im = new \Imagick( $path );
                } catch ( Exception $e ) {
                    return array( 'error' => 'Imagick fails', 'exceptions' => $e );
                }

                // crop images
                switch ( $scale ) {
                    case 0: // crop
                        $im->cropThumbnailImage( $width, $height );
                        break;

                    case 3: // Ignore proportions
                        $im->thumbnailimage( $width, $height );
                        break;

                    case 2: // use short edge, ignore long edge
                        if ( $width > $height ) {
                            $im->thumbnailimage( 0, $height );
                        } else {
                            $im->thumbnailimage( $width, 0 );
                        }
                        break;

                    case 1: // use long edge, ignore short edge
                    default:
                        if ( $width > $height ) {
                            $im->thumbnailimage( $width, 0 );
                        } else {
                            $im->thumbnailimage( 0, $height );
                        }
                        break;
                }

                // write image to cache
                $im->writeImage( $this->cacheDir . '/' . $cachefile );

                @chmod( $this->cacheDir . '/' . $cachefile, octdec( '0666' ) );
            }
            $path = $this->cacheDir . '/' . $cachefile;
        }

        // output image
        if ( empty( $args['returnThumb'] ) && empty( $_REQUEST['returnThumb'] ) ) {
            $thumbnail = null;
        } else {
            $thumbnail = file_get_contents( $path );
        }

        $newSize = getimagesize( $path );

        return array(
            'thumb' => $thumbnail,
            'content-type' => $contentType,
            'path' => $path,
            'url' => str_replace( $this->srvDir, get_bloginfo( 'wpurl' ) . '/', $path ),
            'width' => $newSize[0],
            'height' => $newSize[1],
        );
    }

    /**
     * Resize using gd-lib
     *
     * @param $args
     * @return array
     */
    public function getThumbGd( $args ) {
        $args = array_merge( $this->defaultSettings, $args );
        // Setting-Variables
        $scale = $args['scale'];
        $bw = $args['bw'];
        $width = $args['width'];
        $height = $args['height'];
        $quality = $args['quality'];
        $stretchImages = !empty( $this->pgOptions['stretchImages'] );

        $returnArray = array();

        if ( $width == 'auto' || !is_numeric( $width ) ) {
            $width = 10000;
        }

        if ( $height == 'auto' || !is_numeric( $height ) ) {
            $height = 10000;
        }

        // Image-Path
        if ( !empty( $args['path'] ) ) {
            $path = $args['path'];
        } else if ( !empty( $args['url'] ) ) {
            $path = $args['url'];
        } else {
            return array( 'error' => 'Filepath missed' );
        }

        $path = $this->checkPath( $path );

        if ( !file_exists( $path ) ) {
            return array( 'error' => 'File not found' );
        }

        // create cache-filename
        $cachefile = $this->getCacheFilename( $path, $width, $height, $scale, $bw );

        // check if cache-file already exists
        if ( file_exists( $this->cacheDir . '/' . $cachefile ) && empty( $_REQUEST['forceNew'] ) ) {
            if ( !empty( $args['returnThumb'] ) ) {
                $returnArray['thumb'] = file_get_contents( $this->cacheDir . '/' . $cachefile );
            } else {
                $returnArray['thumb'] = null;
            }
            $returnArray['path'] = $this->cacheDir . '/' . $cachefile;
            $returnArray['url'] = str_replace( $this->srvDir, get_bloginfo( 'wpurl' ) . '/', $returnArray['path'] );

            $newSize = getimagesize( $returnArray['path'] );
            $returnArray['width'] = $newSize[0];
            $returnArray['height'] = $newSize[1];

            return $returnArray;
        }

        // Get imagedata
        $size = GetImageSize( $path );
        $orgWidth = $size[0];
        $orgHeight = $size[1];
        $newHeight = $height;
        $newWidth = $width;

        switch ( $scale ) {
            case 0: // crop images
                $newHeight = $height;
                break;

            case 1:
                if ( $orgWidth > $orgHeight ) {
                    $newWidth = 9999;
                } else {
                    $newHeight = 9999;
                }
            // no break
            case 2: // let aspect ratio, scale 1:1
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

            case 3: // ignore aspect ratio
                $newHeight = $height;
                break;

            case 4:  // Let aspect ratio, scale 1:1, if to wide, then crop
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

            case 5:  // nobody knows
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
        }

        if ( !$stretchImages && $orgWidth <= $newWidth && $orgHeight <= $newHeight && !$bw ) {
            // Load original
            if ( !empty( $args['returnThumb'] ) ) {
                $returnArray['thumb'] = file_get_contents( $path );
            }
            $returnArray['path'] = $path;
            $returnArray['url'] = str_replace( $this->srvDir, get_bloginfo( 'wpurl' ) . '/', $path );
        } else {
            // create cache-filename
            $cachefile = $this->getCacheFilename( $path, $width, $height, $scale, $bw );

            if ( !file_exists( $this->cacheDir . '/' . $cachefile ) || !empty( $_REQUEST['forceNew'] ) ) {
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
                switch ( $size[2] ) {
                    case 1:
                        // GIF
                        $createFunction = 'imagecreatefromgif';
                        $returnArray['content-type'] = 'image/gif';
                        break;

                    case 2:
                        // JPG
                        $createFunction = 'imagecreatefromjpeg';
                        $returnArray['content-type'] = 'image/jpg';
                        break;

                    case 3:
                        // PNG
                        $createFunction = 'imagecreatefrompng';
                        $returnArray['content-type'] = 'image/png';
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
                    return array(
                        'content-type' => 'image/jpg',
                        'show_org' => true,
                        'thumb' => file_get_contents( $path ),
                        'path' => $path,
                        'url' => str_replace( $this->srvDir, get_bloginfo( 'wpurl' ) . '/', $path ),
                        'width' => $orgWidth,
                        'height' => $orgHeight,
                    );
                }

                $newImage = imagecreatetruecolor( $newWidth, $newHeight );
                imageCopyResampled( $newImage, $oldImage, $newOffsetX, $newOffsetY, $sourceOffsetX, $sourceOffsetY, $newWidth, $newHeight, $orgWidth, $orgHeight );
                if ( $bw ) {
                    imagefilter( $newImage, IMG_FILTER_GRAYSCALE );
                }


                // Save in cache
                switch ( $size[2] ) {
                    case 1:
                        // GIF
                        $returnArray['thumb'] = imagegif( $newImage, $this->cacheDir . '/' . $cachefile );
                        break;

                    case 2:
                        // JPG
                        $quality = '100';
                        $returnArray['thumb'] = imagejpeg( $newImage, $this->cacheDir . '/' . $cachefile, $quality );
                        break;

                    case 3:
                        // PNG
                        $returnArray['thumb'] = imagepng( $newImage, $this->cacheDir . '/' . $cachefile );
                        break;

                    default: // anderer Mediatyp

                        break;
                }

                @chmod( $this->cacheDir . '/' . $cachefile, octdec( '0666' ) );
                imagedestroy( $oldImage );
                imagedestroy( $newImage );
            }

            // output image
            if ( !empty( $args['returnThumb'] ) ) {
                $returnArray['thumb'] = file_get_contents( $this->cacheDir . '/' . $cachefile );
            } else {
                $returnArray['thumb'] = null;
            }

            $returnArray['path'] = $this->cacheDir . '/' . $cachefile;
            $returnArray['url'] = str_replace( $this->srvDir, get_bloginfo( 'wpurl' ) . '/', $returnArray['path'] );
        }

        $newSize = getimagesize( $returnArray['path'] );
        $returnArray['width'] = $newSize[0];
        $returnArray['height'] = $newSize[1];

        return $returnArray;
    }

    /**
     * Echos the thumb and set header
     */
    public function printThumb() {
        // Fix wrong keys (beginning with amp;)
        foreach ( $_GET as $key => $value ) {
            $key = str_replace( "amp;", "", $key );
            $_GET[$key] = $value;
        }

        if ( ( !empty( $_GET['path'] ) || !empty( $_GET['url'] ) ) ) {
            if ( empty( $_GET['quality'] ) ) {
                $quality = 100;
            } else {
                $quality = $_GET['quality'];
            }
            if ( isset( $_GET['scale'] ) ) {
                $scale = $_GET['scale'];
            } else {
                $scale = 1;
            }

            $bw = false;
            if ( isset( $_GET['bw'] ) ) {
                $bw = true;
            }

            $thumbResult = $this->getThumb( array(
                'path' => urldecode( $_GET['path'] ),
                'width' => ( isset( $_GET['width'] ) ? $_GET['width'] : 0 ),
                'height' => ( isset( $_GET['height'] ) ? $_GET['height'] : 0 ),
                'scale' => $scale,
                'quality' => $quality,
                'bw' => $bw,
                'returnThumb' => ( !empty( $_GET['returnThumb'] ) ? true : false ),
            ) );

            if ( empty( $thumbResult ) ) {
                echo 'Fatal error! -> Empty result';
            } else if ( !empty( $thumbResult['error'] ) ) {
                echo 'An Error occurred: ' . $thumbResult['error'];
            } else if ( empty( $thumbResult['url'] ) ) {
                echo 'Fatal error! -> Thumb not found';
            } else if ( !empty( $_REQUEST['returnThumb'] ) ) {
                header( "Content-type: image/jpeg" );
                echo $thumbResult['thumb'];
            } else {
                if ( empty( $_GET['debug'] ) ) {
                    header( 'Location: ' . $thumbResult['url'] );
                } else {
                    var_dump( $thumbResult );
                }
            }
        } else {
            echo 'Path is missing';
        }
    }

    /**
     * Static function for printing the thumb
     */
    public static function theThumb() {
        $instance = new Thumb();
        $instance->printThumb();
    }

    /**
     * Return an instance of this class.
     *
     * @since 0.1
     *
     * @return object A single instance of this class.
     */
    public static function getInstance() {
        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
        return self::$instance;
    }
}