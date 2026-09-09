<?php

// Add page form.
function template_add_pages()
{
	global $context, $scripturl, $txt;
	
	echo '
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg centertext">
				'.$txt['add_pages'].'
			</h3>
		</div>
		<div class="roundframe centertext">	
		<form method="post" action="', $scripturl, '?action=admin;area=pages;sa=submit_page" accept-charset="', $context['character_set'], '">
				<div class="content">
						<dl class="centertext">				
						<dt>
							<span><label for="add_page_title">', $txt['add_page_title'], '</label></span>
						</dt>
						<dd>
							<input type="text" id="add_page_title" name="add_page_title" size="75" required />
						</dd>
						<dt>
							<span><label for="add_page_content">', $txt['add_page_content'], '</label></span>
						</dt>';
	                    echo '<dd>';
						if (!function_exists('getLanguages'))
	                    {
		                    if ($context['show_bbc'])
		                    {
			                  echo '
					               ', template_control_richedit($context['post_box_name'], 'bbc'), ' ';
		                    }

		                    if (!empty($context['smileys']['postform']))
			                echo '
					               ', template_control_richedit($context['post_box_name'], 'smileys'), ' ';

		                    echo '
				                   ', template_control_richedit($context['post_box_name'], 'message'), ' ';
	                    }
	                    else
	                    {
		                    echo '<dd>';

		                    if ($context['show_bbc'])
		                    {
			                    echo '<div id="bbcBox_message"></div>';
		                    }

		                    if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
			                echo '<div id="smileyBox_message"></div>';

		                    echo '
					              ', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');

		                    echo '</dd>';
	                    }					  			
					echo '	  
					</dl>
					<hr class="hrcolor clear" />
					<div class="centertext">
						<input style="display:none" name="save" value="ok" />
						<input style="display:none" name="sc" value="', $context['session_id'], '" />
						<input type="submit" value="', $txt['save'], '" class="button_submit" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</form>
	</div>';
}

//Edit pages acp.
function template_edit_pages()
{
	global $context, $scripturl, $txt;
	
	echo '
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg centertext">
				'.$txt['editing_page'].' '.$context['page_edit']['title'].'
			</h3>
		</div>
		<div class="roundframe centertext">	
		<form method="post" action="', $scripturl, '?action=admin;area=pages;sa=update_page" accept-charset="', $context['character_set'], '">
				<div class="content">
					<dl class="centertext">				
						<dt>
							<span><label for="add_page_title">', $txt['add_page_title'], '</label></span>
						</dt>
						<dd>
							<input type="text" id="add_page_title" value="',$context['page_edit']['title'] ,'" name="add_page_title" size="75" required />
						</dd>
						<dt>
							<span><label for="add_page_content">', $txt['add_page_content'], '</label></span>
						</dt>';
	                    echo '<dd>';
						if (!function_exists('getLanguages'))
	                    {
		                    if ($context['show_bbc'])
		                    {
			                  echo '
					               ', template_control_richedit($context['post_box_name'], 'bbc'), ' ';
		                    }

		                    if (!empty($context['smileys']['postform']))
			                echo '
					               ', template_control_richedit($context['post_box_name'], 'smileys'), ' ';

		                    echo '
				                   ', template_control_richedit($context['post_box_name'], 'message'), ' ';
	                    }
	                    else
	                    {
		                    echo '<dd>';
		                    if ($context['show_bbc'])
		                    {
			                    echo '<div id="bbcBox_message"></div>';
		                    }

		                    if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
			                echo '<div id="smileyBox_message"></div>';
		                    echo '
					              ', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');

		                    echo '</dd>';
	                    }					  			
					echo '	  					
					</dl>
					<hr class="hrcolor clear" />
					<div class="centertext">
						<input style="display:none" name="id" value="'.  $context['page_edit']['id'] .'" />
						<input style="display:none" name="save" value="ok" />
						<input style="display:none" name="sc" value="', $context['session_id'], '" />
						<input type="submit" value="', $txt['save'], '" class="button_submit" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</form>
	</div>';
}

// View page.
function template_view_page()
{
    global $context, $txt;

		foreach ($context['pagedata'] as $page)
		{
			echo ' 
			<div class="cat_bar">
			   <h3 class="catbg">
			       ', $page['title'], '
			   </h3>
		    </div>';
			echo '
			<div class="roundframe information">
			    <span class="smalltext"><strong>', $txt['page_added_by'], ':</strong> ', $page['user_id'] , '; ', $page['time'] , '; <strong>', $txt['views'], ':</strong> ', $page['views'] , '</span>
				<hr class="hrcolor clear" />
				', $page['content'], '
			</div>';
		}
}