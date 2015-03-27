<?php
namespace samsoncms\input;

/**
 * Generic SamsonCMS input field
 * @author Vitaly Iegorov<egorov@samsonos.com>
 *
 */
class Field extends \samson\core\CompressableExternalModule implements \samson\core\iModuleViewable
{
    /** @var  int Field type identifier */
    protected static $type;

    protected $id = 'samson_cms_input_field';

    /** Database object class name which connected  with this field */
    protected $entity;

    /** Database object field name */
    protected $param = 'Value';

    /** Database object current field value */
    protected $value;

    /** Main field action controller */
    protected $action = 'save';

    /** Special CSS classname for nested field objects to bind JS and CSS */
    protected $cssClass = '__textarea';

    /** @var \samson\activerecord\dbRecord Pointer to database object instance */
    protected $obj;

    /** Path to view file for field rendering */
    protected $defaultView = "index";

    /** Path to view file for inner field rendering */
    protected $fieldView = "field";

    /**
     * Create field class ancestor
     *
     * @param \samson\activerecord\dbRecord $dbObject Any database object
     * @param int $type Field type identifier
     * @param string $param dbObject field
     * @param string $className Class name for double and more ancestors
     * @return self Field class ancestor instance
     */
    public static function create($dbObject, $type, $param = null, $className = __CLASS__)
    {
        /** @var \samsoncms\input\Field $cmsField This class or it's ancestor entity */
        $cmsField = null;
        /** @var \samson\core\ExternalModule $module Variable to store Field or it's ancestor module */
        $module = null;

        // TODO: Double nested classes not supported, we need to build class tree

        // Iterate all child classes
        foreach (get_child_classes($className) as $child) {
            // If we have child class with specified type
            if ($child::$type == $type) {
                /** @var string $moduleId Module identifier */
                $moduleId = get_class_vars($child)['id'];

                // If object has not passed
                if (!is_object($dbObject)) {
                    // Generate error
                    e(
                        'Cannot create ## object instance - no database record object is passed',
                        E_SAMSON_CORE_ERROR,
                        __CLASS__
                    );
                }

                // Try to get field module instance from core
                if (null !== ($module = & m($moduleId))) {
                    // Create input field instance
                    $cmsField = & $module->copy();
                    $cmsField->view_path = $module->view_path;
                    $cmsField->entity = get_class($dbObject);
                    $cmsField->obj = & $dbObject;
                    $cmsField->param = isset($param) ? $param : $cmsField->param;
                    $cmsField->value = $dbObject[$cmsField->param];
                } else { // Module doesn't exists
                    // Generate error
                    e(
                        'Cannot create ## object instance - Field module ## not loaded to system core',
                        E_SAMSON_CORE_ERROR,
                        array(__CLASS__, $child)
                    );
                }
            }
        }
        // Return this class or ancestor instance
        return $cmsField;
    }

    /**
     * Create field instance from input data
     *
     * @param string $entity class name it should be \samson\activerecord\dbRecord ancestor
     * @param string $param $entity class field
     * @param int $identifier Identifier to find and create $entity instance
     * @param string $className Class name for double and more ancestors
     * @return null|Field If given entity instance exists return Filed object otherwise return null
     */
    public static function createFromMetadata($entity, $param, $identifier, $className = __CLASS__)
    {
        /** @var \samson\activerecord\dbRecord $dbObject Given entity instance */
        $dbObject = null;
        /** @var Field $createdField Created field instance */
        $createdField = null;

        // If we can find given entity instance
        if (dbQuery($entity)->id($identifier)->first($dbObject)) {
            // Create Field instance
            $createdField = self::create($dbObject, self::$type, $param, $className);
        }

        // Return created field
        return $createdField;
    }

    /**
     * Create instance of field from metadata
     *
     * @param string $entity
     * @param string $param
     * @param string $identifier
     * @param string $className
     * @return object
     */
    public static function & fromMetadata($entity, $param, $identifier, $className = __CLASS__)
    {
        e('Method cms_input_field:fromMetadata() is deprecated', D_SAMSON_DEBUG);
        $cmsField = null;
        return $cmsField;
//        $dbObject = null;
//
//        // Correct namespace classname generation
//        $entity = ns_classname($entity, __NAMESPACE__);
//        if (!class_exists($entity)) {
//            e(
//                'Cannot create ## object instance - Class ## does not exists',
//                E_SAMSON_CORE_ERROR,
//                array(__CLASS__, $entity)
//            );
//        }
//
//        // Generate correct namespace for class
//        $className = uni_classname(ns_classname(strtolower($className), __NAMESPACE__));
//
//        // Try to get field module instance from core
//        if (null !== ($module = & m($className))) {
//            // Try to find field corresponding entity
//            if (dbQuery($entity)->id($identifier)->first($dbObject)) {
//                // Create field object copy
//                $cmsField = & $module->copy();
//                $cmsField->view_path = $module->view_path;
//                $cmsField->entity = $entity;
//                $cmsField->obj = & $dbObject;
//                $cmsField->param = $param;
//                $cmsField->value = $dbObject[$param];
//            } else {
//                e(
//                    'Cannot create ## object instance - Entity ## with id: ## - does not exists',
//                    E_SAMSON_CORE_ERROR,
//                    array(__CLASS__, $entity, $identifier)
//                );
//            }
//        } else {
//            e(
//                'Cannot create ## object instance - Field module ## not loaded to system core',
//                E_SAMSON_CORE_ERROR,
//                array(__CLASS__,$className)
//            );
//        }
    }

