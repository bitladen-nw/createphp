<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author David Buchmann <david@liip.ch>
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Metadata;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Entity\Controller as Type;
use Midgard\CreatePHP\Entity\Property as PropertyDefinition;
use Midgard\CreatePHP\Entity\Collection as CollectionDefinition;
use Midgard\CreatePHP\Type\TypeInterface;
use Midgard\CreatePHP\Type\PropertyDefinitionInterface;

/**
 * Base driver class with helper methods for drivers
 *
 * @package Midgard.CreatePHP
 */
abstract class AbstractRdfDriver implements RdfDriverInterface
{
    private $definitions = array();

    /**
     * @param array $definitions array of type definitions
     */
    public function __construct($definitions)
    {
        $this->definitions = $definitions;
    }

    /**
     * Build the property attribute from this child definition. The identifier is
     * used in case there is no property defined on the child - in which case the
     * $add_default_vocabulary flag is set to true. The caller has to set the
     * createphp vocabulary in that case.
     *
     * @param \ArrayAccess $child the child to read field from
     * @param string $field the field to be read, property for properties, rel for collections
     * @param string $identifier to be used in case there is no property field in $child
     * @param boolean $add_default_vocabulary flag to tell whether to add vocabulary for
     *      the default namespace.
     *
     * @return string property value
     */
    protected function buildInformation($child, $identifier, $field, &$add_default_vocabulary)
    {
        if (isset($child[$field])) {
            return (string) $child[$field];
        }
        switch($field) {
            case 'rel':
                return 'dcterms:hasPart';
            case 'rev':
                return 'dcterms:partOf';
            default:
                $add_default_vocabulary = true;
                return self::DEFAULT_VOCABULARY_PREFIX . ':' . $identifier;
        }
    }

    /**
     * Get the config information for this element
     *
     * @param mixed $element the configuration element representing any kind of node to read the config from
     *
     * @return array of key-value mappings for configuration
     */
    protected abstract function getConfig($element);

    /**
     * Create a NodeInterface wrapping a type instance.
     *
     * @param RdfMapperInterface $mapper
     * @param array $config
     * @return \Midgard\CreatePHP\NodeInterface
     */
    protected function createType(RdfMapperInterface $mapper, $config)
    {
        return new Type($mapper, $config);
    }

    /**
     * Instantiate a type model for this kind of child element
     *
     * @param string $type the type information from the configuration
     * @param string $identifier the field name of this child
     * @param mixed $element the configuration element
     * @param RdfTypeFactory $typeFactory
     *
     * @return \Midgard\CreatePHP\Type\RdfElementDefinitionInterface
     *
     * @throws \Exception if $type is unknown
     */
    protected function createChild($type, $identifier, $element, RdfTypeFactory $typeFactory)
    {
        switch($type) {
            case 'property':
                $child = new PropertyDefinition($identifier, $this->getConfig($element));
                break;
            case 'collection':
                $child = new CollectionDefinition($identifier, $typeFactory, $this->getConfig($element));
                break;
            default:
                throw new \Exception('unknown child type '.$type.' with identifier '.$identifier);
        }
        return $child;
    }
}