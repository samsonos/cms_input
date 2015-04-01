<?php
namespace samsoncms\input;

use samson\activerecord\dbQuery;
use samson\activerecord\dbRecord;
use samsonphp\event\Event;

/**
 * Generic SamsonCMS input field
 * @author Vitaly Iegorov<egorov@samsonos.com>
 *
 */
class Field
{

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
     * @param dbQuery $dbQuery Database object
     * @param string|dbRecord $entity Class name or object
     * @param string|null $param $entity class field
     * @param int $identifier Identifier to find and create $entity instance
     **/
    public function __construct($dbQuery, $entity, $param = null, $identifier = null)
    {
        $this->dbQuery = $dbQuery;
        $this->param = isset($param) ? $param : $this->param;

        if (!is_object($entity)) {
            if (isset($identifier)) {
                $this->entity = $entity;
                $this->dbObject = $this->dbQuery->className($entity)->id($identifier)->first();
            } else {
                e(
                    'Cannot create ## object instance - no identifier was passed',
                    E_SAMSON_CORE_ERROR,
                    __CLASS__
                );
            }
        } else {
            $this->dbObject = $entity;
            $this->entity = get_class($entity);
            $this->dbQuery->className($this->entity);
        }

        $this->value = $this->dbObject[$this->param];
    }

    /**
     * Function to retrieve CMS Field value
     *
     * @return mixed CMS Field value
     */
    public function getValue()
    {
        return $this->value;
    }

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

        if ($this->param != 'numeric_value' && isset($this->dbObject['numeric_value'])) {
            // Convert value to numeric value
            $this->dbObject['numeric_value'] = $this->convert($value);
        }

        // Set field value
        $this->dbObject[$this->param] = $value;

        // Create new event on object updating
        Event::fire('samson.cms.input.change', array(& $this->dbObject, $this->param, $previousValue));

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
