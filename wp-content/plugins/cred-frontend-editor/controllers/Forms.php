<?php
final class CRED_Forms_Controller extends CRED_Abstract_Controller
{
	public function testNotification()
    {
        if (
            isset($_POST['cred_form_id']) && 
            isset($_POST['cred_test_notification_data'])
            //&& verify nonce
        )
        {
            $notification=$_POST['cred_test_notification_data'];
            $form_id=intval($_POST['cred_form_id']);
            CRED_Loader::load('CLASS/Notification_Manager');
            $results=CRED_Notification_Manager::sendTestNotification($form_id, $notification);
            echo json_encode($results);
            die();
        }
        echo json_encode(array('error'=>'not allowed'));
        die();
    }
    
    public function suggestUserMail($get, $post)
    {
        if ( !current_user_can(CRED_CAPABILITY) ) wp_die();
        
        global $wpdb;
        
        //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/187413931/comments
        $user=esc_sql(cred_wrap_esc_like($post['user']));
        $sql="SELECT user_nicename AS label, user_email AS value FROM {$wpdb->users} WHERE user_nicename LIKE '%$user%' ORDER BY user_email,user_nicename LIMIT 0, 100";
        $results=$wpdb->get_results($sql);
        
        echo json_encode($results);
    }
    
    public function updateFormFields($get,$post)
    {
        if ( !current_user_can(CRED_CAPABILITY) ) wp_die();
        
		$form_id = $post['form_id'];
        $fields = $post['fields'];
        $fm=CRED_Loader::get('MODEL/Forms');
        $fm->updateFormCustomFields($form_id,$fields);
        
        echo json_encode(true);
        die();
    }
    
	public function updateFormField($get,$post)
    {
        if ( !current_user_can(CRED_CAPABILITY) ) wp_die();
        
		if (!isset($post['form_id']))
        {
            echo json_encode(false);
            die();
        }
            
		$form_id = $post['form_id'];
        $field = $post['field'];
        $value = $post['value'];
        $fm=CRED_Loader::get('MODEL/Forms');
        $fm->updateFormCustomField($form_id, $field, $value);
        
        echo json_encode(true);
        die();
    }
    
    public function getPostFields($get,$post)
	{
        if ( !current_user_can(CRED_CAPABILITY) ) wp_die();
        
        if (!isset($post['post_type']))
        die();
            
        $post_type=$post['post_type'];
		
        $fields_model=CRED_Loader::get('MODEL/Fields');
        $fields_all = $fields_model->getFields($post_type);
        
        $settings_model = CRED_Loader::get('MODEL/Settings');
        $settings = $settings_model->getSettings();   
        $publickey = $settings['recaptcha']['public_key'];
        $privatekey = $settings['recaptcha']['private_key'];
        
        $fields_all['extra_fields']['recaptcha']['public_key'] = $publickey;
        $fields_all['extra_fields']['recaptcha']['private_key'] = $privatekey;
        
        echo json_encode($fields_all);
	die();
	}
	
    public function getFormFields($get,$post)
    {
        if ( !current_user_can(CRED_CAPABILITY) ) wp_die();
        
		if (!isset($post['form_id']))
        {
            die();
        }
		$form_id = $post['form_id'];
        $fm=CRED_Loader::get('MODEL/Forms');
        $fields = $fm->getFormCustomFields($form_id);
        
        echo json_encode($fields);
        die();
    }
    
    public function getFormField($get,$post)
    {
        if ( !current_user_can(CRED_CAPABILITY) ) wp_die();
        
		if (!isset($post['form_id']))
        {
            die();
        }
		$form_id = $post['form_id'];
        $field = $post['field'];
        $fm=CRED_Loader::get('MODEL/Forms');
        $value = $fm->getFormCustomField($form_id,$field);
        
        echo json_encode($value);
        die();
    }
    
    
    // export forms to XML and download
    public function exportForm($get,$post)
    {
        if ( !current_user_can(CRED_CAPABILITY) ) wp_die();
        
        if (isset($get['form']) && isset($get['_wpnonce']))
        {
            if (wp_verify_nonce($get['_wpnonce'],'cred-export-'.$get['form']))
            {
                CRED_Loader::load('CLASS/XML_Processor');
                $filename=isset($get['filename'])?urldecode($get['filename']):'';
                CRED_XML_Processor::exportToXML(array($get['form']), isset($get['ajax']), $filename);
                die();
            }
        }
        die();
    }
    
    public function exportSelected($get,$post)
    {
        if ( !current_user_can(CRED_CAPABILITY) ) wp_die();
        
        if (isset($_REQUEST['checked']) && is_array($_REQUEST['checked']))
        {
            check_admin_referer('cred-bulk-selected-action','cred-bulk-selected-field');
            CRED_Loader::load('CLASS/XML_Processor');
            $filename=isset($_REQUEST['filename'])?urldecode($_REQUEST['filename']):'';
            CRED_XML_Processor::exportToXML((array)$_REQUEST['checked'], isset($get['ajax']), $filename);
            die();
        }
        die();
    }
    
    public function exportAll($get,$post)
    {
        if ( !current_user_can(CRED_CAPABILITY) ) wp_die();
        
        if (isset($get['all']) && isset($get['_wpnonce']))
        {
            if (wp_verify_nonce($get['_wpnonce'],'cred-export-all'))
            {
                CRED_Loader::load('CLASS/XML_Processor');
                $filename=isset($get['filename'])?urldecode($get['filename']):'';
                CRED_XML_Processor::exportToXML('all', isset($get['ajax']), $filename);
                die();
            }
        }
        die();
    }
}
