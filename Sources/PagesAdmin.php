<?php

// If we are outside SMF throw an error.
if (!defined('SMF'))
	die('Hacking attempt...');

// Admin panel menu for the mod.
function PagesAdminMenu()
{
	global $context, $txt;
	
	loadLanguage('Pages');

    $context['show_bbc'] = 1;
	
	$subActions = array(
	    'main' => 'AdminPages', 
	    'add_page' => 'AddPage',
		'submit_page' => 'SubmitPage',
		'edit_page' => 'EditPage',
		'update_page' => 'UpdatePage',
	);
	
	$context['page_title'] = $txt['pages_directory_admin'];

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => &$txt['pages_directory_admin'],
		'description' => $txt['pages_directory_admin_desc'],
		'tabs' => array(
			'main' => array(
				'description' => $txt['pages_directory_admin_desc'],
			),
			'add_page' => array(
				'description' => $txt['add_pages_desc'],
			),
		),
	);

	// Call the right function for this sub-acton.
	if (!isset($_GET['sa']) || !isset($subActions[$_GET['sa']]))
		$_GET['sa'] = 'main';
	
	$subActions[$_GET['sa']]();
}

// Display add page form.
function AddPage()
{
	global $context, $txt, $sourcedir;
	
	loadTemplate('Pages');
	
	$context['page_title'] = $txt['add_pages'];
	$context['sub_template'] = 'add_pages';
	
	require_once($sourcedir . '/Subs-Editor.php');

    $editorOptions = array(
		'id' => 'add_page_content',
		'value' => '',
		'width' => '100%',
	);
   
   create_control_richedit($editorOptions);
   $context['post_box_name'] = $editorOptions['id'];
}

// Insert page in the database.
function SubmitPage()
{
	global $smcFunc, $user_info, $sourcedir;
	
	if (!empty($_REQUEST['add_page_content_mode']) && isset($_REQUEST['add_page_content']))
	{
		require_once($sourcedir . '/Subs-Editor.php');

		$_REQUEST['add_page_content'] = html_to_bbc($_REQUEST['add_page_content']);

		$_REQUEST['add_page_content'] = un_htmlspecialchars($_REQUEST['add_page_content']);
	}
	
	$_POST['add_page_title'] = $smcFunc['htmlspecialchars']($_POST['add_page_title']);
	$_POST['add_page_content'] = $smcFunc['htmlspecialchars']($_POST['add_page_content'], ENT_QUOTES);
	
	censorText($_POST['add_page_title']);
	censorText($_POST['add_page_content']);
	
	$title = !empty($_POST['add_page_title']) ? $_POST['add_page_title'] : '';
	$content = !empty($_POST['add_page_content']) ? $_POST['add_page_content'] : '';
	
	if (empty($title))
		fatal_lang_error('you must_enter_page_title', false);
	
	if (empty($content))
		fatal_lang_error('you must_enter_page_content', false);
	
	$smcFunc['db_insert']('',
		'{db_prefix}pages',
		array(
				'title' => 'string', 'content' => 'string', 'user_id' => 'int', 'time' => 'int',
		    ),
		array(
				$title, $content, $user_info['id'], time(),
			),
		array('id')
	);
		
	redirectexit('action=admin;area=pages;sa=main');
}

// Display edit page form.
function EditPage()
{
	global $smcFunc, $context, $txt, $scripturl, $sourcedir;
	
	$id = (int) (isset($_REQUEST['id']) ? $_REQUEST['id'] : 0);
	
	if(empty($id))
		fatal_lang_error('page_is_deleted', false);
	
	isAllowedTo('admin_forum');

	loadTemplate('Pages');
	
	$context['sub_template'] = 'edit_pages';
	
	$results = $smcFunc['db_query']('', '
	    SELECT id, title, content
	    FROM {db_prefix}pages
	    WHERE id = {int:id} 
		LIMIT 1',
		array(
			'id' => $id,
		)
	);

    $row = $smcFunc['db_fetch_assoc']($results);
	$smcFunc['db_free_result']($results);
	
	$context['page_edit'] = $row;
	
	$context['page_title'] = $txt['editing_page'] . $row['title'];
	
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=admin;area=pages;sa=edit_page;id=' . $id,
		'name' => $txt['editing_page'] . $row['title'],
	);
	
	require_once($sourcedir . '/Subs-Editor.php');
	
    $editorOptions = array(
      'id' => 'add_page_content',
      'value' => $context['page_edit']['content'],
      'width' => '100%',
    );
   
   create_control_richedit($editorOptions);
   
   $context['post_box_name'] = $editorOptions['id'];
}

