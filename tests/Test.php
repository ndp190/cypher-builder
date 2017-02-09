<?php

namespace Nikk\CypherBuilder\Test;

use GraphAware\Neo4j\Client\ClientBuilder;
use Nikk\CypherBuilder\Builder\CypherBuilder;

class Test extends \PHPUnit_Framework_TestCase
{
    public function testMatch()
    {
        $this->markTestIncomplete();
//        $graphUrl = '';
//        $client = ClientBuilder::create()->addConnection('default', $graphUrl)->build();

//        // MATCH (foo:Foo {prop: "value"})-[r:fooRel|:barRel]->(bar:Bar)<-[rr:bazRel]-(baz:Baz) RETURN foo,baz
//        $cb = CypherBuilder::createBuilder($client) // maybe another class for creation
//            ->match(
//                $cb::connection()
//                    ->alias('r')->type('fooRel')->orType('barRel')
//                    ->from($cb::node()->..) // node builder
//                    ->to($cb::node()->..)
//            // examine if MATCH (a)-[]->(b)<-[]-(c) is equal to MATCH (a)-[]->(b),(b)<-[]-(c)
//            // list out frequent use cypher
//            );
    }
}
