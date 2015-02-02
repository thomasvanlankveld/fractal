<?php

namespace League\Fractal;

/**
 * Implementing this interface on a class and defining these methods
 * in it allows you to use that class as a Transformer.
 */
interface TransformerInterface {

    /**
     * Getter for availableIncludes.
     *
     * @return array
     */
    public function getAvailableIncludes();

    /**
     * Getter for defaultIncludes.
     *
     * @return array
     */
    public function getDefaultIncludes();

    /**
     * This method is fired to loop through available includes, see if any of
     * them are requested and permitted for this scope.
     *
     * @internal
     *
     * @param Scope $scope
     * @param mixed $data
     *
     * @return array
     */
    public function processIncludedResources(Scope $scope, $data);
}