    /**
     * Create instance from object
     *
     * @param mixed 	$obj 	Object for creating inputfield
     * @param string 	$param	Object field name
     * @return \samsoncms\input\Field Class instance
     */
    public static function & fromObject(& $obj, $param = 'Value', $classname = __CLASS__)
    {
        e('Method cms_input_field:fromObject() is deprecated', D_SAMSON_DEBUG);
        $temp = null;
        return $temp;

//
//
//        // If object is passed
//        if (!is_object($obj)) {
//            e('Cannot create ## object instance - no object is passed', E_SAMSON_CORE_ERROR, __CLASS__);
//        }
//
//        // Generate correct namespace for class
//        $classname = \samson\core\AutoLoader::oldClassName(__NAMESPACE__.'\\'.$classname);
//
//        // Try to get field module instance from core
//        if (null !== ($f = & m($classname))) {
//            // Create input field instance
//            $o = & $f->copy();
//            $o->view_path = $f->view_path;
//            $o->entity	= get_class($obj);
//            $o->obj 	= & $obj;
//            $o->param 	= $param;
//            $o->value 	= $obj[$param];
////            trace($o->value, true);
//        } else {
//            e(
//                'Cannot create ## object instance - Field module ## not loaded to system core',
//                E_SAMSON_CORE_ERROR,
//                array(__CLASS__,$classname)
//            );
//        }
//
//        return $o;
    }

    /**
     * Function to retrieve CMS Field value
     *
     * @return mixed CMS Field value
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * Special function for processing value before saving
     *
     * @param mixed $value Value to convert
     * @return int Converted value
     */
    public function convert($value)
    {
        return $value;
    }

    // Controller

    /**
     * Save handler for CMS Field input
     */
    public function __save()
    {
        // Does it necessary?
        s()->async(true);

        $dbObject = null;

        // If we have post data
        if (isset($_POST)) {
            // Make pointers to posted parameters
            $entity = & $_POST['__entity'];
            $param 	= & $_POST['__param'];
            $objectId 	= & $_POST['__obj_id'];
            $value 	= & $_POST['__value'];

            // Check if all nessesarly data is passed
            if (!isset($value)) {
                e('CMSField - no "value" is passed for saving', E_SAMSON_CORE_ERROR);
            }
            if (!isset($entity)) {
                e('CMSField - no "entity" is passed for saving', E_SAMSON_CORE_ERROR);
            }
            if (!isset($objectId)) {
                e('CMSField - no "object identifier" is passed for saving', E_SAMSON_CORE_ERROR);
            }
            if (!isset($param)) {
                e('CMSField - no "object field name" is passed for saving', E_SAMSON_CORE_ERROR);
            }

            // Try to find passed object for saving
            if (dbQuery($entity)->id($objectId)->first($dbObject)) {
                // If object supports numeric value
                if ($param != 'numeric_value' && isset($dbObject['numeric_value'])) {
                    // Convert value to numeric value
                    $dbObject['numeric_value'] = $this->numericValue($value);
                }

                // Set field value and than save it
                $this->obj = $dbObject;
                $this->param = $param;
                $this->save($value);

            } else {
                // Generate error
                e(
                    'CMSField - Entity ## with id: ## - does not exists',
                    E_SAMSON_CORE_ERROR,
                    array($entity, $objectId)
                );
            }
        }
    }

    /**
     * Save input field value
     *
     * @param mixed $value Field value
     */
    public function save($value)
    {
        /** @var mixed $previousValue Previous instance value for transfer in event handlers */
        $previousValue = $this->obj[$this->param];

        // Set field value
        $this->obj[$this->param] = $value;

        // Create new event on object updating
        \samsonphp\event\Event::fire('samson.cms.input.change', array(& $this->obj, $this->param, $previousValue));

        // Save object
        $this->obj->save();
    }

    /** @see \samson\core\iModuleViewable::toView() */
    public function toView($prefix = null, array $restricted = array())
    {
        // Generate unique prefix if not passed
        $innerPrefix = 'field_';

        // Result view collection
        $result = array();

    // 		// Get only this class properties
    // 		$own = array_keys(get_object_vars($this));
    // 		$parent = array_keys(get_object_vars(ns_classname('ExternalModule','samson\core')));
    // 		$properties = array_diff($own, $parent);

        // Iterate object vars and gather them in collection
        foreach (get_object_vars($this) as $key => $value) {
            $result[$innerPrefix.$key] = $value;
        }

        // Generate field action controller
        $result[$innerPrefix.'action'] = url_build($this->id, $this->action);

        // Generate unique textarea id
        $result[$innerPrefix.'textarea_id'] = 'field_'.$this->obj->id;

        // Render inner field view
        $result[$innerPrefix.'view'] = $this->view($this->fieldView)->set($result)->output();

        // Return input fields collection prepared for module view
        return array(
            $prefix.'html' => $this
                ->view($this->defaultView)
                ->set($result)
                ->set($this->obj, 'object')
            ->output()
        );
    }
}
