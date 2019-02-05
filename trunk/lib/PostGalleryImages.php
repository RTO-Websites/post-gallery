<?php namespace Lib;

use Admin\PostGalleryAdmin;

class PostGalleryImages {
    static $cachedImages = [];

    /**
     * Sorting an image-array
     *
     * @param {array} $images
     * @return {array}
     */
    public static function sort( $images, $postid ) {
        // get post in default language
        $orgPost = PostGallery::getOrgPost( $postid );
        if ( !empty( $orgPost ) ) {
            $post = $orgPost;
            $postid = $orgPost->ID;
        }
        $sort = get_post_meta( $postid, 'postgalleryImagesort', true );

        // sort by elementor-widget
        if ( class_exists( '\Elementor\Plugin' ) && !empty( $GLOBALS['elementorWidgetSettings'] ) ) {
            if ( !empty( $GLOBALS['elementorWidgetSettings']['pgsort'] ) ) {
                $sort = $GLOBALS['elementorWidgetSettings']['pgsort'];
            }
        }

        $sortimages = [];

        if ( !empty( $sort ) ) {
            $count = 0;
            $sortArray = explode( ',', $sort );
            foreach ( $sortArray as $key ) {
                $key = trim( $key );
                if ( !empty( $images[$key] ) ) {
                    $sortimages[$key] = $images[$key];
                    unset( $images[$key] );
                }
                $count += 1;
            }
        }


        $sortimages = array_merge( $sortimages, $images );

        return $sortimages;
    }

    /**
     * Return an image-array
     *
     * @param int $postid
     * @return array
     */
    public static function get( $postid = null ) {
        if ( empty( $postid ) && empty( $GLOBALS['post'] ) ) {
            return;
        }
        if ( empty( $postid ) ) {
            $postid = $GLOBALS['post']->ID;
            $post = $GLOBALS['post'];
        }

        // check if image list is in cache
        if ( isset( self::$cachedImages[$postid] ) ) {
            return self::$cachedImages[$postid];
        }

        if ( empty( $post ) ) {
            $post = get_post( $postid );
        }
        // get post in default language
        $orgPost = PostGallery::getOrgPost( $postid );
        if ( !empty( $orgPost ) ) {
            $post = $orgPost;
            $postid = $orgPost->ID;
            if ( isset( self::$cachedImages[$postid] ) ) {
                // check if image list is in cache
                return self::$cachedImages[$postid];
            }
        }

        if ( empty( $post ) || $post->post_type === 'attachment' ) {
            return;
        }

        $uploads = wp_upload_dir();

        //$imageDir = strtolower(str_replace('http://', '', esc_url($post->post_title)));
        $imageDir = PostGallery::getImageDir( $post );
        $uploadDir = $uploads['basedir'] . '/gallery/' . $imageDir;
        $uploadFullUrl = $uploads['baseurl'] . '/gallery/' . $imageDir;
        $uploadUrl = str_replace( get_bloginfo( 'wpurl' ), '', $uploadFullUrl );
        $images = [];

        if ( file_exists( $uploadDir ) && is_dir( $uploadDir ) ) {
            $dir = scandir( $uploadDir );

            foreach ( $dir as $file ) {
                if ( !is_dir( $uploadDir . '/' . $file ) ) {
                    $fullUrl = $uploadFullUrl . '/' . $file;
                    $path = $uploadUrl . '/' . $file;

                    if ( self::urlIsThumbnail( $fullUrl ) ) {
                        continue;
                    }

                    $alt = '';
                    $imageTitle = '';
                    $imageOptions = '';
                    $imageDesc = '';
                    $attachmentId = self::checkForAttachmentData( $fullUrl, $postid );
                    if ( !empty( $attachmentId ) ) {
                        $attachment = get_post( $attachmentId );
                        $alt = get_post_meta( $attachmentId, '_wp_attachment_image_alt', true );
                        $imageOptions = get_post_meta( $attachmentId, 'postgallery-image-options', true );
                        if ( !empty( $attachment ) ) {
                            $imageTitle = $attachment->post_title;
                            $imageDesc = $attachment->post_content;
                        }
                    }

                    $imageOptionsParsed = self::parseImageOptions( $imageOptions );

                    $images[$file] = [
                        'filename' => $file,
                        'path' => $path,
                        'url' => $fullUrl,
                        'thumbURL' => get_bloginfo( 'wpurl' ) . '/?loadThumb&amp;path=' . $uploadUrl . '/' . $file,
                        'title' => $imageTitle,
                        'desc' => $imageDesc,
                        'alt' => $alt,
                        'post_id' => $postid,
                        'post_title' => get_the_title( $postid ),
                        'imageOptions' => $imageOptions,
                        'imageOptionsParsed' => $imageOptionsParsed,
                        'attachmentId' => $attachmentId,
                        'srcset' => wp_get_attachment_image_srcset( $attachmentId, 'full' ),
                        //'srcsetSizes' => wp_get_attachment_image_sizes($attachmentId, 'full'),
                    ];
                }
            }
        }

        $images = self::sort( $images, $postid );
        self::$cachedImages[$postid] = $images;
        return $images;
    }


