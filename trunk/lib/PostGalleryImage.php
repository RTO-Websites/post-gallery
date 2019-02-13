<?php
/**
 * @since 1.0.0
 * @author shennemann
 * @licence MIT
 */

namespace Lib;

use Admin\PostGalleryAdmin;

class PostGalleryImage {
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
     * Parse an string of image options to a string for html-attributes
     *  Input string has a key|value pair in every line.
     *
     * @param $imageOptions
     * @return string
     */
    public static function parseImageOptions( $imageOptions ) {
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