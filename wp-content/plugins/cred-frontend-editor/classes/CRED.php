<?php
require "StaticClass.php";
require "CredForm.php";

/**
 *
 * $HeadURL: https://www.onthegosystems.com/misc_svn/crud/tags/1.3.4/classes/CRED.php $
 * $LastChangedDate: 2014-11-03 14:29:53 +0000 (Mon, 03 Nov 2014) $
 * $LastChangedRevision: 28463 $
 * $LastChangedBy: francesco $
 *
 */
if (!function_exists("cred_wrap_esc_like")) {
    function cred_wrap_esc_like( $text ) { 
        global $wpdb;
        if ( method_exists( $wpdb, 'esc_like' ) ) { 
            return $wpdb->esc_like( $text ); 
        } else { 
            return addcslashes( $text, '_%\\' ); 
        } 
    } 
}
/*
function try_to_remove_view_shortcode() {
    remove_shortcode('wpv-post-body');
}

function try_to_add_view_shortcode() {
    add_shortcode('wpv-post-body','wpv_shortcode_wpv_post_body');
}
*/

/**
 * Main Class
 *
 * Main class of the plugin
 * Class encapsulates all hook handlers
 *
 */
final class CRED_CRED
{
    public static $help=array();
    public static $help_link_target='_blank';
    public static $settingsPage=null;
    private static $prefix='_cred_';
    
    /*
     * Initialize plugin enviroment
    */
    public static function init()
    {
        // plugin init
        // NOTE Early Init, in order to catch up with early hooks by 3rd party plugins (eg CRED Commerce)
        add_action( 'init', array('CRED_CRED', '_init_'), 1 );
        CRED_Loader::load('CLASS/Notification_Manager');
        CRED_Notification_Manager::init();
        
        /*
        if (isset($_GET['cred-edit-form'])&&!empty($_GET['cred-edit-form'])) {
            add_action('the_content', 'try_to_remove_view_shortcode', 0);
            add_action('the_content', 'try_to_add_view_shortcode', 9);
        }
        */

        // try to catch user shortcodes (defined by [...]) and solve shortcodes inside shortcodes
        // adding filter with priority before do_shortcode and other WP standard filters
        add_filter( 'the_content', 'cred_do_shortcode', 9 );
    }
    
    // main init hook
    public static function _init_()
    {
        global $wp_version, $post;

        // load help settings (once)
        self::$help=CRED_Loader::getVar(CRED_INI_PATH."/help.ini.php");
        // set up models and db settings
        CRED_Helper::prepareDB();
        // needed by others
        self::$settingsPage=admin_url('admin.php').'?page=CRED_Settings';
        // localize forms, support for WPML
        CRED_Helper::localizeForms();
        // setup custom capabilities
        CRED_Helper::setupCustomCaps();
        // setup extra admin hooks for other plugins
        CRED_Helper::setupExtraHooks();

        if(is_admin())
        {
            // add plugin menus
            // setup js, css assets
            CRED_Helper::setupAdmin();

            // add media buttons for cred forms at editor
            if (version_compare($wp_version, '3.1.4', '>'))
            {
                add_action('media_buttons', array(__CLASS__, 'addFormsButton'),20, 2);
            }
            else
            {
                add_action('media_buttons_context', array(__CLASS__, 'addFormsButton'), 20, 2);
            }

            // integrate with Views
            add_filter('wpv_meta_html_add_form_button', array(__CLASS__, 'addCREDButton'), 20, 2);

            //WATCHOUT: remove custom meta boxes from cred forms (to avoid any problems)
            // add custom meta boxes for cred forms
            add_action('add_meta_boxes_' . CRED_FORMS_CUSTOM_POST_NAME, array(__CLASS__, 'addMetaBoxes'), 20, 1);

            // save custom fields of cred forms
            add_action('save_post', array(__CLASS__, 'saveFormCustomFields'), 10, 2);
            // IMPORTANT: drafts should now be left with post_status=draft, maybe show up because of previous versions
            add_filter('wp_insert_post_data', array(__CLASS__,'forcePrivateforForms'));
        }
        else
        {
            // init form processing to check for submits
            CRED_Loader::load('CLASS/Form_Builder');
            CRED_Form_Builder::init();
        }
        // add form short code hooks and filters, to display forms on front end
        CRED_Helper::addShortcodesAndFilters();

        // handle Ajax calls
        CRED_Router::addCalls(array(
            'cred_skype_ajax'=>array(
                'nopriv'=>true,
                'callback'=>array(__CLASS__,'cred_skype_ajax')
            ),
            'cred-ajax-tag-search'=>array(
                'nopriv'=>true,
                'callback'=>array(__CLASS__,'cred_ajax_tag_search')
            ),
            'cred-ajax-delete-post'=>array(
                'nopriv'=>true,
                'callback'=>array(__CLASS__,'cred_ajax_delete_post')
            )
        ));

        CRED_Router::addRoutes('cred', array(
            'Forms'=>0, // Forms controller
            'Posts'=>0, // Posts controller
            'Settings'=>0, // Settings controller
            'Generic_Fields'=>0  // Generic Fields controller
        ));
        /*CRED_Router::addPages('cred', array(
        ));*/
    }