    /**
     * Return an image-array with resized images
     *
     * @param int $postid
     * @param array $args
     * @return array
     */
    public static function getResized( $postid = 0, $args = [] ) {
        $images = self::getImages( $postid );

        return self::getPicsResized( $images, $args );
    }


    /**
     * Generate thumb-path for an array of pics
     *
     * @param array $pics
     * @param array $args
     * @return array
     */
    public static function resize( $pics, $args ) {
        if ( !is_array( $pics ) ) {
            return $pics;
        }
        $newPics = [];
        foreach ( $pics as $pic ) {
            // create resized image
            if ( is_array( $pic ) ) {
                if ( !empty( $pic['url'] ) ) {
                    $newPic = self::getThumb( $pic['url'], $args );
                } else if ( !empty( $pic['path'] ) ) {
                    $newPic = self::getThumb( $pic['path'], $args );
                }
            } else {
                $newPic = self::getThumb( $pic, $args );
            }
            if ( !empty( $newPic ) && is_array( $pic ) ) {
                // add info (title and description)
                $newPics[] = array_merge( $pic, $newPic );
            } else if ( !empty( $newPic ) ) {
                $newPics[] = $newPic;
            } else {
                $newPics[] = $pic;
            }
        }

        return $newPics;
    }

    /**
     * Returns a comma seperated list with images
     *
     * @param {int} $postid
     * @param {array} $args (singlequotes, quotes)
     * @return {string}
     */
    public static function getImageString( $postid = null, $args = [] ) {
        if ( empty( $postid ) ) {
            global $postid;
        }
        $images = self::get( $postid );
        if ( empty( $images ) ) {
            return '';
        }
        $imageList = [];
        foreach ( $images as $image ) {
            $imageList[] = $image['path'];
        }
        $imageString = '';
        if ( !empty( $args['quotes'] ) ) {
            $imageString = '"' . implode( '","', $imageList ) . '"';
        } elseif ( !empty( $args['singlequotes'] ) ) {
            $imageString = "'" . implode( "','", $imageList ) . "'";
        } else {
            $imageString = implode( ',', $imageList );
        }

        return $imageString;
    }

    /**
     * Get path to thumb.php
     *
     * @param string $filepath
     * @param array $args
     * @return string
     */
    public static function getThumbUrl( $filepath, $args = [] ) {
        $thumb = self::getThumb( $filepath, $args );
        $thumbUrl = ( !empty( $thumb['url'] ) ? $thumb['url'] : get_bloginfo( 'wpurl' ) . '/' . $thumb['path'] );
        $thumbUrl = str_replace( '//wp-content', '/wp-content', $thumbUrl );

        return $thumbUrl;
    }

