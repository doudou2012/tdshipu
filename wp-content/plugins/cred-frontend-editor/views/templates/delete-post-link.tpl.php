<?php if (!defined('ABSPATH'))  die('Security check'); ?>

<div style='display:none'>
<?php if ($include_js) : ?>
<script type='text/javascript'>
function _cred_cred_parse_url(__url__, __params__)
{
    var __urlparts__=__url__.split('?'), __urlparamblocks__, __paramobj__, __p__, __v__, __query_string__=[], __ii__;
    if (__urlparts__.length>=2) 
    {

        __urlparamblocks__=__urlparts__[1].split(/[&;]/g);
        for (__ii__=0; __ii__<__urlparamblocks__.length; __ii__++)
        {
            __paramobj__=__urlparamblocks__[__ii__].split('=');
            __p__=decodeURIComponent(__paramobj__[0]);
            if (__paramobj__[1])
                __v__=decodeURIComponent(__paramobj__[1]);
            else
                __v__=false;
                
            if (__params__.remove && __params__.remove.length)
            {
                if (__params__.remove.indexOf(__p__)>-1)
                    continue;
            }
            if (__v__)
                __query_string__.push(encodeURIComponent(__p__)+'='+encodeURIComponent(__v__));
            else
                __query_string__.push(encodeURIComponent(__p__));
        }
        if (__params__.add)
        {
            for (__ii__ in __params__.add)
            {
                if (__params__.add.hasOwnProperty(__ii__))
                {
                    if (__params__.add[__ii__])
                        __query_string__.push(encodeURIComponent(__ii__)+'='+encodeURIComponent(__params__.add[__ii__]));
                    else
                        __query_string__.push(encodeURIComponent(__ii__));
                }
            }
        }
        if (__query_string__.length)
        {
            __query_string__=__query_string__.join('&');
            __url__=__urlparts__[0]+'?'+__query_string__;
        }
        else
        {
            __url__=__urlparts__[0];
        }
    }
    return __url__;
}

function _cred_cred_delete_post_handler(__isFromLink__, __link__, __url__, __result__, __message__, __message_show__)
{
    var __ltext__='';
    
    /*if (typeof __isFromLink__=='undefined')
        __isFromLink__=false;*/
        
    if (__isFromLink__) // callback from link click
    {
        if ( __message_show__ ) {
            if (undefined === __message__)
            {
                __message__ = '';
            }
            var __go__=confirm(__message__ == '' ? '<?php echo esc_js(__('Are you sure you want to delete this post?', 'wp-cred')); ?>' : __message__);
            if (!__go__) return false;
        }

        if (__link__.text)
            __ltext__=__link__.text;
        else if (__link__.innerText)
            __ltext__=__link__.innerText;

        var __deltext__='<?php echo esc_js(__('Deleting..', 'wp-cred')); ?>';
        // static storage of reference texts of related post delete links
        _cred_cred_delete_post_handler.refs=_cred_cred_delete_post_handler.refs || {};
        if (!_cred_cred_delete_post_handler.refs[__link__.id])
            _cred_cred_delete_post_handler.refs[__link__.id]=__ltext__;
        if (__link__.text)
            __link__.text=__deltext__;
        else if (__link__.innerText)
            __link__.innerText=__deltext__;
        
        __link__.href=_cred_cred_parse_url(__link__.href, {
            remove : ['_cred_link_id', '_cred_url'],
            add : {
                '_cred_link_id': __link__.id,
                '_cred_url': ''
            }
        });
        
        // this is set to refresh page
        if (__link__.className.indexOf('cred-refresh-after-delete')>=0)
            __link__.href=_cred_cred_parse_url(__link__.href, {
                remove : ['_cred_url'],
                add : {
                    '_cred_url': document.location
                }
            });
        
        return true;
    }
    else // callback from iframe return function
    {
        // success
        if (__result__ && 101==__result__)
        {
<?php
if ( !empty($message_after) ) {
    echo 'alert(\'';
    echo esc_js($message_after);
    echo '\');';
    echo PHP_EOL;

}
?>
            var __linkel__=document.getElementById(__link__);
            if (__linkel__.text)
                __linkel__.text=_cred_cred_delete_post_handler.refs[__link__];
            else if (__linkel__.innerText)
                __linkel__.innerText=_cred_cred_delete_post_handler.refs[__link__];
                
            if (__url__ && __linkel__.className.indexOf('cred-refresh-after-delete')>=0)
            {
                // refresh current page
                if (__url__==document.location)
                    location.reload();
                else // redirect
                    document.location=__url__;
            }
        }
        else
        {
            if (202==__result__)
                alert('<?php echo esc_js(__('Post delete failed','wp-cred')); ?>');
            else if (404==__result__)
                alert('<?php echo esc_js(__('No post defined','wp-cred')); ?>');
            else if (505==__result__)
                alert('<?php echo esc_js(__('Permission denied','wp-cred')); ?>');
        }
    }
}
</script>
<?php endif; ?>
<?php $iframehandle=$link_id.'_iframe'; ?>
<iframe name='<?php echo $iframehandle; ?>' id='<?php echo $iframehandle; ?>' src=''></iframe>
</div>
<a href='<?php echo $link; ?>' <?php if ($link_atts!==false) echo $link_atts; ?> id='<?php echo $link_id; ?>' target='<?php echo $iframehandle; ?>' onclick='return _cred_cred_delete_post_handler(true, this, false, false, "<?php echo esc_js($message); ?>", <?php echo intval($message_show); ?>);'><?php echo $text; ?></a>
