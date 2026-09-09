<?php

declare(strict_types=1);

function template_callback_topic_descriptions_boards(): void
{
	global $context;

	echo '
									</dl>';

	foreach ($context['categories'] as $category)
	{
		echo '
							<fieldset>
								<legend>
									', $category['name'], '
								</legend>
						<ul class="reset">';

		for ($i = 0, $n = count($category['boards']); $i < $n; $i++)
		{
			echo '
							<li><label><input type="checkbox" name="topic_descriptions_boards[]" value="', $category['boards'][$i]['id'], '"', $category['boards'][$i]['selected'] ? ' checked' : '', '>', $category['boards'][$i]['name'], '</label>';

			// Nest child boards inside another list.
			$curr_child_level = $category['boards'][$i]['child_level'];
			$next_child_level = $category['boards'][$i + 1]['child_level'] ?? 0;

			if ($next_child_level > $curr_child_level)
			{
				echo '
								<ul>';
			}
			else
			{
				// Close child board lists until we reach a common level
				// with the next board.
				while ($next_child_level < $curr_child_level--)
				{
					echo '
									</li>
								</ul>';
				}

				echo '</li>';
			}
		}

		echo '
						</ul>
							</fieldset>';
	}

	echo '
			<script>
				for (const div of document.forms.admin_form_wrapper)
					if (div.nodeName == "FIELDSET")
					{
						let allChecked = true;
						for (let o of div.elements)
							if (o.nodeName == "INPUT" && o.type == "checkbox")
								allChecked &= o.checked;

						var
							a = document.createElement("legend"),
							b = document.createElement("input"),
							c = document.createElement("label");
						b.type = "checkbox";
						b.checked = allChecked;
						c.appendChild(b);
						c.appendChild(document.createTextNode(div.firstElementChild.textContent));
						a.appendChild(c);
						div.firstElementChild.replaceWith(a);
						b.addEventListener("click", function(els)
						{
							for (const o of els)
								if (o.nodeName == "INPUT" && o.type == "checkbox")
									o.checked = this.checked;
						}.bind(b, div.elements));
					}
			</script>
									<dl>';
}