    /**
     * Get thumb (wrapper for Thumb->getThumb()
     *
     * @param string $filepath
     * @param array $args
     * @return array
     */
    public static function getThumb( $filepath, $args = [] ) {
        if ( empty( $args['width'] ) ) {
            $args['width'] = 1000;
        }
        if ( empty( $args['height'] ) ) {
            $args['height'] = 1000;
        }
        if ( !isset( $args['scale'] ) ) {
            $args['scale'] = 1;
        }
        $args['path'] = str_replace( get_bloginfo( 'wpurl' ), '', $filepath );

        $thumbInstance = Thumb::getInstance();
        $thumb = $thumbInstance->getThumb( $args );

        return $thumb;
    }

    /**
     * Check if post has a thumb or a postgallery-image
     *
     * @param int $postid
     * @return int
     */
    public static function hasPostThumbnail( $postid = 0 ) {
        if ( empty( $postid ) && empty( $GLOBALS['post'] ) ) {
            return;
        }
        if ( empty( $postid ) ) {
            $postid = $GLOBALS['post']->ID;
        }

        if ( empty( $postid ) ) {
            return false;
        }

        if ( has_post_thumbnail( $postid ) || is_admin() ) {
            return has_post_thumbnail( $postid );
        } else {
            return count( self::get( $postid ) );
        }
    }

    /**
     * Parse an string of image options to a string for html-attributes
     *  Input string has a key|value pair in every line.
     *
     * @param $imageOptions
     * @return string
     */
    private static function parseImageOptions( $imageOptions ) {
        $imageOptionsParsed = '';
        if ( empty( $imageOptions ) ) {
            return '';
        }
        foreach ( explode( "\n", $imageOptions ) as $imageOption ) {
            $imageOption = explode( '|', $imageOption );
            $imageOptionsParsed .= ' ' . $imageOption[0];

            if ( !empty( $imageOption[1] ) ) {
                $imageOptionsParsed .= '="' . htmlspecialchars( $imageOption[1] ) . '"';
            }
        }

        return $imageOptionsParsed;
    }


    /**
     * Creates an attachment-post if not exists
     *
     * @param $fullUrl
     * @param $parentId
     * @return int|null|string|\WP_Error
     */
    public static function checkForAttachmentData( $fullUrl, $parentId ) {
        if ( strpos( $fullUrl, '/gallery/' ) === false ) {
            return false;
        }

        $attachmentId = self::getAttachmentIdByUrl( $fullUrl );

        if ( !empty( $attachmentId ) ) {
            return $attachmentId;
        }

        // get relative path
        $uploads = wp_upload_dir();
        $uploadDir = $uploads['basedir'];
        $uploadUrl = $uploads['baseurl'];
        $path = str_replace( [
            $uploadUrl,
            $uploadDir,
        ], '', $fullUrl );


        // no attachment exists, create new

        // Check the type of file. We'll use this as the 'post_mime_type'.
        $filetype = wp_check_filetype( basename( $path ), null );
        $pathSplit = explode( '/', $path );
        $filename = array_pop( $pathSplit );

        // Prepare an array of post data for the attachment.
        $attachment = array(
            'guid' => $fullUrl,
            'post_mime_type' => $filetype['type'],
            'post_title' => '',
            'post_content' => '',
            'post_status' => 'inherit',
        );

        // Insert the attachment.
        $attachmentId = wp_insert_attachment( $attachment, $path, $parentId );

        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        // Generate the metadata for the attachment, and update the database record.
        PostGalleryAdmin::fixAttachmentPath( $attachmentId, $fullUrl );

        return $attachmentId;
    }


