<?php

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Scope;
use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\Test\Stub\Transformer\JsonApiBookTransformer;

class JsonApiSerializerTest extends PHPUnit_Framework_TestCase
{
    private $manager;

    /**
     * Create a Manager and make it use the JsonApiSerializer
     */
    public function setUp()
    {
        $this->manager = new Manager();
        $this->manager->setSerializer(new JsonApiSerializer());
    }

    /**
     * One book
     */
    public function testSerializingItemResource()
    {
        $bookData = array(
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
        );

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'book');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'book' => array(
                array(
                    'id' => 1,
                    'type' => 'book',
                    'title' => 'Foo',
                    'year' => 1991,
                ),
            )
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"book":[{"id":1,"type":"book","title":"Foo","year":1991}]}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    /**
     * One book with included author
     */
    public function testSerializingItemResourceWithSingleInclude()
    {
        $this->manager->parseIncludes('author');

        $bookData = array(
            'title' => 'Foo',
            'year' => '1991',
            '_author' => array(
                'id' => 1,
                'name' => 'Dave',
            ),
        );

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'book');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'book' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
            ),
            'linked' => array(
                'author' => array(
                    array(
                        'name' => 'Dave',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"book":[{"title":"Foo","year":1991}],"linked":{"person":[{"name":"Dave"}]}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    /**
     * Two books
     */
    public function testSerializingCollectionResource()
    {
        $this->manager->parseIncludes('author');

        $booksData = array(
            array(
                'title' => 'Foo',
                'year' => '1991',
            ),
            array(
                'title' => 'Bar',
                'year' => '1997',
            ),
        );

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'book');
        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'book' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
                array(
                    'title' => 'Bar',
                    'year' => 1997,
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"book":[{"title":"Foo","year":1991},{"title":"Bar","year":1997}]}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    /**
     * Two books with different authors
     */
    public function testSerializingCollectionResourceWithIncludes()
    {
        $this->manager->parseIncludes('author');

        $booksData = array(
            array(
                'title' => 'Foo',
                'year' => '1991',
                '_author' => array(
                    'name' => 'Dave',
                ),
            ),
            array(
                'title' => 'Bar',
                'year' => '1997',
                '_author' => array(
                    'name' => 'Bob',
                ),
            ),
        );

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'book');
        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'book' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
                array(
                    'title' => 'Bar',
                    'year' => 1997,
                ),
            ),
            'linked' => array(
                'author' => array(
                    array('name' => 'Dave'),
                    array('name' => 'Bob'),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"book":[{"title":"Foo","year":1991},{"title":"Bar","year":1997}],"linked":{"author":[{"name":"Dave"},{"name":"Bob"}]}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    /**
     * Two books with a shared author
     */
    public function testSerializingCollectionResourceWithSharedIncludeData()
    {
        $this->manager->parseIncludes('author');

        $booksData = array(
            array(
                'title' => 'Foo',
                'year' => '1991',
                '_author' => array(
                    'id' => 1,
                    'name' => 'Dave',
                ),
            ),
            array(
                'title' => 'Bar',
                'year' => '1997',
                '_author' => array(
                    'id' => 1,
                    'name' => 'Dave',
                ),
            ),
        );

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');
        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'books' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
                array(
                    'title' => 'Bar',
                    'year' => 1997,
                ),
            ),
            'linked' => array(
                'author' => array(
                    array(
                        'id' => 1,
                        'name' => 'Dave',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"books":[{"title":"Foo","year":1991},{"title":"Bar","year":1997}],"linked":{"author":[{"id":1,"name":"Dave"}]}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    /**
     * Two books with their author's countries
     */
    public function testSerializingCollectionResourceWithNestedIncludes()
    {
        $this->manager->parseIncludes('author.country');

        $booksData = array(
            array(
                'title' => 'Foo',
                'year' => '1991',
                '_author' => array(
                    'id' => 1,
                    'name' => 'Dave',
                    '_country' => array(
                        'id' => 1,
                        'name' => 'The Netherlands',
                    ),
                ),
            ),
            array(
                'title' => 'Bar',
                'year' => '1997',
                '_author' => array(
                    'id' => 2,
                    'name' => 'Bob',
                    '_country' => array(
                        'id' => 2,
                        'name' => 'Belgium',
                    ),
                ),
            ),
        );

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');
        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'books' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
                array(
                    'title' => 'Bar',
                    'year' => 1997,
                ),
            ),
            'linked' => array(
                'country' => array(
                    array(
                        'id' => 1,
                        'name' => 'The Netherlands',
                    ),
                    array(
                        'id' => 2,
                        'name' => 'Belgium',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"books":[{"title":"Foo","year":1991},{"title":"Bar","year":1997}],"linked":{"country":[{"id":1,"name":"The Netherlands"},{"id":2,"name":"Belgium"}]}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    /**
     * Two books with their authors and their author's countries
     */
    public function testSerializingCollectionResourceWithDirectAndNestedIncludes()
    {
        $this->manager->parseIncludes(array('author', 'author.country'));

        $booksData = array(
            array(
                'title' => 'Foo',
                'year' => '1991',
                '_author' => array(
                    'id' => 1,
                    'name' => 'Dave',
                    '_country' => array(
                        'id' => 1,
                        'name' => 'The Netherlands',
                    ),
                ),
            ),
            array(
                'title' => 'Bar',
                'year' => '1997',
                '_author' => array(
                    'id' => 2,
                    'name' => 'Bob',
                    '_country' => array(
                        'id' => 2,
                        'name' => 'Belgium',
                    ),
                ),
            ),
        );

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');
        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'books' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
                array(
                    'title' => 'Bar',
                    'year' => 1997,
                ),
            ),
            'linked' => array(
                'author' => array(
                    array(
                        'id' => 1,
                        'name' => 'Dave',
                    ),
                    array(
                        'id' => 2,
                        'name' => 'Bob',
                    ),
                ),
                'country' => array(
                    array(
                        'id' => 1,
                        'name' => 'The Netherlands',
                    ),
                    array(
                        'id' => 2,
                        'name' => 'Belgium',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"books":[{"title":"Foo","year":1991},{"title":"Bar","year":1997}],"linked":{"country":[{"id":1,"name":"The Netherlands"},{"id":2,"name":"Belgium"}],"author":[{"id":1,"name":"Dave"},{"id":2,"name":"Bob"}]}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    /**
     * Two books, their libraries, their authors, their author's countries, their author's countries' presidents and their author's pets
     */
    public function testSerializingCollectionResourceWithIrregularAndPartiallyOverlappingNestedIncludes()
    {
        $this->manager->parseIncludes(array(
            'library',
            'author',
            'author.pet',
            'author.country',
            'author.country.king',
        ));

        $booksData = array(
            array(
                'title' => 'Foo',
                'year' => '1991',
                '_library' => array(
                    'id' => 1,
                    'name' => 'House of Wisdom',
                ),
                '_author' => array(
                    'id' => 1,
                    'name' => 'Dave',
                    '_pet' => array(
                        'id' => 1,
                        'name' => 'Bertha',
                    ),
                    '_country' => array(
                        'id' => 1,
                        'name' => 'The Netherlands',
                        '_king' => array(
                            'id' => 4,
                            'name' => 'Willem Alexander',
                        ),
                    ),
                ),
            ),
            array(
                'title' => 'Bar',
                'year' => '1995',
                '_library' => array(
                    'id' => 2,
                    'name' => 'House of Wisdom',
                ),
                '_author' => array(
                    'id' => 2,
                    'name' => 'Bob',
                    '_country' => array(
                        'id' => 2,
                        'name' => 'Belgium',
                        '_king' => array(
                            'id' => 5,
                            'name' => 'Ben',
                        ),
                    ),
                ),
            ),
            array(
                'title' => 'Baz',
                'year' => '1997',
                '_library' => array(
                    'id' => 3,
                    'name' => 'Library of Alexandria',
                ),
                '_author' => array(
                    'id' => 3,
                    'name' => 'Bill',
                    '_country' => array(
                        'id' => 3,
                        'name' => 'The Land of Cockaigne',
                    ),
                ),
            ),
            array(
                'title' => 'Buh',
                'year' => '2003',
                '_author' => array(
                    'id' => 5,
                    'name' => 'Ben',
                    '_pet' => array(
                        'id' => 2,
                        'name' => 'Truus',
                    ),
                    '_country' => array(
                        'id' => 2,
                        'name' => 'Belgium',
                        '_king' => array(
                            'id' => 5,
                            'name' => 'Ben',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Item with meta data on the response
     */
    public function testSerializingItemResourceWithMeta()
    {
        $this->manager->parseIncludes('author');

        $bookData = array(
            'title' => 'Foo',
            'year' => '1991',
            '_author' => array(
                'name' => 'Dave',
            ),
        );

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'book');
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'book' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
            ),
            'linked' => array(
                'author' => array(
                    array(
                        'name' => 'Dave',
                    ),
                ),
            ),
            'meta' => array(
                'foo' => 'bar',
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"book":[{"title":"Foo","year":1991}],"linked":{"author":[{"name":"Dave"}]},"meta":{"foo":"bar"}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    /**
     * Collection with meta data on the response
     */
    public function testSerializingCollectionResourceWithMeta()
    {
        $this->manager->parseIncludes('author');

        $booksData = array(
            array(
                'title' => 'Foo',
                'year' => '1991',
                '_author' => array(
                    'name' => 'Dave',
                ),
            ),
            array(
                'title' => 'Bar',
                'year' => '1997',
                '_author' => array(
                    'name' => 'Bob',
                ),
            ),
        );

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'book');
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'book' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
                array(
                    'title' => 'Bar',
                    'year' => 1997,
                ),
            ),
            'linked' => array(
                'author' => array(
                    array('name' => 'Dave'),
                    array('name' => 'Bob'),
                ),
            ),
            'meta' => array(
                'foo' => 'bar',
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"book":[{"title":"Foo","year":1991},{"title":"Bar","year":1997}],"linked":{"author":[{"name":"Dave"},{"name":"Bob"}]},"meta":{"foo":"bar"}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    /**
     * Book with no include key specified
     */
    public function testResourceKeyMissing()
    {
        $this->manager->setSerializer(new JsonApiSerializer());

        $bookData = array(
            'title' => 'Foo',
            'year' => '1991',
        );

        $resource = new Item($bookData, new JsonApiBookTransformer());
        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());
    }

    /**
     * Not that we're mocking
     */
    public function tearDown()
    {
        Mockery::close();
    }
}
