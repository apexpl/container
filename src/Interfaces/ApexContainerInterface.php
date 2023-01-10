<?php

namespace Apex\Container\Interfaces;

use Psr\Container\ContainerInterface;

/**
 * Container interface
 */
interface ApexContainerInterface extends ContainerInterface
{

    /**
     * Set item
     */
    public function set(string $id, mixed $item):void;

    /**
     * Make
     */
    public function make(string $name, array $params = []):mixed;

    /**
     * Make set
     */
    public function makeset(string $name, array $params = []):mixed;

    /**
     * Call
     */
    public function call(callable | array | string $callable, array $params = []):mixed;


    /**
     * Build container
     */
    public function buildContainer(string $config_file = '', array $items = []):void;


    /**
     * Mark item as service
     */
    public function markItemAsService(string $id):bool;


    /**
     * Unmark item as service
     */
    public function unmarkItemAsService(string $id):bool;

    /**
     * Add alias
     */
    public function addAlias(string $alias, string $value, bool $alias_class = true):void;

    /**
     * Remove service
     */
    public function removeAlias(string $alias):void;

    /**
     * Get fail reason
     */
    public function getFailReason():string;


}


