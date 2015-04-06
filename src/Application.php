<?php
/**
 * Created by Maxim Omelchenko <omelchenko@samsonos.com>
 * on 31.03.2015 at 15:24
 */
namespace samsoncms\input;

use samson\activerecord\dbQuery;

/**
 * SamsonCMS input module
 * @author Max Omelchenko <omelchenko@samsonos.com>
 */
class Application extends \samson\core\CompressableExternalModule implements \samson\core\iModuleViewable
{
    public static $type;

    /** @var string SamsonCMS field class */
    protected $fieldClass = '\samsoncms\input\Field';

    /** @var Field|null SamsonCMS field object */
    protected $field = null;

    /**
     * Create field class instance
     *
     * @param \samson\activerecord\dbQuery $dbQuery Database object
     * @param string|\samson\activerecord\dbRecord $entity Class name or object
     * @param string|null $param $entity class field
     * @param int $identifier Identifier to find and create $entity instance
     * @return self Chaining
     */
    public function createField($dbQuery, $entity, $param = null, $identifier = null)
    {
        if (class_exists($this->fieldClass)) {
            $this->field = new $this->fieldClass($dbQuery, $entity, $param, $identifier);
        } else {
            e('Class (##) not found', E_SAMSON_CORE_ERROR, $this->fieldClass);
        }
        return $this;
    }

    public function createFieldByType($dbQuery, $type, $entity, $param = null, $identifier = null)
    {
        /** @var Application $module Variable to store Field or it's ancestor module */
        $module = null;

        /** @var Application $child */
        foreach (get_child_classes(__CLASS__) as $child) {
            if ($child::$type == $type) {
                // Generate module identifier from its class name
                $moduleId = strtolower(str_replace('\\', '_', $child));

                /** @var string $moduleId Module identifier */
                if (($module = &m($moduleId)) !== null) {
                    $module->createField($dbQuery, $entity, $param, $identifier);
                }
            }
        }
        // return module
        return $module;
    }

    /**
     * Save handler for CMS Field input
     */
    public function __async_save()
    {
        $response = array('status' => 0);

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
            $this->createField(new dbQuery(), $entity, $param, $identifier);

            $response['status'] = 1;
            // Save specified value to SamsonCMS input
            $this->field->save($value, $response);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function toView($prefix = null, array $restricted = array())
    {
        // Return input fields collection prepared for module view
        return array($prefix . 'html' => $this->field->view($this));
    }
}
