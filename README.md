## Integrate ACF with WPCF7 to enable custom fields on a contact form

Create a new Form Group, select 'Contact Form' as a display location and specify which form to display your fields on, or just select 'Any' to display on all forms.

A new tab will appear when editing Contact Forms called 'Custom Fields'.

Once saved, you can retrieve form data in the same way as with posts:
```php
$forms = get_posts(array("post_type" => "wpcf7_contact_form"));

foreach($forms as $form)
{
  $field = get_field('form_title', $form);
}
```
