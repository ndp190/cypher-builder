<?php

namespace Nikk\CypherBuilder\Builder;


/**
 * CypherBuilder class is responsible to dynamically create Neo4j Cypher queries.
 * @author Nikk Nguyen <ndp190@gmail.com>
 */
class CypherBuilder
{
    const MATCH = 0;
    const OPTIONAL_MATCH = 1;
    const CREATE = 2;
    const MERGE = 3;

    /**
     * The complete cypher string for this query
     *
     * @var string
     */
    private $cypher;
}