<?php
/**
 * @since 1.0.0
 * @author shennemann
 * @licence MIT
 */

namespace Lib;

class PostGalleryHelper {
    /**
     * Helper function, find value in mutlidimensonal array
     *
     * @param $array
     * @param $key
     * @return array
     */
    public static function arraySearch( $array, $key ) {
        $results = [];

        if ( is_array( $array ) ) {
            if ( isset( $array[$key] ) ) {
                $results[] = $array[$key];
            }

            foreach ( $array as $subarray ) {
                $results = array_merge( $results, self::arraySearch( $subarray, $key ) );
            }
        }

        return $results;
    }


    /**
     * Returns a post in default language
     *
     * @param {int} $post_id
     * @return boolean|object
     */
    public static function getOrgPost( $currentPostId ) {
        if ( class_exists( 'SitePress' ) ) {
            global $locale, $sitepress;

            $orgPostId = icl_object_id( $currentPostId, 'any', true, $sitepress->get_default_language() );
            //icl_ob
            if ( $currentPostId !== $orgPostId ) {
                $mainLangPost = get_post( $orgPostId );
                return $mainLangPost;
            }
        }
        return false;
    }

}