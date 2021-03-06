<?php if (count($form_field_names) > 0) { ?>
<div class="report-custom-forms-text">
<table>
<?php
	foreach ($form_field_names as $field_id => $field_property)
	{
		if ($field_property['field_type'] == 8)
		{
			echo "</table></div>";

			if (isset($field_propeerty['field_default']))
			{
				echo "<div class=\"" . $field_property['field_name'] . "\">";
			}
			else
			{
				echo "<div class=\"custom_div\">";
			}

			echo "<h2>" . $field_property['field_name'] . "</h2>";
			echo "<table>";

			continue;
		}
		elseif ($field_property['field_type'] == 9)
		{
			echo "</table></div>";
			continue;
		}

		echo "<tr>";

		// Get the value for the form field
		$value = $field_property['field_response'];

		// Check if a value was fetched
		if ($value == "")
			continue;
// HT: Start of new autocomplete search select type display 
		if($field_property['field_type'] == 10) {
			
			$field_options = customforms::get_custom_field_options($field_id);
			if (isset($field_options['field_autocomplete_type']) && ($field_options['field_autocomplete_type'] == 'FILE')) {
				if (!empty($field_options['field_autocomplete_file'])) 
				{
					$field_file = $field_options['field_autocomplete_file'];
					$value = customforms::get_autosearch_text($value, $field_file, true);	
				} 
			} else {
				$value = customforms::get_autosearchDb_text($field_id, $value, true);
			}
			echo "<td><strong>" . html::specialchars($field_property['field_name']) . ": </strong></td>";
			echo "<td class=\"answer\">$value</td>";
			
// HT: End of new autocomplete search select type display
		} else if ($field_property['field_type'] == 1 OR $field_property['field_type'] > 3)
		{
			// Text Field
			// Is this a date field?
			echo "<td><strong>" . html::specialchars($field_property['field_name']) . ": </strong></td>";
			echo "<td class=\"answer\">$value</td>";
		}
		elseif ($field_property['field_type'] == 2)
		{
			// TextArea Field
			echo "<td><strong>" . html::specialchars($field_property['field_name']) . ": </strong></td>";
			echo "<td class=\"answer\">$value</tr>";
		}
		elseif ($field_property['field_type'] == 3)
		{
			echo "<td><strong>" . html::specialchars($field_property['field_name']) . ": </strong></td>";
			echo "<td class=\"answer\">" . date('M d Y', strtotime($value)) . "</td>";
		}
		//echo "</div>";
		echo "</tr>";
	}
?>
</table>
</div>
<?php } ?>