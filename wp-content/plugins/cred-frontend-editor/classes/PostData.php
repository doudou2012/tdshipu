<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PostData
 *
 * @author Franko
 */
class PostData {
    public function __construct() {
    }
    
    public function getPostData($post_id)
    {
        if ($post_id)
        {
            $fm = CRED_Loader::get('MODEL/Forms');
            $data = $fm->getPost($post_id);

            if ($data && isset($data[0]))
            {
                $mypost=$data[0];
                $myfields=isset($data[1])?$data[1]:array();
                $mytaxs=isset($data[2])?$data[2]:array();
                $myextra=isset($data[3])?$data[3]:array();
                if (isset($mypost->post_title))
                    $myfields['post_title']=array($mypost->post_title);
                if (isset($mypost->post_content))
                    $myfields['post_content']=array($mypost->post_content);
                if (isset($mypost->post_excerpt))
                    $myfields['post_excerpt']=array($mypost->post_excerpt);
                if (isset($mypost->post_parent))
                    $myfields['post_parent']=array($mypost->post_parent);
                
                return (object) array(
                    'post'=>$mypost,
                    'fields'=>$myfields,
                    'taxonomies'=>$mytaxs,
                    'extra'=>$myextra
                );
            }
            return $this->error(__('Post does not exist', 'wp-cred'));
        }
        return null;
    }
}

?>