    /**
     * Gets first image (for example to use as post_thumbnail)
     *
     * @param string $size
     * @param null|int $post_id
     * @return bool|array(width, height, size, url, orientation)
     * @throws \ImagickException
     */
    public static function getFirstImage( $size = 'post-thumbnail', $post_id = null ) {
        if ( empty( $post_id ) ) {
            $post_id = $GLOBALS['post']->ID;
        }
        // get id from main-language post
        if ( class_exists( 'SitePress' ) ) {
            global $sitepress;

            $post_id = icl_object_id( $post_id, 'any', true, $sitepress->get_default_language() );
        }

        $postGalleryImages = self::get( $post_id );
        if ( !count( $postGalleryImages ) ) {
            return false;
        }

        $firstThumb = array_shift( $postGalleryImages );

        if ( empty( $size ) ) {
            $size = 'post-thumbnail';
        }

        // get width of thumbnail
        $width = intval( get_option( "{$size}_size_w" ) );
        $height = intval( get_option( "{$size}_size_h" ) );
        $crop = intval( get_option( "{$size}_crop" ) );

        if ( empty( $width ) && empty( $height ) ) {
            global $_wp_additional_image_sizes;
            if ( !empty( $_wp_additional_image_sizes ) &&
                !empty( $_wp_additional_image_sizes[$size] )
            ) {
                $width = $_wp_additional_image_sizes[$size]['width'];
                $height = $_wp_additional_image_sizes[$size]['height'];
            }
        }

        if ( empty( $width ) ) {
            $width = '1920';
        }
        if ( empty( $height ) ) {
            $height = '1080';
        }

        $path = $firstThumb['path'];
        $path = explode( '/wp-content/', $path );
        $path = '/wp-content/' . array_pop( $path );

        if ( $size !== 'full' ) {
            $thumbInstance = new Thumb();
            $thumb = $thumbInstance->getThumb( [
                'path' => $path,
                'width' => $width,
                'height' => $height,
                'scale' => 2,
            ] );
        } else {
            $filesize = getimagesize( ABSPATH . $path );
            $thumb = [
                'width' => $filesize[0],
                'height' => $filesize[1],
                'url' => get_bloginfo( 'wpurl' ) . $path,
            ];
        }

        $width = $height = 'auto';

        $orientation = ' wide';

        if ( $thumb['width'] >= $thumb['height'] ) {
            $width = $thumb['width'];
        } else {
            $height = $thumb['height'];
            $orientation = ' upright';
        }

        return [
            'width' => $width,
            'height' => $height,
            'orientation' => $orientation,
            'thumb' => $thumb,
            'url' => $thumb['url'],
            'orgPath' => $path,
            'size' => $size,
        ];
    }

    /**
     * Get an attachment ID given a URL.
     *
     * @param string $url
     *
     * @return int Attachment ID on success, 0 on failure
     */
    public static function getAttachmentIdByUrl( $url ) {
        global $wpdb;

        // get relative path
        $uploads = wp_upload_dir();
        $uploadDir = $uploads['basedir'];
        $uploadUrl = $uploads['baseurl'];
        $path = str_replace( [
            $uploadUrl,
            $uploadDir,
            '/gallery/',
        ], [
            '',
            '',
            'gallery/',
        ], $url );

        $statement = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s", '%' . $path );
        $attachment_id = $wpdb->get_var( $statement );

        if ( !empty( $attachment_id ) ) {
            return $attachment_id;
        }

        // fallback
        $attachment_id = 0;
        $dir = wp_upload_dir();
        if ( false !== strpos( $url, $dir['baseurl'] . '/' ) ) { // Is URL in uploads directory?
            $query_args = array(
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'fields' => 'ids',
                'meta_query' => array(
                    array(
                        'value' => $path,
                        'compare' => 'LIKE',
                        'key' => '_wp_attached_file',
                    ),
                ),
            );
            $query = new \WP_Query( $query_args );
            if ( $query->have_posts() ) {
                foreach ( $query->posts as $attachment_id ) {
                    return $attachment_id;
                }
            }
        }
        return $attachment_id;
    }

    /**
     * Checks if an url is a attachment
     *
     * @param string $attachmentUrl
     * @return bool
     */
    public static function urlIsThumbnail( $attachmentUrl = '' ) {
        // If there is no url, return.
        if ( '' == $attachmentUrl )
            return true;

        // Get the upload directory paths
        $upload_dir_paths = wp_upload_dir();

        // Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
        if ( false !== strpos( $attachmentUrl, $upload_dir_paths['baseurl'] ) ) {

            // If this is the URL of an auto-generated thumbnail, get the URL of the original image
            $attachmentUrlNew = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachmentUrl );
            if ( strcmp( $attachmentUrlNew, $attachmentUrl ) === 0 ) {
                return false;
            }
        }

        return true;
    }
}