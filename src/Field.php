<?php
namespace samsoncms\input;

use samson\activerecord\dbQuery;
use samson\activerecord\dbRecord;
use samsonphp\event\Event;

/**
 * Generic SamsonCMS input field
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @author Max Omelchenko <omelchenko@samsonos.com>
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

    /** Path to view file for field rendering */
    protected $defaultView = "index";

    /** Path to view file for inner field rendering */
    protected $fieldView = "field";

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

        // Check what has passed in $entity parameter
        if (!is_object($entity)) {
            // If it's not object save it as entity
            $this->entity = $entity;
            // If identifier was passed try to create object from database object
            // Otherwise show error
            if (isset($identifier)) {
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
    public function value()
    {
        return $this->dbObject[$this->param];
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
    public function save($value, & $response = null)
    {
        /** @var mixed $previousValue Previous instance value for transfer in event handlers */
        $previousValue = $this->dbObject[$this->param];

        // Set field value
        $this->dbObject[$this->param] = $this->convert($value);

        // Create new event on object updating
        Event::fire('samson.cms.input.change', array(& $this->dbObject, $this->param, $previousValue, & $response));

        // Save object
        $this->dbObject->save();
    }

    /**
     * Function to render inner object
     *
     * @param Application $renderer Renderer object
     * @return string HTML string
     */
    protected function viewField($renderer)
    {
        return $renderer->view($this->fieldView)
            ->set('fieldId', 'field_' . $this->dbObject->id)
            ->set('value', $this->value())
            ->output();
    }

    /**
     * Function to render class
     *
     * @param Application $renderer Renderer object
     * @param string $saveHandler Save controller name
     * @return string HTML string
     */
    public function view($renderer, $saveHandler = 'save')
    {
        return $renderer->view($this->defaultView)
            ->set('cssClass', $this->cssClass)
            ->set('value', $this->value())
            ->set('action', url_build(preg_replace('/(_\d+)/', '', $renderer->id()), $saveHandler))
            ->set('entity', $this->entity)
            ->set('param', $this->param)
            ->set('objectId', $this->dbObject->id)
            ->set('applicationId', $renderer->id())
            ->set('fieldView', $this->viewField($renderer))
            ->output();
    }
}
