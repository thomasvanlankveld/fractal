<?php

/*
 * This file is part of the League\Fractal package.
 *
 * (c) Thomas van Lankveld <thomas.van.lankveld@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Fractal\Serializer;

use League\Fractal\Resource\ResourceInterface;

class EmberSerializer extends ArraySerializer
{
    /**
     * Serialize a collection.
     *
     * @param string $resourceKey
     * @param array  $data
     *
     * @return array
     */
    public function collection($resourceKey, array $data)
    {
        return array($resourceKey ?: 'data' => $data);
    }

    /**
     * Serialize an item.
     *
     * @param string $resourceKey
     * @param array  $data
     *
     * @return array
     */
    public function item($resourceKey, array $data)
    {
        return array($resourceKey ?: 'data' => array($data));
    }

    /**
     * Serialize the included data.
     *
     * @param ResourceInterface $resource
     * @param array             $data
     *
     * @return array
     */
    public function includedData(ResourceInterface $resource, array $data)
    {
        $serializedData = array();
        $linkedIds = array();

        // var_dump($data);


        foreach ($data as $value) {
            foreach ($value as $includeValue) {

                // Prevent empty collection error
                if (empty(array_values($includeValue)[0][0])) {
                    continue;
                }

                $includeKey = array_keys($includeValue)[0];
                $itemValue = array_values($includeValue)[0][0];

                // ???
                if (!array_key_exists('id', $itemValue)) {
                    $serializedData[$includeKey][] = $itemValue;
                    continue;
                }

                // ???
                $itemId = $itemValue['id'];
                if (!empty($linkedIds[$includeKey]) && in_array($itemId, $linkedIds[$includeKey], true)) {
                    continue;
                }

                $serializedData[$includeKey][] = $itemValue;
                $linkedIds[$includeKey][] = $itemId;
            }
        }

        return empty($serializedData) ? array() : $serializedData;
    }

    /**
     * Indicates if includes should be side-loaded.
     *
     * @return bool
     */
    public function sideloadIncludes()
    {
        return true;
    }
}