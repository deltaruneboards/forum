/**
 * @package Enhanced Quick Reply
 * @version 1.0.2
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2022, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

// Collapsed
function quickreply_collapsed_behavior()
{
	const quickreplyOptions = document.getElementById('quickreply_options');
	const catBar = document.querySelector('#quickreply .cat_bar');
	catBar.classList.add('quickreply_collapsed');
	// Hide the quick reply box
	quickreplyOptions.style.display = 'none';
	// Set the height to auto
	quickreplyOptions.style.height = 'auto';
	// Restore the scale property
	quickreplyOptions.style.scale = 1;
	// Toggle when clicking the title
	catBar.addEventListener('click', function() {
		// For now, use jQuery
		$('#quickreply_options').slideToggle();
		catBar.classList.toggle('quickreply_collapsed');
	});
	// Make it visible when quoting selected text or quoteAll
	document.querySelectorAll('li.quoteSelected > a, li.quoteAll > a').forEach(function(element) {
		element.addEventListener('click', function() {
			quickreplyOptions.style.display = 'block';
		});
	});
}

// Minimalistic
function quickreply_minimalistic_behavior()
{
	// Get the alerts:
	let topic_alerts = document.getElementById('quickreply').getElementsByTagName('p');

	if (topic_alerts)
	{
		Array.from(topic_alerts).forEach(warning => {
			// Remove alert class
			warning.classList.remove("alert");
			// Add errorbox class
			warning.classList.add("errorbox");
		})
	}

	// Get the quickreply options
	let quickreply_options = document.getElementById('quickreply_options');
	// Get the textarea
	let quickreply_textarea = quickreply_options.querySelector('textarea');
	// Add a placeholder
	quickreply_textarea.placeholder = quickreply_placeholder;

	// Textarea focus
	quickreply_textarea.addEventListener('focus', function()
	{
		// Remove the max-height limitation from the textarea
		this.style.maxHeight = 'none';

		// Min Height for the toolbar
		let toolbar_container = quickreply_options.querySelector('.sceditor-container');
		if (toolbar_container)
			toolbar_container.style.minHeight = '140px';
		// Show the toolbar
		let toolbar = quickreply_options.querySelector('.sceditor-toolbar');
		if (toolbar)
			toolbar.style.display = 'block';
		// Show the emoticons
		let emoticons = quickreply_options.querySelector('.sceditor-insertemoticon');
		if (emoticons)
			emoticons.style.display = 'block';
		// Show the buttons
		let post_confirm_buttons = quickreply_options.querySelector('#post_confirm_buttons');
		if (post_confirm_buttons)
			post_confirm_buttons.style.display = 'inline-flex';
		// Show the header
		let post_header = quickreply_options.querySelector('#post_header');
		if (post_header)
			post_header.style.display = 'block';
	});
}
