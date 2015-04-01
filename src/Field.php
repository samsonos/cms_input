<?php
namespace samsoncms\input;

use samson\activerecord\dbQuery;
use samson\activerecord\dbRecord;

/**
 * Generic SamsonCMS input field
 * @author Vitaly Iegorov<egorov@samsonos.com>
 *
 */
class Field
{
//    /** @var  int Field type identifier */
//    protected static $type;

    /** Database object class name which connected  with this field */
    protected $entity;

    /** Database object field name */
    protected $param = 'Value';

    /** Database object current field value */
    protected $value;

    /** Special CSS classname for nested field objects to bind JS and CSS */
    protected $cssClass = '__textarea';

    /** @var dbRecord Pointer to database object instance */
    protected $dbObject;

    /** @var null|dbQuery Pointer to activerecord dbQuery instance */
    protected $dbQuery = null;

    /**
     * Constructor
     *
     * @param string|dbRecord $entity Class name or object
     * @param string|null $param $entity class field
     * @param int $identifier Identifier to find and create $entity instance
     * @param dbQuery|null $dbQuery Database object
     **/
    public function __construct($entity, $param = null, $identifier = null, $dbQuery = null)
    {
        $this->dbQuery = isset($dbQuery) ? $dbQuery : new dbQuery();
        $this->param = isset($param) ? $param : $this->param;

        if (!is_object($entity)) {
            $this->entity = $entity;
            $this->dbObject = $this->dbQuery->className($entity)->id($identifier)->first();
        } else {
            $this->dbObject = $entity;
            $this->entity = get_class($entity);
            $this->dbQuery->className($this->entity);
        }

        $this->value = $this->dbObject[$this->param];
    }

//    /**
//     * Create field class ancestor
//     *
//     * @param \samson\activerecord\dbRecord $dbObject Any database object
//     * @param int $type Field type identifier
//     * @param string $param dbObject field
//     * @param string $className Class name for double and more ancestors
//     * @return self Field class ancestor instance
//     */
//    public function create($dbObject, $type, $param = null, $className = __CLASS__)
//    {
//        /** @var \samsoncms\input\Field $cmsField This class or it's ancestor entity */
//        $cmsField = null;
//        /** @var \samson\core\ExternalModule $module Variable to store Field or it's ancestor module */
//        $module = null;
//
//        // TODO: Double nested classes not supported, we need to build class tree
//
//        // Iterate all child classes
//        foreach (get_child_classes($className) as $child) {
//            // If we have child class with specified type
//            if ($child::$type == $type) {
//                /** @var string $moduleId Module identifier */
//                $moduleId = get_class_vars($child)['id'];
//
//                // If object has not passed
//                if (!is_object($dbObject)) {
//                    // Generate error
//                    e(
//                        'Cannot create ## object instance - no database record object is passed',
//                        E_SAMSON_CORE_ERROR,
//                        __CLASS__
//                    );
//                }
//
//                // Try to get field module instance from core
//                if (null !== ($module = & m($moduleId))) {
//                    // Create input field instance
//                    $cmsField = & $module->copy();
//                    $cmsField->view_path = $module->view_path;
//                    $cmsField->entity = get_class($dbObject);
//                    $cmsField->obj = & $dbObject;
//                    $cmsField->param = isset($param) ? $param : $cmsField->param;
//                    $cmsField->value = $dbObject[$cmsField->param];
//                } else { // Module doesn't exists
//                    // Generate error
//                    e(
//                        'Cannot create ## object instance - Field module ## not loaded to system core',
//                        E_SAMSON_CORE_ERROR,
//                        array(__CLASS__, $child)
//                    );
//                }
//            }
//        }
//        // Return this class or ancestor instance
//        return $cmsField;
//    }
//
//    /**
//     * Create field instance from input data
//     *
//     * @param string $entity class name it should be \samson\activerecord\dbRecord ancestor
//     * @param string $param $entity class field
//     * @param int $identifier Identifier to find and create $entity instance
//     * @param string $className Class name for double and more ancestors
//     * @return null|Field If given entity instance exists return Filed object otherwise return null
//     */
//    public static function createFromMetadata($entity, $param, $identifier, $className = __CLASS__)
//    {
//        /** @var \samson\activerecord\dbRecord $dbObject Given entity instance */
//        $dbObject = null;
//        /** @var Field $createdField Created field instance */
//        $createdField = null;
//
//        // If we can find given entity instance
//        if (dbQuery($entity)->id($identifier)->first($dbObject)) {
//            // Create Field instance
//            $createdField = self::create($dbObject, self::$type, $param, $className);
//        }
//
//        // Return created field
//        return $createdField;
//    }

    /**
     * Function to retrieve CMS Field value
     *
     * @return mixed CMS Field value
     */
    public function getValue()
    {
        return $this->value;
    }

//    /**
//     * @return mixed Object
//     */
//    public function getDBObject()
//    {
//        return $this->dbObject;
//    }

    /**
     * Special function for processing value before saving
     *
     * @param mixed $value Value to convert
     * @return int Converted value
     */
    protected function convert($value)
    {
        return $value;
    }

    /**
     * Save input field value
     *
     * @param mixed $value Field value
     */
    public function save($value)
    {
        /** @var mixed $previousValue Previous instance value for transfer in event handlers */
        $previousValue = $this->dbObject[$this->param];

//        $value = $this->convert($value);

        if ($this->param != 'numeric_value' && isset($this->dbObject['numeric_value'])) {
            // Convert value to numeric value
            $this->dbObject['numeric_value'] = $this->convert($value);
        }

        // Set field value
        $this->dbObject[$this->param] = $value;

        // Create new event on object updating
        \samsonphp\event\Event::fire('samson.cms.input.change', array(& $this->dbObject, $this->param, $previousValue));

        // Save object
        $this->dbObject->save();
    }

    /**
     * Function to retrieve all object vars
     *
     * @param string $innerPrefix Prefix to form array keys
     * @return array Object vars
     */
    public function getObjectData($innerPrefix = '')
    {
        $result = array();
        foreach (get_object_vars($this) as $key => $value) {
            $result[$innerPrefix . $key] = $value;
        }
        return $result;
    }
}