    public static function route($path='', $params=null, $raw=true)
    {
        return CRED_Router::getRoute('cred', $path, $params, $raw);
    }

    public static function routeAjax($action)
    {
        return admin_url('admin-ajax.php').'?action='.$action;
    }

    // duplicated from wp ajax function
    public static function cred_ajax_tag_search()
    {
        global $wpdb;

        if ( isset( $_GET['tax'] ) ) {
            $taxonomy = sanitize_key( $_GET['tax'] );
            $tax = get_taxonomy( $taxonomy );
            if ( ! $tax )
                wp_die( 0 );
            // possible issue here, anyway bypass for now
            /*if ( ! current_user_can( $tax->cap->assign_terms ) )
                wp_die( -1);*/
        } else {
            wp_die( 0 );
        }

        $s = stripslashes( $_GET['q'] );

        $comma = _x( ',', 'tag delimiter' );
        if ( ',' !== $comma )
            $s = str_replace( $comma, ',', $s );
        if ( false !== strpos( $s, ',' ) ) {
            $s = explode( ',', $s );
            $s = $s[count( $s ) - 1];
        }
        $s = trim( $s );
        if ( strlen( $s ) < 2 )
            wp_die(); // require 2 chars for matching

        global $sitepress, $wp_version;
        $post_id = $_GET['post_id'];
        if (isset($sitepress)&&isset($post_id)) {
            $post_type = get_post_type($post_id);
            $post_language = $sitepress->get_element_language_details($post_id, 'post_' . $post_type);
            $lang = $post_language->language_code;
            $current_language = $sitepress->get_current_language();
            //$sitepress->switch_lang($post_language->language_code, false);            
            //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/187413931/comments
            $results = $wpdb->get_col( $wpdb->prepare( "SELECT t.name FROM $wpdb->term_taxonomy AS tt INNER JOIN $wpdb->terms AS t ON tt.term_id = t.term_id JOIN {$wpdb->prefix}icl_translations tr ON tt.term_taxonomy_id = tr.element_id WHERE tt.taxonomy = %s AND tr.language_code = %s AND tr.element_Type = %s AND t.name LIKE (%s)", $taxonomy, $lang, 'tax_'.$taxonomy, '%' . cred_wrap_esc_like( $s ) . '%' ) );
            //$sitepress->switch_lang($current_language);
        } else {            
            //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/187413931/comments
            $results = $wpdb->get_col( $wpdb->prepare( "SELECT t.name FROM $wpdb->term_taxonomy AS tt INNER JOIN $wpdb->terms AS t ON tt.term_id = t.term_id WHERE tt.taxonomy = %s AND t.name LIKE (%s)", $taxonomy, '%' . cred_wrap_esc_like( $s ) . '%' ) );
        }

        echo join( $results, "\n" );
        wp_die();
    }

    public static function cred_ajax_delete_post()
    {
        CRED_Loader::get("CONTROLLER/Posts")->deletePost($_GET,$_POST);
        wp_die();
    }

    // link CRED ajax call to wp-types ajax call (use wp-types for this)
    public static function cred_skype_ajax()
    {
        do_action('wp_ajax_wpcf_ajax');
        wp_die();
    }

    public static function getPostAdminEditLink($post_id)
    {
        return admin_url('post.php').'?action=edit&post='.$post_id;
    }

    public static function getFormEditLink($form_id)
    {
        //return admin_url('post.php').'?action=edit&post='.$form_id;
        return get_edit_post_link($form_id);
    }

    public static function getNewFormLink($abs=true)
    {
        return ($abs)?admin_url('post-new.php').'?post_type='.CRED_FORMS_CUSTOM_POST_NAME:'post-new.php?post_type='.CRED_FORMS_CUSTOM_POST_NAME;
    }

