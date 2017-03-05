<?php

namespace Nikk\CypherBuilder\Test;

use nikk\cypherbuilder\builder\CypherBuilder;

class Test extends \PHPUnit_Framework_TestCase
{
    public function testMatch()
    {
// // Detail version
// $cb = CypherBuilder::createBuilder($client) // maybe another class for creation
//    ->match(
//        $cb::connection(ConnectionType::DIRECTION) // DIRECTION, NON-DIRECTION
//            ->alias('r')->type('fooRel')->orType('barRel')
//            ->from($cb::node('Foo')->alias('foo')->prop('prop', 'value')) // node builder
//            ->to($cb::node())
//     )

// // Easy version
// $cb = CypherBuilder::createBuilder($client)
//     ->match('foo:Foo {prop: "value"}-[r:fooRel|:barRel]->()<-[rr:bazRel]-(baz:Baz)')
//     ->where('foo.name = {fooName}')
//     ->andWhere('exists(r.rr)')
//     ->return('foo, baz')
//     ->setParameters([
//         'fooName' => 'blah'
//     ])
//     ->getCypher()
//     ->execute()
//     ->fetchAll();

        // examine if MATCH (a)-[]->(b)<-[]-(c) is equal to MATCH (a)-[]->(b),(b)<-[]-(c)
        // list out create, delete, merge

        $cb = new CypherBuilder();
        $cb
            ->match('(a:A)-[:r]->(b:B)')
            ->where('a.name = "foo"')
            ->addMatch('(c:C)-[:r]->(d:D)')
            ->andWhere('c.id = 1')
            ->returning('c')
            ->setSkip(5)
            ->setLimit(10);

        $expectedCypher = <<<EOT
MATCH (a:A)-[:r]->(b:B),
(c:C)-[:r]->(d:D)
WHERE (a.name = "foo") AND (c.id = 1)
RETURN c
SKIP 5
LIMIT 10
EOT;
        $this->assertEquals($expectedCypher, $cb->getCypher());
    }
}
