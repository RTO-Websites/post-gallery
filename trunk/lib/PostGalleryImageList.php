<?php namespace Lib;

class PostGalleryImageList {
    static $cachedImages = [];
    public static $allAttachments = [];

    public function __construct() {
        self::getAllAttachmentIds();
    }

    /**
     * Sorting an image-array
     *
     * @param array $images
     * @param int $postid
     * @return array
     */
    public static function sort( array $images, int $postid ): array {
        // get post in default language
        $orgPost = PostGalleryHelper::getOrgPost( $postid );
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
    public static function get( $postid = null ): array {
        if ( empty( $postid ) && empty( $GLOBALS['post'] ) ) {
            return [];
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
        $orgPost = PostGalleryHelper::getOrgPost( $postid );
        if ( !empty( $orgPost ) ) {
            $post = $orgPost;
            $postid = $orgPost->ID;
            if ( isset( self::$cachedImages[$postid] ) ) {
                // check if image list is in cache
                return self::$cachedImages[$postid];
            }
        }

        if ( empty( $post ) || $post->post_type === 'attachment' ) {
            return [];
        }

        $uploads = wp_upload_dir();

        //$imageDir = strtolower(str_replace('http://', '', esc_url($post->post_title)));
        $imageDir = PostGalleryFilesystem::getImageDir( $post );
        $uploadDir = $uploads['basedir'] . '/gallery/' . $imageDir;
        $uploadFullUrl = $uploads['baseurl'] . '/gallery/' . $imageDir;
        $uploadUrl = str_replace( get_bloginfo( 'wpurl' ), '', $uploadFullUrl );
        $images = [];

        if ( !file_exists( $uploadDir ) || !is_dir( $uploadDir ) ) {
            return [];
        }
        $dir = scandir( $uploadDir );

        foreach ( $dir as $file ) {
            if ( is_dir( $uploadDir . '/' . $file ) ) {
                continue;
            }
            $fullUrl = $uploadFullUrl . '/' . $file;
            $path = $uploadUrl . '/' . $file;

            if ( PostGalleryImage::urlIsThumbnail( $fullUrl ) ) {
                continue;
            }
            $attachmentId = PostGalleryImage::checkForAttachmentData( $fullUrl, $postid );

            $info = self::getAttachmentInfo( $attachmentId, $postid );
            $file = $info['filename'];
            $images[$file] = $info;
        }

        $images = self::sort( $images, $postid );
        self::$cachedImages[$postid] = $images;
        return $images;
    }

    /**
     * Get list with attachments-data from an attachment-list
     *
     * @param array $imageIdList
     * @return array
     */
    public static function getByDynamic( $imageIdList ): array {
        $images = [];
        foreach ( $imageIdList as $item ) {
            if ( empty( $item) || empty( $item['id']) ) {
                continue;
            }
            $attachmentId = $item['id'];
            $attachment = get_post( $attachmentId );
            $info = self::getAttachmentInfo( $attachmentId, $attachment->post_parent );

            $file = $info['filename'];

            $images[$file] = $info;
        }

        return $images;
    }

    /**
     * Get info for attachment
     *
     * @param int $attachmentId
     * @param int $parentId
     * @return array
     */
    public static function getAttachmentInfo( $attachmentId, $parentId ): array {
        $alt = '';
        $imageTitle = '';
        $imageOptions = '';
        $imageDesc = '';
        $imageCaption = '';

        if ( !empty( $attachmentId ) ) {
            $attachment = get_post( $attachmentId );
            $alt = get_post_meta( $attachmentId, '_wp_attachment_image_alt', true );
            $imageOptions = get_post_meta( $attachmentId, 'postgallery-image-options', true );
            $imageCaption = wp_get_attachment_caption( $attachmentId );
            $imageTitle = '';
            $imageDesc = '';
            if ( !empty( $attachment ) ) {
                $imageTitle = $attachment->post_title;
                $imageDesc = $attachment->post_content;
            }
        }

        $imageOptionsParsed = PostGalleryImage::parseImageOptions( $imageOptions );

        $path = get_attached_file( $attachmentId );
        $file = basename( $path );
        $fullUrl = wp_get_attachment_url( $attachmentId );
        $shortPath = str_replace( get_bloginfo( 'wpurl' ), '', $fullUrl );


        $info = [
            'post_id' => $parentId,
            'post_title' => get_the_title( $parentId ),
            'attachmentId' => $attachmentId,
            'filename' => $file,
            'path' => $path,
            'url' => $fullUrl,
            'thumbURL' => get_bloginfo( 'wpurl' ) . '/?loadThumb&amp;path=' . $shortPath,
            'title' => $imageTitle,
            'desc' => $imageDesc,
            'alt' => $alt,
            'imageCaption' => $imageCaption,
            'imageOptions' => $imageOptions,
            'imageOptionsParsed' => $imageOptionsParsed,
            'srcset' => wp_get_attachment_image_srcset( $attachmentId, 'full' ),
        ];

        return $info;
    }

    /**
     * Returns a list of placeholder-images
     *
     * @param int $count
     * @return array
     */
    public static function getPseudoImages( int $count = 10 ): array {
        $images = [];

        for ( $i = 1; $i < $count; $i += 1 ) {
            $images[$i] = [
                'filename' => 'image-placeholder.png',
                'path' => POSTGALLERY_DIR . '/images/image-placeholder.png',
                'url' => POSTGALLERY_URL . '/images/image-placeholder.png',
                'thumbURL' => get_bloginfo( 'wpurl' ) . '/?loadThumb&amp;path=' . POSTGALLERY_URL . '/images/image-placeholder',
                'title' => 'Pseudo-Image ' . $i,
                'desc' => 'Lorem ipsum dolor med',
                'alt' => '',
                'post_id' => 0,
                'post_title' => 'Pseudo-Title',
                'imageOptions' => '',
                'imageOptionsParsed' => '',
                'attachmentId' => '',
                'srcset' => '',
                'isPlaceholder' => true,
            ];
        }

        return $images;
    }

    /**
     * Return an image-array with resized images
     *
     * @param int $postid
     * @param array $args
     * @return array
     */
    public static function getResized( int $postid = 0, array $args = [] ): array {
        $images = self::get( $postid );

        return self::resize( $images, $args );
    }


    /**
     * Generate thumb-path for an array of pics
     *
     * @param array $pics
     * @param array $args
     * @return array
     */
    public static function resize( array $pics, array $args ): array {
        if ( !is_array( $pics ) ) {
            return $pics;
        }
        $newPics = [];
        foreach ( $pics as $pic ) {
            // create resized image
            if ( is_array( $pic ) ) {
                if ( !empty( $pic['url'] ) ) {
                    $newPic = PostGalleryImage::getThumb( $pic['url'], $args );
                } else if ( !empty( $pic['path'] ) ) {
                    $newPic = PostGalleryImage::getThumb( $pic['path'], $args );
                }
            } else {
                $newPic = PostGalleryImage::getThumb( $pic, $args );
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
     * @param int $postid
     * @param array $args (singlequotes, quotes)
     * @return string
     */
    public static function getImageString( int $postid = null, array $args = [] ): string {
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


    public static function getAllAttachmentIds() {
        global $wpdb;
        $sql = "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' 
            AND meta_value LIKE '%gallery/%'";


        $result = $wpdb->get_results( $sql );

        $list = [];
        foreach ( $result as $data ) {
            $value = explode( '/uploads/', $data->meta_value );
            if ( count( $value ) > 1 ) {
                $value = $value[1];
            } else {
                $value = $value[0];
            }
            $list[$value] = $data->post_id;
        }

        self::$allAttachments = $list;
    }
}