    public static function forcePrivateforForms($post)
    {
        if (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post;

        if (CRED_FORMS_CUSTOM_POST_NAME != $post['post_type']) return $post;

        if (isset($post['ID']) && !current_user_can( 'edit_post', $post['ID'] ) ) return $post;

        if (isset($post['ID']) && wp_is_post_revision( $post['ID'] ) ) return $post;

        if ('auto-draft'==$post['post_status'])  return $post;

        $post['post_status'] = 'private';
        return $post;
    }

   // when form is submitted from admin, save the custom fields which describe the form configuration to DB
   public static function saveFormCustomFields($post_id, $post)
   {
        if (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

        if (wp_is_post_revision( $post_id ) ) return;
        
        if (CRED_FORMS_CUSTOM_POST_NAME != $post->post_type) return;

        if (!current_user_can( 'edit_post', $post_id ) ) return;

        // hook not called from admin edit page, return
        if (empty($_POST) || !isset($_POST['cred-admin-post-page-field']) || !wp_verify_nonce($_POST['cred-admin-post-page-field'],'cred-admin-post-page-action'))  return;
        
        if (isset($_POST['_cred']) && is_array($_POST['_cred']) && !empty($_POST['_cred']))
        {
            // new format
            $model = CRED_Loader::get('MODEL/Forms');
            
            // settings (form, post, actions, messages, css etc..)
            $settings=new stdClass;
            $settings->form=isset($_POST['_cred']['form'])?$_POST['_cred']['form']:array();
            $settings->post=isset($_POST['_cred']['post'])?$_POST['_cred']['post']:array();
            $settings->form=CRED_Helper::mergeArrays(array(
                'hide_comments'=>0,
                'has_media_button'=>0,
                'action_message'=>''
            ),$settings->form);
            
            // notifications
            $notification=new stdClass;
            $notification->notifications=array();
            // normalize order of notifications using array_values
            $notification->notifications=isset($_POST['_cred']['notification']['notifications'])
                                        ?array_values($_POST['_cred']['notification']['notifications'])
                                        :array();
            //we have notifications allways enabled
            //$notification->enable=isset($_POST['_cred']['notification']['enable'])?1:0;
            $notification->enable=1;
            foreach ($notification->notifications as $ii=>$nott)
            {
                if (isset($nott['event']['condition']) && is_array($nott['event']['condition']))
                {
                    // normalize order
                    $notification->notifications[$ii]['event']['condition']=array_values($notification->notifications[$ii]['event']['condition']);
                    $notification->notifications[$ii]['event']['condition']=CRED_Helper::applyDefaults($notification->notifications[$ii]['event']['condition'], array(
                        'field'=>'',
                        'op'=>'',
                        'value'=>'',
                        'only_if_changed'=>0
                    ));
                }
                else
                {
                    $notification->notifications[$ii]['event']['condition']=array();
                }
            }
            // extra            
            $__allowed_tags = wp_kses_allowed_html('post');
            $__allowed_protocols = array('http', 'https', 'mailto');
            $allowed_tags = $__allowed_tags;            
            $allowed_protocols = $__allowed_protocols;
            $extra_js = isset($_POST['_cred']['extra']['js'])?$_POST['_cred']['extra']['js']:'';
            $extra_css = isset($_POST['_cred']['extra']['css'])?$_POST['_cred']['extra']['css']:'';
            if (!empty($extra_css)) $extra_css = wp_kses($extra_css, $allowed_tags,  $allowed_protocols);            
            
            $messages=$model->getDefaultMessages();
            $extra=new stdClass;
            $extra->css=$extra_css;
            $extra->js=$extra_js;
            $extra->messages=(isset($_POST['_cred']['extra']['messages']))?$_POST['_cred']['extra']['messages']:$model->getDefaultMessages();
            
            // update
            $model->updateFormCustomFields($post_id, array(
                'form_settings'=>$settings,
                'notification'=>$notification,
                'extra'=>$extra
            ));
            
            // wizard
            if (isset($_POST['_cred']['wizard']))
                $model->updateFormCustomField($post_id, 'wizard', intval($_POST['_cred']['wizard']));
            
            // validation
            if (isset($_POST['_cred']['validation']))
                $model->updateFormCustomField($post_id, 'validation', $_POST['_cred']['validation']);
            else
                $model->updateFormCustomField($post_id, 'validation', array('success'=>1));
            
            // allow 3rd-party to do its own stuff on CRED form save
            do_action('cred_admin_save_form', $post_id, $post);
            
            // localize form with WPML
            //THIS code make notification on cred to be lost
            /*$data=CRED_Helper::localizeFormOnSave(array(
                'post'=>$post,
                'notification'=>$notification,
                'message'=>$settings->form['action_message'],
                'messages'=>$extra->messages
            ));
            $model->updateFormCustomField($post_id, 'notification', $data['notification']);*/
            CRED_Helper::localizeFormOnSave(array(
                'post'=>$post,
                'notification'=>$notification,
                'message'=>$settings->form['action_message'],
                'messages'=>$extra->messages
            ));
            
        }
   }

   // add meta boxes in admin pages which manipulate forms
   public static function addMetaBoxes($form)
   {
		global $pagenow;
		
		if (CRED_FORMS_CUSTOM_POST_NAME==$form->post_type)
		{
			$model = CRED_Loader::get('MODEL/Forms');
			$form_fields = $model->getFormCustomFields($form->ID, array('form_settings', 'notification', 'extra'));
			
			// add cred related classes to our metaboxes
			$metaboxes=array(
				// form type meta box
				'credformtypediv' => array(
					'title' => __('Form Settings','wp-cred'), 
					'callback' => array('CRED_Helper', 'addFormSettingsMetaBox'), 
					'post_type' => NULL, 
					'context' => 'normal',
					'priority' => 'high', 
					'callback_args' => $form_fields), 
				// post type meta box
				'credposttypediv' => array(
					'title' => __('Post Type Settings','wp-cred'), 
					'callback' => array('CRED_Helper', 'addPostTypeMetaBox'), 
					'post_type' => NULL, 
					'context' => 'normal',
					'priority' => 'high', 
					'callback_args' => $form_fields), 
				// content meta box to wrap rich editor, acts as placeholder
				'credformcontentdiv' => array(
					'title' => __('Form Content','wp-cred'), 
					'callback' => array('CRED_Helper', 'addFormContentMetaBox'), 
					'post_type' => NULL, 
					'context' => 'normal',
					'priority' => 'high', 
					'callback_args' => array()), 
				// extra meta box (css, js) (placed inside editor meta box)
				'credextradiv' => array(
					'title' => __('CSS and Javascript for this form','wp-cred'), 
					'callback' => array('CRED_Helper', 'addExtraAssetsMetaBox'), 
					'post_type' => NULL, 
					'context' => 'normal',
					'priority' => 'high', 
					'callback_args' => $form_fields), 
				// email notification meta box
				'crednotificationdiv' => array(
					'title' => __('Notification Settings','wp-cred'), 
					'callback' => array('CRED_Helper', 'addNotificationMetaBox'), 
					'post_type' => NULL, 
					'context' => 'normal',
					'priority' => 'high', 
					'callback_args' => $form_fields), 
				// messages meta box
				'credmessagesdiv' => array(
					'title' => __('Form Texts','wp-cred'), 
					'callback' => array('CRED_Helper', 'addMessagesMetaBox'), 
					'post_type' => NULL, 
					'context' => 'normal',
					'priority' => 'high', 
					'callback_args' => $form_fields)
			);
			// CRED_PostExpiration
			$metaboxes = apply_filters('cred_ext_meta_boxes', $metaboxes, $form_fields);
			if (defined('MODMAN_PLUGIN_NAME'))
				// module manager sidebar meta box
				$metaboxes['modulemanagerdiv'] =  array(
					'title' => __('Module Manager','wp-cred'), 
					'callback' => array('CRED_Helper', 'addModManMetaBox'), 
					'post_type' => NULL, 
					'context' => 'side',
					'priority' => 'default', 
					'callback_args' => array()
					);
				
			// do same for any 3rd-party metaboxes added to CRED forms screens
			$extra_metaboxes=apply_filters('cred_admin_register_meta_boxes', array());
			if (!empty($extra_metaboxes))
			{
				foreach ($extra_metaboxes as $mt)
					add_filter('postbox_classes_' . CRED_FORMS_CUSTOM_POST_NAME . "_$mt", array('CRED_Helper', 'addMetaboxClasses'));
			}
			
			// add defined meta boxes
			foreach ($metaboxes as $mt=>$mt_definition) {
				add_filter('postbox_classes_' . CRED_FORMS_CUSTOM_POST_NAME . "_$mt", array('CRED_Helper', 'addMetaboxClasses'));
				add_meta_box($mt, $mt_definition['title'], $mt_definition['callback'], $mt_definition['post_type'], $mt_definition['context'], $mt_definition['priority'], $mt_definition['callback_args']); 
			}
            
			// allow 3rd-party to add meta boxes to CRED form admin screen
			do_action('cred_admin_add_meta_boxes', $form);
		}
   }

   // add CRED button in 3rd-party (eg Views)
   public static function addCREDButton($v, $area)
   {
        static $id=1;

        $id++;
        $m=CRED_Loader::get('MODEL/Forms');
        $forms=$m->getFormsForTable(0,-1);

        $shortcode_but='';
        $shortcode_but = CRED_Loader::tpl('insert-form-shortcode-button-extra',array(
                'id'=>$id,
                'forms'=>$forms,
                'help'=>self::$help,
                'content'=>$area,
                'help_target'=>self::$help_link_target
        ));

        $out=$shortcode_but;

        return $out;
   }

   // function to handle the media buttons associated to forms, like  Scaffold,Insert Shortcode, etc..
   public static function addFormsButton($context, $text_area = 'textarea#content')
   {
        global $wp_version, $post;
        //static $add_only_once=0;

        if (!isset($post) || empty($post) || !isset($post->post_type)) {
            return '';
        }

        if ($post->post_type==CRED_FORMS_CUSTOM_POST_NAME)
        {
            // WP 3.3 changes ($context arg is actually a editor ID now)
            if (version_compare($wp_version, '3.1.4', '>') && !empty($context))
            {
                $text_area = $context;
            }

            $out='';
            if ('content'==$context)
            {
                $addon_buttons = array();
                $shortcode_but='';
                $shortcode_but = CRED_Loader::tpl('insert-field-shortcode-button',array(
                    'help'=>self::$help,
                    'help_target'=>self::$help_link_target

                ));

                $shortcode2_but='';
                $fields_model=CRED_Loader::get('MODEL/Fields');
                $shortcode2_but = CRED_Loader::tpl('insert-generic-field-shortcode-button',array(
                    'gfields'=>$fields_model->getTypesDefaultFields(),
                    'help'=>self::$help,
                    'help_target'=>self::$help_link_target
                ));

                $forms_model = CRED_Loader::get('MODEL/Forms');
                $settings = $forms_model->getFormCustomField($post->ID, 'form_settings');
                $scaffold_but='';
                $scaffold_but = CRED_Loader::tpl('scaffold-button',array(
                    'include_captcha_scaffold'=>isset($settings->form['include_captcha_scaffold'])?$settings->form['include_captcha_scaffold']:false,
                    'include_wpml_scaffold'=>isset($settings->form['include_wpml_scaffold'])?$settings->form['include_wpml_scaffold']:false,
                    'help'=>self::$help,
                    'help_target'=>self::$help_link_target
                ));

                $preview_but='';
                ob_start();
                ?><span id="cred-preview-button" class="cred-media-button">
                    <a class='button cred-button' href="javascript:;" title='<?php _e('Preview','wp-cred'); ?>'><i class="icon-camera ont-icon-18"></i> <?php _e('Preview','wp-cred'); ?></a>
                </span><?php
                $preview_but = ob_get_clean();

                $addon_buttons['scaffold'] = $scaffold_but;
                $addon_buttons['post_fields'] = $shortcode_but;
                $addon_buttons['generic_fields'] = $shortcode2_but;
                $addon_buttons['preview'] = $preview_but;
                $addon_buttons = apply_filters('cred_wpml_glue_generate_insert_button_block',$addon_buttons,$insert_after=2);
                $out=implode('&nbsp;',array_values($addon_buttons));
            }

            // WP 3.3 changes
            if (version_compare($wp_version, '3.1.4', '>')) {
                echo $out;
            } else {
                return $context . $out;
            }
        } else {
            if (is_string($context) && 'content'!=$context) // allow button only on main area
            {
                $out='';//self::addCREDButton('', $context);
                // WP 3.3 changes
                if (version_compare($wp_version, '3.1.4', '>'))
                {
                    echo $out;
                    return;
                }
                else
                {
                    return $context.$out;
                }
            }
            $fm=CRED_Loader::get('MODEL/Forms');
            $forms=$fm->getFormsForTable(0,-1);

            // WP 3.3 changes ($context arg is actually a editor ID now)
            if (version_compare($wp_version, '3.1.4', '>') && !empty($context))
            {
                $text_area = $context;
            }

            $addon_buttons = array();
            $shortcode_but='';
            $shortcode_but = CRED_Loader::tpl('insert-form-shortcode-button',array(
                    'forms'=>$forms,
                    'help'=>self::$help,
                    'help_target'=>self::$help_link_target
            ));
            $addon_buttons['cred_shortcodes'] = $shortcode_but;
            $out=implode('&nbsp;',array_values($addon_buttons));

            // WP 3.3 changes
            if (version_compare($wp_version, '3.1.4', '>'))
            {
                echo $out;
            }
            else
            {
                return $context . $out;
            }
        }
   }
}