// Insert updated page in the database.
function UpdatePage()
{
	global $smcFunc;
	
	$id = (int) (isset($_REQUEST['id']) ? $_REQUEST['id'] : 0);
	
	if(empty($id))
		fatal_lang_error('page_is_deleted', false);
	
	if (!empty($_REQUEST['add_page_content_mode']) && isset($_REQUEST['add_page_content']))
	{
		require_once($sourcedir . '/Subs-Editor.php');

		$_REQUEST['add_page_content'] = html_to_bbc($_REQUEST['add_page_content']);

		$_REQUEST['add_page_content'] = un_htmlspecialchars($_REQUEST['add_page_content']);
	}
	
	$title = $smcFunc['htmlspecialchars']($_POST['add_page_title']);
	$content = $smcFunc['htmlspecialchars']($_POST['add_page_content'], ENT_QUOTES);
	
	censorText($_POST['add_page_title']);
	censorText($_POST['add_page_content']);
	
	if (empty($title))
		fatal_lang_error('you must_enter_page_title', false);
	
	if (empty($content))
		fatal_lang_error('you must_enter_page_content', false);
	
	$smcFunc['db_query']('',"
			UPDATE {db_prefix}pages
			SET
				title = {string:title},			
				content = {string:content}
			WHERE id = {int:id}",
			array(
			    'id' => $id,
				'title' => $title,	
				'content' => $content,
			)
	);
	
	redirectexit('action=admin;area=pages;sa=main');
}

// Display all pages in the admin panel.
function AdminPages()
{
	global $txt, $sourcedir, $scripturl, $context, $smcFunc;
	
	// User pressed the 'remove selection button'.
	if (!empty($_POST['delete']) && !empty($_POST['remove']) && is_array($_POST['remove']))
	{
		checkSession();

		foreach ($_POST['remove'] as $index => $log_time)
			$_POST['remove'][(int) $index] = (int) $log_time;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}pages
			WHERE id IN ({array_int:pages_list})',
			array(
				'pages_list' => $_POST['remove'],
			)
		);

		redirectexit('action=admin;area=pages;sa=main');
	}
	
	$listOptions = array(
		'id' => 'pages_list',
		'items_per_page' => 20,
		'base_href' => $scripturl . '?action=admin;area=pages;sa=main',
		'default_sort_col' => 'title',
		'no_items_label' => $txt['no_pages_added_yet'],
		'get_items' => array(
			'function' => 'list_getAdminPages',
		),
		'get_count' => array(
			'function' => 'list_countAdminPages',
		),
		'columns' => array(
			'title' => array(
				'header' => array(
					'value' => $txt['title'],
				),
				'data' => array(
					'db' => 'title',
					'style' => 'width: 40%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'title',
					'reverse' => 'title DESC',
				),
			),
			'user_id' => array(
				'header' => array(
					'value' => $txt['username'],
				),
				'data' => array(
					'db' => 'user_id',
					'style' => 'width: 20%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'user_id',
					'reverse' => 'user_id DESC',
				),
			),
			'views' => array(
				'header' => array(
					'value' => $txt['views'],
				),
				'data' => array(
					'db' => 'views',
					'style' => 'width: 5%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'views',
					'reverse' => 'views DESC',
				),
			),
			'time' => array(
				'header' => array(
					'value' => $txt['page_time'],
				),
				'data' => array(
					'db' => 'time',
					'style' => 'width: 35%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'time',
					'reverse' => 'time DESC',
				),
			),
			'modify' => array(
				'header' => array(
					'value' => $txt['modify'],
					'style' => 'width: 5%; text-align: center;',
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . $scripturl . '?action=admin;area=pages;sa=edit_page;id=%1$d">' . $txt['edit'] . '</a>',
						'params' => array(
							'id' => false,
						),
					),
					'class' => 'centercol',
				),
			),
			'check' => array(
				'header' => array(
					'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" class="input_check" />',
			),
			'data' => array(
					'sprintf' => array(
						'format' => '<input type="checkbox" name="remove[]" value="%1$d" class="input_check" />',
						'params' => array(
							'id' => false,
						),
					),
					'style' => 'text-align: center',
				),
			),
		),	
		'form' => array(
			'href' => $scripturl . '?action=admin;area=pages;sa=main',
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '<input type="submit" name="delete" value="' . $txt['delete_selected_pages'] . '" data-confirm="' . $txt['confirm_delete_selected_pages_desc'] . '" class="button you_sure">',
				'style' => 'text-align: right;',
			),
		),
	);

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	$context['sub_template'] = 'show_list';
	$context['default_list'] = 'pages_list';
}

// Get all added pages.
function list_getAdminPages($start, $items_per_page, $sort)
{
	global $smcFunc, $scripturl, $txt;
	
	$result = $smcFunc['db_query']('', '
	       SELECT p.id, p.user_id, p.title, p.content, p.views, p.time,
		   mem.id_member, mem.member_name
		   FROM {db_prefix}pages AS p
		   LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = p.user_id)
		   ORDER BY {raw:sort}
		   LIMIT {int:start}, {int:per_page}',
		   array(
				 'sort' => $sort,
		         'start' => $start,
			     'per_page' => $items_per_page,
			    )
	);
	
    $pages = array();
	
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$pages[] = array(
		    'id' => $row['id'],
			'title' => '<a href="' . $scripturl . '?action=pages;sa=view;id='.$row['id']. '">' . $row['title'] . '</a>',
			'user_id' => !empty($row['user_id']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['user_id'] . '">' . $row['member_name'] . '</a>' : $txt['guest'],
			'views' => $row['views'],
			'time' => timeformat($row['time']),
		);
	}
	
	return $pages;

	$smcFunc['db_free_result']($result);
}

// Count all added pages for the pagination.
function list_countAdminPages()
{
	global $smcFunc;
	
	$result = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}pages',
		array( )
	);
				
	list ($pages) = $smcFunc['db_fetch_row']($result);
	
	$smcFunc['db_free_result']($result);

	return $pages;
}