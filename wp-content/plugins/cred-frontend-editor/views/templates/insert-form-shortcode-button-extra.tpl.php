<?php if (!defined('ABSPATH'))  die('Security check'); ?>
<!-- span inline-block as container for IE :p -->
<span class="cred-media-button cred-form-shortcode-button2">
    <?php if( ( isset($_GET['page']) && ( $_GET['page'] == 'views-editor' || $_GET['page'] == 'view-archives-editor' ) ) || defined('CT_INLINE') ) {?>
        <button class="js-code-editor-toolbar-button js-code-editor-toolbar-button-cred-icon code-editor-toolbar-button-cred-icon button-secondary">
            <i class="icon-cred-logo ont-icon-18"></i><span class="button-label"><?php echo esc_attr(__('CRED forms','wp-cred')); ?></span></button>
    <?php } else{?>
    <a href='javascript:;' class='button cred-icon-button cred-form-shortcode-button-button2' title='<?php echo esc_attr(__('Insert Forms','wp-cred')); ?>'><i class="icon-cred ont-icon-22"></i><?php echo __( 'CRED forms', 'wp-cred' ); ?></a>
    <?php }?>

    <div class="cred-popup-box cred-form-shortcodes-box2">

        <div class='cred-popup-heading'>
        <h3><?php _e('Choose which form to insert','wp-cred'); ?></h3>
        <i title='<?php echo esc_attr(__('Close','wp-cred')); ?>' class='icon-remove cred-close-button cred-cred-cancel-close'></i>
        </div>

        <div class="cred-popup-inner cred-form-shortcodes-box-inner2">
            <div class='cred-form-shortcode-types-select-container2'>
                <table class='cred-table cred-table-choose-form' cellpadding=0 cellspacing=0>
                    <tr>
                        <td colspan=2>
                            <label class='cred-label'><input type='radio' class='cred-shortcode-container-radio cred-radio-10 cred-post-creation-container2' name='cred-shortcode-container-<?php echo $id; ?>' value='1' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('New content form','wp-cred'); ?></span></label>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <label class='cred-label'><input type='radio' class='cred-shortcode-container-radio cred-radio-10 cred-post-edit-container2' name='cred-shortcode-container-<?php echo $id; ?>' value='2' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Edit content form','wp-cred'); ?></span></label>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <label class='cred-label'><input type='radio' class='cred-shortcode-container-radio cred-radio-10 cred-post-delete-link-container2' name='cred-shortcode-container-<?php echo $id; ?>' value='3' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Delete content link','wp-cred'); ?></span></label>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <label class='cred-label'><input type='radio' class='cred-shortcode-container-radio cred-radio-10 cred-post-child-link-container2' name='cred-shortcode-container-<?php echo $id; ?>' value='4' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Create Child Content link','wp-cred'); ?></span></label>
                        </td>
                    </tr>
                </table>
            </div>

            <div class='cred-shortcodes-container _cred-post-creation-container2' >
                <table class='cred-table' cellpadding=0 cellspacing=0>
                    <tr>
                        <td>
						<label for="cred_form-new-shortcode-select-<?php echo $id; ?>"><?php _e('Select form','wp-cred'); ?></label>
                        <select name="cred_form-new-shortcode-select-<?php echo $id; ?>" class="cred_form-new-shortcode-select2">
                            <optgroup label="<?php echo esc_attr(__('Select form','wp-cred')); ?>">
                                <option value='' disabled selected style='display:none;'><?php _e('Select form','wp-cred'); ?></option>

                                        <?php
                                            foreach ($forms as $form)
                                            {
                                                if (isset($form->meta->form['type']) && $form->meta->form['type']=='new')
                                                {
                                                    echo "<option value='{$form->ID}'>{$form->post_title}</option>";
                                                }
                                            }
                                        ?>
                            </optgroup>
                        </select>
						<a class='cred-help-link' href='<?php echo $help['content_creation_shortcode_settings']['link']; ?>' target='<?php echo $help_target; ?>' title="<?php echo esc_attr($help['content_creation_shortcode_settings']['text']); ?>" >
							<i class="icon-question-sign"></i>
							<span><?php echo $help['content_creation_shortcode_settings']['text']; ?></span>
						</a>
                        </td>
                    </tr>
                </table>
            </div>

            <div class='cred-shortcodes-container _cred-post-child-link-container2' >
                <fieldset class='cred-fieldset'>
                <legend><b><?php _e('Child Form Page','wp-cred'); ?></b></legend>
                    <table class='cred-table' cellpadding=0 cellspacing=0>
                      <tr>
                          <td colspan=2><div style='display:inline-block' class='cred_ajax_loader_small cred-form-suggest-child-form-loader2'></div><span class="cred-explain-text"><?php _e('Type some characters from title of the page and a suggestion popup will appear..','wp-cred'); ?></span></td>
                      </tr>
                      <tr>
                          <td ><?php _e('Page:','wp-cred'); ?> &nbsp;</td>
                          <td><input  type='text' class='cred-child-form-page2' name='cred-child-form-page-<?php echo $id; ?>' value=''  placeholder="<?php echo esc_attr(__('Type some characters..','wp-cred')); ?>" /></td>
                      </tr>
                      <tr>
                          <td><?php _e('link text:','wp-cred'); ?> &nbsp;</td>
                          <td><input   type='text' class='cred-child-link-text2' name='cred-child-link-text-<?php echo $id; ?>' value='' /></td>
                      </tr>
                    </table>
                </fieldset>
                <fieldset class='cred-fieldset'>
                <legend><b><?php _e('How do you want to set the parent in child form?','wp-cred'); ?></b></legend>
                    <table class='cred-table' cellpadding=0 cellspacing=0>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='radio' checked='checked' class='cred-advanced-options-radio cred-radio-10' name='cred-post-child-parent-action-<?php echo $id; ?>' value='current' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Set the parent according to the currently displayed content','wp-cred'); ?></span></label>
                            </td>
                        </tr>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='radio' class='cred-advanced-options-radio cred-radio-10' name='cred-post-child-parent-action-<?php echo $id; ?>' value='other' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Choose a specific parent','wp-cred'); ?></span></label>
                            <input type='text' class='cred_post_child_parent_id2' name='cred_post_child_parent_id-<?php echo $id; ?>' value='' size='15'  placeholder="<?php echo esc_attr(__('Type some characters..','wp-cred')); ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='radio' class='cred-advanced-options-radio cred-radio-10' name='cred-post-child-parent-action-<?php echo $id; ?>' value='form' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('The form includes the parent selector','wp-cred'); ?></span></label>
                            </td>
                        </tr>
                    </table>
                </fieldset>
                <a href='javascript:;' class='cred-show-hide-advanced'></a>
                <div class='cred-shortcodes-container-advanced cred-post-child-link-container-advanced2'>
                    <fieldset class='cred-fieldset cred-edit-html-fieldset2'>
                    <legend><b><?php _e('HTML attributes for child content link (advanced)','wp-cred'); ?></b></legend>
                            <table class='cred-table' cellpadding=0 cellspacing=0>
								<tr>
									<td ><?php _e('class:','wp-cred'); ?></td>
									<td><input  type='text' class='cred-child-html-class2' name='cred-child-html-class-<?php echo $id; ?>' value='' /></td>
								</tr>
								<tr>
									<td ><?php _e('style:','wp-cred'); ?></td>
									<td><input   type='text' class='cred-child-html-style2' name='cred-child-html-style-<?php echo $id; ?>' value='' /></td>
								</tr>
								<tr>
									<td ><?php _e('target:','wp-cred'); ?></td>
									<td>
									  <select class='cred-child-html-target2' name='cred-child-html-target-<?php echo $id; ?>'>
									  <option value="_self" selected='selected'><?php _e('Current Window','wp-cred'); ?></option>
									  <option value="_top"><?php _e('Parent Window','wp-cred'); ?></option>
									  <option value="_blank"><?php _e('New Window','wp-cred'); ?></option>
									  </select>
								  </td>
								</tr>
								<tr>
									<td><?php _e('more attributes:','wp-cred'); ?></td>
									<td><input  type='text' class='cred-child-html-attributes2' name='cred-child-html-attributes-<?php echo $id; ?>' value='' /></td>
								</tr>
                          </table>
                    </fieldset>
                </div>
            </div>

            <div class='cred-shortcodes-container _cred-post-delete-link-container2' >
                <fieldset class='cred-fieldset'>
                <legend><b><?php _e('HTML attributes for delete link','wp-cred'); ?></b></legend>
                <table class='cred-table' cellpadding=0 cellspacing=0>
                  <tr>
                      <td><?php _e('text:','wp-cred'); ?> &nbsp;</td>
                      <td><input type='text' class='cred-delete-html-text2' name='cred-delete-html-text-<?php echo $id; ?>' value='Delete %TITLE%' /></td>
                  </tr>
                </table>
                </fieldset>
				<p style="position: relative;">
					<a class='cred-help-link' href='<?php echo $help[ 'content_delete_shortcode_settings' ][ 'link' ]; ?>' target='<?php echo $help_target; ?>' title="<?php echo esc_attr($help[ 'content_delete_shortcode_settings' ][ 'text' ]); ?>">
						<i class="icon-question-sign"></i>
						<span><?php echo $help[ 'content_delete_shortcode_settings' ][ 'text' ]; ?></span>
					</a>
                </p>
                <a href='javascript:;' class='cred-show-hide-advanced'></a>
                <div class='cred-shortcodes-container-advanced cred-post-delete-link-container-advanced2'>
                    <fieldset class='cred-fieldset'>
                    <legend><b><?php _e('What to delete','wp-cred'); ?></b></legend>
                    <table class='cred-table' cellpadding=0 cellspacing=0>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='radio' checked='checked' class='cred-advanced-options-radio cred-radio-10' name='cred-delete-what-to-delete-<?php echo $id; ?>' value='delete-current-post' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Delete current post (in Loop)','wp-cred'); ?></span></label>
                            </td>
                        </tr>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='radio' class='cred-advanced-options-radio  cred-radio-10' name='cred-delete-what-to-delete-<?php echo $id; ?>' value='delete-other-post' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Delete another post (post ID)','wp-cred'); ?></span></label>
                            <input type='text' class='cred_post_delete_id2' name='cred_post_delete_id' value='' size='5' />
                            </td>
                        </tr>
                    </table>
                    </fieldset>
                    <fieldset class='cred-fieldset'>
                    <legend><b><?php _e('Delete action','wp-cred'); ?></b></legend>
                    <table class='cred-table' cellpadding=0 cellspacing=0>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='radio' checked='checked' class='cred-advanced-options-radio cred-radio-10' name='cred-delete-delete-action-<?php echo $id; ?>' value='trash' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Trash post','wp-cred'); ?></span></label>
                            </td>
                        </tr>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='radio' class='cred-advanced-options-radio cred-radio-10' name='cred-delete-delete-action-<?php echo $id; ?>' value='delete' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Delete post','wp-cred'); ?></span></label>
                            </td>
                        </tr>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='checkbox' class='cred-refresh-after-action cred-checkbox-10' name='cred-refresh-after-action-<?php echo $id; ?>' value='refresh' checked='checked'/><span class='cred-checkbox-replace'></span>
                            <span ><?php _e('Refresh page after deletion','wp-cred'); ?></span></label>
                            </td>
                        </tr>
                    </table>
                    </fieldset>
                    <fieldset class='cred-fieldset'>
                    <legend><b><?php _e('HTML attributes for delete link (advanced)','wp-cred'); ?></b></legend>
                    <table class='cred-table' cellpadding=0 cellspacing=0>
                      <tr>
                          <td ><?php _e('class:','wp-cred'); ?> &nbsp;</td>
                          <td><input  type='text' class='cred-delete-html-class2' name='cred-delete-html-class-<?php echo $id; ?>' value='' /></td>
                      </tr>
                      <tr>
                          <td ><?php _e('style:','wp-cred'); ?> &nbsp;</td>
                          <td><input   type='text' class='cred-delete-html-style2' name='cred-delete-html-style-<?php echo $id; ?>' value='' /></td>
                      </tr>
                        <tr>
                            <td><?php _e('message:','wp-cred'); ?></td>
                            <td><input  type='text' class='cred-delete-html-message2' name='cred-delete-html-message-<?php echo $id; ?>' value='' /></td>
                        </tr>
                    </table>
                    </fieldset>
                </div>
            </div>

            <div class='cred-shortcodes-container _cred-post-edit-container2' >
                <table class='cred-table' cellpadding=0 cellspacing=0>
                    <tr>
                        <td><?php _e('Select form','wp-cred'); ?>
                        <select style='position:relative;' class="cred_form-edit-shortcode-select2" name="cred_form-edit-shortcode-select-<?php echo $id; ?>">
                            <optgroup label="<?php echo esc_attr(__('Select form','wp-cred')); ?>">
                                <option value='' disabled selected style='display:none;'><?php _e('Select form','wp-cred'); ?></option>

                                        <?php
                                            foreach ($forms as $form)
                                            {
                                                if (isset($form->meta->form['type']) && $form->meta->form['type']=='edit')
                                                {
                                                    echo "<option class='_cred_cred_{$form->meta->post['post_type']}' value='{$form->ID}'>{$form->post_title}</option>";
                                                }
                                            }
                                        ?>
                            </optgroup>
                        </select>
							<a class='cred-help-link' href='<?php echo $help['content_edit_shortcode_settings']['link']; ?>' target='<?php echo $help_target; ?>' title="<?php echo esc_attr($help['content_edit_shortcode_settings']['text']); ?>">
								<i class="icon-question-sign"></i>
								<span><?php echo $help['content_edit_shortcode_settings']['text']; ?></span>
							</a>
                        </td>
                    </tr>
                </table>
                <a href='javascript:;' class='cred-show-hide-advanced'></a>
                <div class='cred-shortcodes-container-advanced cred-post-edit-container-advanced2'>
                    <fieldset class='cred-fieldset'>
                    <legend><b><?php _e('What to edit','wp-cred'); ?></b></legend>
                    <table class='cred-table' cellpadding=0 cellspacing=0>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='radio' checked='checked' class='cred-advanced-options-radio cred-radio-10' name='cred-edit-what-to-edit-<?php echo $id; ?>' value='edit-current-post' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Edit current post (in Loop)','wp-cred'); ?></span></label>
                            </td>
                        </tr>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='radio' class='cred-advanced-options-radio cred-radio-10' name='cred-edit-what-to-edit-<?php echo $id; ?>' value='edit-other-post' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Edit another post','wp-cred'); ?></span></label>
                            <div class='cred-edit-other-post-more2' style='display:inline-block'>
                            <div style='display:inline-block' class='cred_ajax_loader_small cred-form-addtional-loader2'></div>
                            <select class="cred-edit-post-select2" name="cred-edit-post-select-<?php echo $id; ?>">
                                <optgroup label="<?php echo esc_attr(__('Select post','wp-cred')); ?>">
                                    <option value='' disabled selected style='display:none;'><?php _e('Select post','wp-cred'); ?></option>
                                </optgroup>
                            </select>
                            </div>
                            </td>
                        </tr>
                    </table>
                    </fieldset>
                    <fieldset class='cred-fieldset'>
                    <legend><b><?php _e('How to display the form','wp-cred'); ?></b></legend>
                    <table class='cred-table' cellpadding=0 cellspacing=0>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='radio' checked='checked' class='cred-advanced-options-radio cred-radio-10' name='cred-edit-how-to-display-<?php echo $id; ?>' value='insert-link' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Insert a link to edit','wp-cred'); ?></span></label>
                            <div class='cred-edit-link-text-container2'>
                                (<?php _e('text:','wp-cred'); ?>
                                <input type='text' class='cred-edit-html-text2' name='cred-edit-html-text-<?php echo $id; ?>' value='Edit %TITLE%' />)
                            </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='radio' class='cred-advanced-options-radio cred-radio-10' name='cred-edit-how-to-display-<?php echo $id; ?>' value='insert-form' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Insert the form itself','wp-cred'); ?></span></label>
                            </td>
                        </tr>
                    </table>
                    </fieldset>
                    <fieldset class='cred-fieldset cred-edit-html-fieldset2'>
                    <legend><b><?php _e('HTML attributes for edit link (advanced)','wp-cred'); ?></b></legend>

						<table class='cred-table' cellpadding=0 cellspacing=0>
							<tr>
								<td ><?php _e('class:','wp-cred'); ?></td>
								<td><input  type='text' class='cred-edit-html-class2' name='cred-edit-html-class-<?php echo $id; ?>' value='' /></td>
							</tr>
							<tr>
								<td ><?php _e('style:','wp-cred'); ?></td>
								<td><input   type='text' class='cred-edit-html-style2' name='cred-edit-html-style-<?php echo $id; ?>' value='' /></td>
							</tr>
							<tr>
								<td ><?php _e('target:','wp-cred'); ?></td>
								<td>
								  <select class='cred-edit-html-target2' name='cred-edit-html-target-<?php echo $id; ?>'>
								  <option value="_self" selected='selected'><?php _e('Current Window','wp-cred'); ?></option>
								  <option value="_top"><?php _e('Parent Window','wp-cred'); ?></option>
								  <option value="_blank"><?php _e('New Window','wp-cred'); ?></option>
								  </select>
							  </td>
							</tr>
                          <tr>
                              <td><?php _e('more attributes:','wp-cred'); ?> &nbsp;</td>
                              <td><input  type='text' class='cred-edit-html-attributes2' name='cred-edit-html-attributes-<?php echo $id; ?>' value='' /></td>
                          </tr>
						</table>
                    </fieldset>
                </div>

            </div>

        </div>

		<p class="cred-buttons-holder">
			<a class="cred-popup-cancel2 button" href="javascript:;"><?php _e( 'Cancel', 'wp-cred' ); ?></a>
			<a data-content="<?php echo $content; ?>" disabled='disabled' class="cred-insert-shortcode2 button button-secondary" href="javascript:;"><?php _e( 'Insert shortcode', 'wp-cred' ); ?></a>
		</p>

    </div>

</span>