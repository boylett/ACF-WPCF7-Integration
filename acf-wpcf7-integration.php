<?php

	/**
	 * Plugin Name:  ACF WPCF7 Integration
	 * Plugin URI:   https://github.com/boylett/ACF-WPCF7-Integration
	 * Description:  Integrate ACF with WPCF7 to enable custom fields on a contact form
	 * Version:      0.0.2
	 * Author:       Ryan Boylett
	 * Author URI:   http://github.com/boylett/
	 */
	
	add_action('save_post', function($post_id)
	{
		if(function_exists('wpcf7_current_action') and wpcf7_current_action() == 'save')
		{
			$post = get_post($post_id);

			if($post->post_type == 'wpcf7_contact_form')
			{
				acf_save_post($post->ID);
			}
		}
	});
	
	add_filter('wpcf7_editor_panels', function($panels)
	{
		if(isset($_GET['post']))
		{
			$GLOBALS['wpcf7form-display'] = true;

			$groups = acf_get_field_groups($_GET['post']);

			if(!empty($groups))
			{
				$panels['custom-fields-panel'] = array
				(
					"title"    => "Custom Fields",
					"callback" => function() use($groups)
					{
						$group_ids = array();

						foreach($groups as $group)
						{
							$group_ids[] = $group['key'];
						}

						acf_form(array
						(
							"field_groups"       => $group_ids,
							"form"               => false,
							"html_submit_button" => '<input type="submit" style="display: none;" />',
							"id"                 => "wpcf7-form",
							"post_id"            => $_GET['post']
						));
					}
				);

				$settings = $panels['additional-settings-panel'];

				unset($panels['additional-settings-panel']);

				$panels['additional-settings-panel'] = $settings;
			}

			unset($GLOBALS['wpcf7form-display']);
		}

		return $panels;
	});

	add_filter('acf/location/rule_types', function($choices)
	{
		$choices['Forms']['wpcf7'] = 'Contact Form';

		return $choices;
	});

	add_filter('acf/location/rule_values/wpcf7', function($choices)
	{
		$forms = get_posts(array
		(
			"order"          => "ASC",
			"orderby"        => "post_title",
			"posts_per_page" => -1,
			"post_type"      => "wpcf7_contact_form"
		));

		$choices['all'] = 'Any';

		if(!empty($forms))
		{
			foreach($forms as $post)
			{
				$choices[$post->ID] = $post->post_title;
			}
		}

		return $choices;
	});

	add_filter('acf/location/rule_match/wpcf7', function($match, $rule, $options)
	{
		if(isset($GLOBALS['wpcf7form-display']) and $GLOBALS['wpcf7form-display'])
		{
			$post_id = (int) $rule['value'];

			if($rule['operator'] == "==")
			{
				return ($post_id == 'all' or $_GET['post'] == $post_id);
			}
			elseif($rule['operator'] == "!=")
			{
				return ($post_id != 'all' and $_GET['post'] != $post_id);
			}
		}

		return false;
	}, 10, 3);
