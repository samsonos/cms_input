<?php
namespace samsoncms\input;

use samson\activerecord\dbRelation;

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

    /** Database object classname which connected  with this field */
    protected $entity;

    /** Database object field name */
    protected $param = 'Value';

    /** Database object current field value */
    protected $value;

    /** Main field action controller */
    protected $action = 'save';

    /** Special CSS classname for nested field objects to bind JS and CSS */
    protected $cssClass;

    /** @var \samson\activerecord\dbRecord Pointer to database object instance */
    protected $obj;

    /** Path to view file for field rendering */
    protected $defaultView = "index";

    /** Path to view file for inner field rendering */
    protected $fieldView = "field";

    /**
     * Create field class ancestor
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

        // TODO: Double nested classes not supported, we need to build class tree

        // Iterate all child classes
        foreach (get_child_classes($className) as $child) {
            // If we have child class with specified type
            if ($child::$type == $type) {
                /** @var string $moduleId Module identifier */
                $moduleId = get_class_vars($child)['id'];

                // If object has not passed
                if (!is_object($dbObject)) {
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
//                    trace($cmsField->obj, true);
                } else { // Module doesn't exists
                    e(
                        'Cannot create ## object instance - Field module ## not loaded to system core',
                        E_SAMSON_CORE_ERROR,
                        array(__CLASS__, $child)
                    );
                }
            }
        }
//        trace($cmsField, true);
        // Return this class or ancestor instance
        return $cmsField;
    }

    /**
     * Create instance of field from metadata
     * @param string $entity
     * @param string $param
     * @param string $identifier
     * @param string $className
     * @return object
     */
    public static function & fromMetadata($entity, $param, $identifier, $className = __CLASS__)
    {
        $cmsField = null;
        $dbObject = null;

        // Correct namespace classname generation
        $entity = ns_classname($entity, __NAMESPACE__);
        if (!class_exists($entity)) {
            e(
                'Cannot create ## object instance - Class ## does not exists',
                E_SAMSON_CORE_ERROR,
                array(__CLASS__, $entity)
            );
        }

        // Generate correct namespace for class
        $className = uni_classname(ns_classname(strtolower($className), __NAMESPACE__));

        // Try to get field module instance from core
        if (null !== ($module = & m($className))) {
            // Try to find field corresponding entity
            if (dbQuery($entity)->id($identifier)->first($dbObject)) {
                // Create field object copy
                $cmsField = & $module->copy();
                $cmsField->view_path = $module->view_path;
                $cmsField->entity = $entity;
                $cmsField->obj = & $dbObject;
                $cmsField->param = $param;
                $cmsField->value = $dbObject[$param];
            } else {
                e(
                    'Cannot create ## object instance - Entity ## with id: ## - does not exists',
                    E_SAMSON_CORE_ERROR,
                    array(__CLASS__, $entity, $identifier)
                );
            }
        } else {
            e(
                'Cannot create ## object instance - Field module ## not loaded to system core',
                E_SAMSON_CORE_ERROR,
                array(__CLASS__,$className)
            );
        }

        return $cmsField;
    }

    /**
     * Create instance from object
     * @param mixed 	$obj 	Object for creating inputfield
     * @param string 	$param	Object field name
     * @return \samsoncms\input\InputField Class instance
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

    public function value()
    {
        return $this->obj[$this->param];
    }

    /**
     * Special function for processing value before saving
     */
    public function numericValue($input)
    {
        return $input;
    }

    // Controller

    /**
     * Save input field value
     * @param mixed $value Field value
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
                return e('CMSField - no "value" is passed for saving', E_SAMSON_CORE_ERROR);
            }
            if (!isset($entity)) {
                return e('CMSField - no "entity" is passed for saving', E_SAMSON_CORE_ERROR);
            }
            if (!isset($objectId)) {
                return e('CMSField - no "object identifier" is passed for saving', E_SAMSON_CORE_ERROR);
            }
            if (!isset($param)) {
                return e('CMSField - no "object field name" is passed for saving', E_SAMSON_CORE_ERROR);
            }

            // Try to find passed object for saving
            if (dbQuery($entity)->id($objectId)->first($dbObject)) {
                // If our object is material field
                if ($dbObject instanceof \samson\activerecord\materialfield) {
                    // Get current material
                    $material = dbQuery('material')->id($dbObject->MaterialID)->first();
                    // If material can have related materials
                    if ($material->type == 1 && $material->parent_id == 0) {
                        // Get related materials identifiers
                        $children = dbQuery('material')->cond('parent_id', $material->id)->fields('MaterialID');

                        // For each child create or update material field record
                        foreach ($children as $child) {
                            if (
                            !dbQuery('materialfield')
                                ->cond('FieldID', $dbObject->FieldID)
                                ->cond('locale', $dbObject->locale)
                                ->cond('MaterialID', $child)
                                ->first($child_mf)
                            ) {
                                $child_mf = new \samson\activerecord\materialfield(false);
                                $child_mf->MaterialID = $child;
                                $child_mf->FieldID = $dbObject->FieldID;
                                $child_mf->locale = $dbObject->locale;
                                $child_mf->Active = 1;
                            }
                            $child_mf->Value = $value;
                            if (isset($dbObject->numeric_value)) {
                                $child_mf->numeric_value = $value;
                            }
                            $child_mf->save();
                        }
                    }
                }
                if ($dbObject instanceof \samson\activerecord\material && $param == 'remains') {
                    /** @var \samson\activerecord\material $parent */
                    $parent = null;
                    if (dbQuery('material')->cond('MaterialID', $dbObject->parent_id)->first($parent)) {
                        $parent->remains = (int)$parent->remains - (int)$dbObject->remains + intval($value);
                        $parent->save();
                    }
                }

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
                e(
                    'CMSField - Entity ## with id: ## - does not exists',
                    E_SAMSON_CORE_ERROR,
                    array($entity, $identifier)
                );
            }
        }
    }

    // Logic

    /**
     * Save input field value
     * @param mixed $value Field value
     */
    public function save($value)
    {
        // Set field value
        $this->obj[ $this->param ] = $value;

        // Create new event on object updating
        \samsonphp\event\Event::fire('samson.cms.input.change', array(& $this->obj));

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
        $result[$innerPrefix.'action'] = url_build(
            \samson\core\AutoLoader::oldClassName(get_class($this)),
            $this->action
        );//'samson_cms_input_field'

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
