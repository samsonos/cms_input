#SamsonCMS input module

This is base module for all cms input modules.

## Usage

There are two main static functions, this module have:

 * create()
 * createFromMetadata()

Function create($dbObject, $type, $param = null, $className = __CLASS__)
This function create and retrieves SamsonCMS input instance based on given parameters.
As you can see first two are required, these are \samson\activerecord\dbRecord instance or it's ancestor
and SamsonCMS input field type, this parameter should be int.
Here is this list:

| Field name                | Field identifier |
|---------------------------|:----------------:|
| Text                      | 0                |
| Resource                  | 1                |
| Date                      | 2                |
| Select                    | 3                |
| Table (deprecated)        | 4                |
| Material                  | 5                |
| Number                    | 6                |
| WYSIWYG                   | 7                |
| Gallery (separate module) | 8                |

The other parameters are not necessary. For example if you will not pass the $param to this function
the ancestor class field will be used, and if it's not defined this class field will be used.
The last parameter is for double or more nested classes. This functionality is not implemented yet.

Second function createFromMetadata($entity, $param, $identifier, $className = __CLASS__) is very similar to create() function
except for first and third parameters, they are used to get \samson\activerecord\dbRecord instance.

Here are some examples how this functions can be used:

```php
$input = Field::create($material, 7, 'remains');
...
$input = Field::createFromMetadata($_GET['e'], $_GET['f'], $_GET['i']);
```

## Other methods

There are also value(), save($value) and convert($value) functions. The first one returns SamsonCMS module value field.
Function convert() does nothing but can be overridden to convert value before save.
Function save() inserts value in \samson\activerecord\dbRecord object field and writes this object to database.
> Be care not to be confused with __save() controller.

As this class is module it has __save() controller which can be called, for example, by URL '...samson_cms_input/save'.
As you know each SamsonCMS module has it's id and 'samson_cms_input' can be replaced by it to perform ancestor controller.

This class implements \samson\core\iModuleViewable interface and should implement toView() function.
Default there is default implementation in this module but it can be overridden in ancestor classes.


[SamsonCMS official web-site](samsoncms.com)
