## Devel Generate

This module creates the "DevelGenerate" plugin type.

All you need to do to provide a new instance for "DevelGenerate" plugin type
is to create your class extending "DevelGenerateBase" and following these steps:

1. Declare your plugin with annotations:
````
/**
 * Provides a ExampleDevelGenerate plugin.
 *
 * @DevelGenerate(
 *   id = "example",
 *   label = @Translation("example"),
 *   description = @Translation("Generate a given number of example elements."),
 *   url = "example",
 *   permission = "administer example",
 *   settings = {
 *     "num" = 50,
 *     "kill" = FALSE,
 *     "another_property" = "default_value"
 *   }
 * )
 */
````
2. Implement the `settingsForm` method to create a form using the properties
from the annotations.

3. Implement the `handleDrushParams` method. It should return an array of
values.

4. Implement the `generateElements` method. You can write here your business
logic using the array of values.

### Notes:

- You can alter existing properties for every plugin by implementing
`hook_devel_generate_info_alter`.

- DevelGenerateBaseInterface details base wrapping methods that most
DevelGenerate implementations will want to directly inherit from
`Drupal\devel_generate\DevelGenerateBase`.

- To give support for a new field type the field type base class should properly
implement `\Drupal\Core\Field\FieldItemInterface::generateSampleValue()`.
Devel Generate automatically uses the values returned by this method during the
generate process for generating placeholder field values. For more information
see: https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Field!FieldItemInterface.php/function/FieldItemInterface::generateSampleValue
