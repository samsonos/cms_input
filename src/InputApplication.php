<?php
/**
 * Created by Maxim Omelchenko <omelchenko@samsonos.com>
 * on 31.03.2015 at 15:24
 */
namespace samsoncms\input;

use samson\activerecord\dbQuery;

class InputApplication extends \samson\core\CompressableExternalModule implements \samson\core\iModuleViewable
{
    /** {@inheritdoc} */
    protected $id = 'samson_cms_input';

    /** @var Field|null SamsonCMS field object */
    protected $field = null;

    /** Main field action controller */
    protected $action = 'save';

    /** Path to view file for field rendering */
    protected $defaultView = "index";

    /** Path to view file for inner field rendering */
    protected $fieldView = "field";

    /**
     * Create field class instance
     *
     * @param string|\samson\activerecord\dbRecord $entity Class name or object
     * @param string|null $param $entity class field
     * @param int $identifier Identifier to find and create $entity instance
     * @param \samson\activerecord\dbQuery|null $dbQuery Database object
     * @return self Chaining
     */
    public function createField($entity, $param = null, $identifier = null, $dbQuery = null)
    {
        $this->field = new Field($entity, $param, $identifier, $dbQuery);
        return $this;
    }

    /**
     * Save handler for CMS Field input
     */
    public function __save()
    {
        // Does it necessary?
        s()->async(true);

        // If we have post data
        if (isset($_POST)) {
            // Make pointers to posted parameters
            $entity = & $_POST['__entity'];
            $param 	= & $_POST['__param'];
            $identifier = & $_POST['__obj_id'];
            $value = & $_POST['__value'];

            // Check if all necessary data is passed
            if (!isset($value)) {
                e('CMSField - no "value" is passed for saving', E_SAMSON_CORE_ERROR);
            }
            if (!isset($entity)) {
                e('CMSField - no "entity" is passed for saving', E_SAMSON_CORE_ERROR);
            }
            if (!isset($identifier)) {
                e('CMSField - no "object identifier" is passed for saving', E_SAMSON_CORE_ERROR);
            }
            if (!isset($param)) {
                e('CMSField - no "object field name" is passed for saving', E_SAMSON_CORE_ERROR);
            }

            // Create new Field instance
            $this->createField($entity, $param, $identifier);

//            // Try to find passed object for saving
//            if (dbQuery($entity)->id($objectId)->first($dbObject)) {
//                // If object supports numeric value
//                if ($param != 'numeric_value' && isset($dbObject['numeric_value'])) {
//                    // Convert value to numeric value
//                    $dbObject['numeric_value'] = $this->convert($value);
//                }
//
//                // Set field value and than save it
//                $this->obj = $dbObject;
//                $this->param = $param;
//                $this->save($value);
//
//            } else {
//                // Generate error
//                e(
//                    'CMSField - Entity ## with id: ## - does not exists',
//                    E_SAMSON_CORE_ERROR,
//                    array($entity, $objectId)
//                );
//            }

            // Save specified value to SamsonCMS input
            $this->field->save($value);
        }
    }

    /** @see \samson\core\iModuleViewable::toView() */
    public function toView($prefix = null, array $restricted = array())
    {
        // Generate unique prefix if not passed
        $innerPrefix = 'field_';

        // Get all field vars
        $result = $this->field->getObjectData($innerPrefix);
        // Set object separate from all other data to use in view
        $object = $result[$innerPrefix . 'dbObject'];

        // 		// Get only this class properties
        // 		$own = array_keys(get_object_vars($this));
        // 		$parent = array_keys(get_object_vars(ns_classname('ExternalModule','samson\core')));
        // 		$properties = array_diff($own, $parent);

        // Iterate object vars and gather them in collection
//        foreach (get_object_vars($this->field) as $key => $value) {
//            $result[$innerPrefix.$key] = $value;
//        }

        // Generate field action controller
        $result[$innerPrefix . 'action'] = url_build($this->id, $this->action);

        // Generate unique textarea id
        $result[$innerPrefix . 'textarea_id'] = 'field_' . $object->id;

        // Render inner field view
        $result[$innerPrefix . 'view'] = $this->view($this->fieldView)->set($result)->output();

        // Return input fields collection prepared for module view
        return array(
            $prefix . 'html' => $this
                ->view($this->defaultView)
                ->set($result)
                ->set($object, 'object')
                ->output()
        );
    }
}
