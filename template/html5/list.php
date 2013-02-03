<?php
/*
 * Copyright (c) 2011, Josef Kufner  <jk@frozen-doe.net>
 * All rights reserved.
 *
 */

function TPL_html5__simplecrud__list($t, $id, $d, $so)
{
	extract($d);

	// TODO: Some helper for building HTML code is required.

	$list_element = @ $list_holder['element'];
	$list_class   = @ $list_holder['class'];
	if ($list_element === null) {
		$list_element = 'div';
	}

	// Holder
	if ($list_element) {
		echo "<$list_element";
		if (!empty($list_class)) {
			echo " class=\"", htmlspecialchars($list_class), "\"";
		}
		echo ">\n";
	}

	// Item holder options
	$item_element = @ $item_holder['element'];
	$item_class   = @ $item_holder['class'];
	if ($item_element === null) {
		$item_element = 'div';
	}

	// Check and fill missing field options
	$prepared_fields = array();
	foreach ($fields as $field) {
		if (!empty($field['hidden'])) {
			// Skip removed fields
			continue;
		}

		$key = @ $field['key'];
		$value = @ $field['value'];

		$element = @ $field['element'];
		if ($element === null) {
			$element = 'div';
		}

		$class =  @ $field['class'];

		$format_function = @ $field['format_function'];
		if ($format_function === null) {
			$format_function = 'htmlspecialchars';
		}

		$link = @ $field['link'];
		$html_before = @ $field['html_before'];
		$html_after = @ $field['html_after'];

		$prepared_fields[] = array($key, $value, $element, $class, $format_function, $link, $html_before, $html_after);
	}

	// Show items
	foreach ($items as $item) {

		// Item holder
		if ($item_element !== false) {
			echo "<$item_element";
			if (!empty($item_class)) {
				echo " class=\"", htmlspecialchars($item_class), "\"";
			}
			echo ">\n";
		}

		// Show item fields
		foreach ($prepared_fields as $field) {
			list($key, $value, $element, $class, $format_function, $link, $html_before, $html_after) = $field;

			$link_filled = $link === null ? null : filename_format($link, $item);

			// Field holder
			if ($element !== false) {
				echo "<$element";
				if ($class !== null) {
					echo " class=\"", htmlspecialchars($class), "\"";
				}
				if ($element == 'a' && $link_filled) {
					echo " href=\"", htmlspecialchars($link_filled), "\"";
				}
				echo ">";
			}

			echo $html_before;

			if ($element != 'a' && $link_filled) {
				echo "<a href=\"", htmlspecialchars($link_filled), "\">";
			}

			if ($key !== null) {
				$value = $item[$key];
			}
			if ($format_function !== false) {
				$value = $format_function($value);
			}

			echo $value;

			if ($element != 'a' && $link) {
				echo "</a>";
			}

			echo $html_after;

			// End of field
			if ($element !== false) {
				echo "</$element>\n";
			}
		}

		// End of item
		if ($item_element !== false) {
			echo "</$item_element>\n";
		}
	}

	// End of holder
	if ($list_element) {
		echo "</$list_element>\n";
	}
}

