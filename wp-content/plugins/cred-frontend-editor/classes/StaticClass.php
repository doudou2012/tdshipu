<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StaticToGlobal
 *
 * @author Franko
 */
class StaticClass {
       
    public static $_cred_container_id;
    public static $_____friendsStatic_____=array(/* Friend Class Hashes as keys Here..*/);
    
    const METHOD='POST';                                         // form method POST
    const PREFIX='_cred_cred_prefix_'; 
                              // prefix for various hidden auxiliary fields
    const NONCE='_cred_cred_wpnonce';                            // nonce field name
    const POST_CONTENT_TAG='%__CRED__CRED__POST__CONTENT__%';    // placeholder for post content
    const FORM_TAG='%__CRED__CRED__FORM___FORM__%';              // 
    const DELAY=0; 
    
    // STATIC Properties
    public static $_staticGlobal=array(
        'ASSETS_PATH'=>null,                                    // physical path to files needed for Zebra form
        'ASSETS_URL'=>null,                                     // url for this physical path
        'MIMES'=>array(),                                       // WP allowed mime types (for file uploads)
        'LOCALES'=>null,                                        // global strings localization
        'RECAPTCHA'=>false,                                     // settings for recaptcha API
        'RECAPTCHA_LOADED'=>false,                              // flag indicating whether recaptcha API has been loaded
        'COUNT'=>0,                                             // number of forms rendered on same page
        'CACHE'=>array(),                                       // cache rendered forms here for future reference (eg by shortcodes)
        'CSS_LOADED'=>array(),                                  // references to CSS files that have been loaded
        'CURRENT_USER'=>null                                    // info about current user using the forms
    );
    
    public static function _pre($v) {
        echo "<pre>";
        print_r($v);
        echo "</pre>";
    }
    
    public static function parseFriendCallStatic($the)
    {
        $what=explode('_1_1_1_', $the);
        if (isset($what[0]) && isset($what[1]))
        {
            $hash=$what[0];
            $whatExactly=$what[1];
            $ref=false;
            if($whatExactly && '&'==$whatExactly[0])
            {
                $ref=true;
                $whatExactly=substr( $whatExactly, 1 );
            }
            return array($hash, $whatExactly, $ref);
        }
        return array(false, false, false);
    }
    
    public static function __getPrivStatic($prop)
    {
        return self::$$prop;
    }
    public static function &__getPrivStaticRef($prop)
    {
        return self::$$prop;
    }
    
}

?>
