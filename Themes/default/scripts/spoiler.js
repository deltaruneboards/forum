(function (document, sceditor) {
	'use strict';

	sceditor.command.set(
		'spoiler', {
			exec: function (caller) {
				let editor = this;

				let content = $(this._('<form><div><label for="title">{0}</label> <input id="title" name="title"></div></form>', smf_txt_spoiler_title)).submit(function () {
					return false;
				});

				content.append(
					$(this._('<div><input class="button" type="button" value="{0}"></div>', this._('Insert'))).click(function (e) {
						let	title = $(this).parent('form').find('#title').val();

						editor.wysiwygEditorInsertHtml(
							'<details class="bbc_spoiler" data-title="' + title + '"><summary class="sceditor-ignore">' + title + '</summary><div class="spoiler_content">',
							'</div></details>'
						);
						editor.closeDropDown(true);

						e.preventDefault();
					})
				);

				editor.createDropDown(caller, 'add-spoiler', content[0]);
			},
			txtExec: function () {
				let title = prompt(smf_txt_spoiler_title, '');

				title = title ? '="' + title + '"' : '';

				this.insertText('[spoiler' + title + ']', '[/spoiler]');
			}
		}
	);

	sceditor.formats.bbcode.set(
		'spoiler', {
			tags: {
				details: {
					class: 'bbc_spoiler'
				},
				summary: null,
				div: {
					class: 'spoiler_content'
				}
			},
			breakBefore: false,
			isInline: false,
			format: function (element, content) {
				let title = element.getAttribute('data-title') ?? '';

				if (element.tagName === 'SUMMARY')
					return '';

				if (title)
					title = '="' + sceditor.escapeEntities(title) + '"';

				return '[spoiler' + title + ']' + content + '[/spoiler]';
			},
			html: function (e, attrs, content) {
				let attr_title = '',
					title = smf_txt_spoiler;

				if (attrs.defaultattr) {
					title = sceditor.escapeEntities(attrs.defaultattr);
					attr_title = ' data-title="' + title + '"';
				}

				return '<details class="bbc_spoiler"' + attr_title + '><summary>' + title + '</summary>' + content + '</details>';
			}
		}
	);

	sceditor.plugins.spoiler = function () {
		let editor;

		this.init = function () {
			editor = this;
		}

		this.signalReady = function () {
			editor.css(spoilerCss);
		}
	}
})(document, sceditor);
