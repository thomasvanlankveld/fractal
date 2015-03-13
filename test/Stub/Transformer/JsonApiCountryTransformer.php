<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\TransformerAbstract;

class GenericCountryTransformer extends TransformerAbstract
{
    protected $availableIncludes = array(
        'king',
    );

    public function transform(array $country)
    {
        unset($country['_king']);

        return $country;
    }

    public function includeKing(array $country)
    {
        return $this->item($country['_king'], new GenericPersonTransformer(), 'person');
    }
}
