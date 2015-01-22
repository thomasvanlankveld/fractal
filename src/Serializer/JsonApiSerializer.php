<?php

/*
 * This file is part of the League\Fractal package.
 *
 * (c) Phil Sturgeon <me@philsturgeon.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Fractal\Serializer;

use League\Fractal\Resource\ResourceInterface;

class JsonApiSerializer extends ArraySerializer
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

        // Don't ask
        foreach ($data as $value) {
            foreach ($value as $collections) {

                // Put linked collections into serializedData
                if (isset($collections['linked'])) {
                    foreach ($collections['linked'] as $key => $collection) {

                        // If the collection already existed in serializedData
                        if (isset($serializedData[$key])) {
                            $serializedData[$key] = array_merge($serializedData[$key], $collection);
                            continue;
                        }

                        // If the collection didn't existed yet
                        $serializedData[$key] = $collection;
                    }
                    unset($collections['linked']);
                }

                // Serialize each collection
                foreach ($collections as $collectionKey => $collection) {

                    // If the collection is empty, move along
                    if (empty($collection)) {
                        continue;
                    }

                    // Add every item in the collection to serializedData
                    foreach ($collection as $item) {

                        // Add item without id
                        if (!array_key_exists('id', $item)) {
                            $serializedData[$collectionKey][] = $item;
                            continue;
                        }

                        // Don't add item if it's already in the collection
                        $itemId = $item['id'];
                        if (!empty($linkedIds[$collectionKey]) && in_array($itemId, $linkedIds[$collectionKey], true)) {
                            continue;
                        }

                        // Add new item
                        $serializedData[$collectionKey][] = $item;
                        $linkedIds[$collectionKey][] = $itemId;
                    }
                }
            }
        }

        return empty($serializedData) ? array() : array('linked' => $serializedData);
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
