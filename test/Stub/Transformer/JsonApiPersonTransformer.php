<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\TransformerAbstract;

class JsonApiPersonTransformer extends TransformerAbstract
{
    protected $availableIncludes = array(
        'country',
    );

    public function transform(array $person)
    {
    	unset($person['_country']);

        return $person;
    }

    public function includeCountry(array $person)
    {
        return $this->item($person['_country'], new JsonApiCountryTransformer(), 'country');
    }
}
