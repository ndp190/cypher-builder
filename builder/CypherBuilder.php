<?php

namespace nikk\cypherbuilder\builder;
use Doctrine\DBAL\Query\Expression\CompositeExpression;


/**
 * CypherBuilder class is responsible to dynamically create Neo4j Cypher queries.
 * @author Nikk Nguyen <ndp190@gmail.com>
 */
class CypherBuilder
{
    /**
     * The query types
     */
    const MATCH = 0;
    const CREATE = 1;
    const MERGE = 2;

    const STATE_DIRTY = 0;
    const STATE_CLEAN = 1;

    /**
     * The complete cypher string for this query
     *
     * @var string
     */
    private $cypher;

    /**
     * The type of cypher this is. Can be match, create or merge
     *
     * @var int
     */
    private $type = self::MATCH;

    /**
     * The state of the cypher. Can be dirty or clean.
     *
     * @var integer
     */
    private $state = self::STATE_CLEAN;

    /**
     * All cypher parts in order
     *
     * @var array
     */
    private $cypherParts = [
        'match'         => array(),
        'create'        => array(),
        'merge'         => array(),
        'where'         => null,
        'optionalMatch' => array(),
        'optionalWhere' => null,
        'with'          => array(),
        'orderBy'       => array(),
        'set'           => array(),
        'delete'        => array(),
        'remove'        => array(),
        'foreach'       => array(),
        'return'        => array(),
    ];

    /**
     * The index of first result to retrieve
     *
     * @var int
     */
    private $skip;

    /**
     * The maximum number of results to retrieve
     *
     * @var int
     */
    private $limit;

    public function match($match = null)
    {
        $this->type = self::MATCH;

        if (empty($match)) {
            return $this;
        }

        $match = is_array($match) ? $match : func_get_args();

        return $this->add('match', $match, false);
    }

    public function addMatch($match = null)
    {
        $this->type = self::MATCH;

        if (empty($match)) {
            return $this;
        }

        $match = is_array($match) ? $match : func_get_args();

        return $this->add('match', $match, true);
    }

    public function optionalMatch($optionalMatch = null)
    {
        $this->type = self::MATCH;

        if (empty($optionalMatch)) {
            return $this;
        }

        $optionalMatch = is_array($optionalMatch) ? $optionalMatch : func_get_args();

        return $this->add('optionalMatch', $optionalMatch, false);
    }

    public function addOptionalMatch($optionalMatch = null)
    {
        $this->type = self::MATCH;

        if (empty($optionalMatch)) {
            return $this;
        }

        $optionalMatch = is_array($optionalMatch) ? $optionalMatch : func_get_args();

        return $this->add('optionalMatch', $optionalMatch, true);
    }

    public function create($create = null)
    {
        $this->type = self::CREATE;

        if (empty($create)) {
            return $this;
        }

        $create = is_array($create) ? $create : func_get_args();

        return $this->add('create', $create, false);
    }

    public function addCreate($create = null)
    {
        $this->type = self::CREATE;

        if (empty($create)) {
            return $this;
        }

        $create = is_array($create) ? $create : func_get_args();

        return $this->add('create', $create, true);
    }

    public function merge($merge = null)
    {
        $this->type = self::MERGE;

        if (empty($merge)) {
            return $this;
        }

        $merge = is_array($merge) ? $merge : func_get_args();

        return $this->add('merge', $merge, false);
    }

    public function addMerge($merge = null)
    {
        $this->type = self::MERGE;

        if (empty($merge)) {
            return $this;
        }

        $merge = is_array($merge) ? $merge : func_get_args();

        return $this->add('merge', $merge, true);
    }

    public function with($with)
    {
        return $this->add('with', $with, true);
    }

    public function orderBy($sort, $order = null)
    {
        return $this->add('orderBy', $sort . ' ' . (! $order ? 'ASC' : $order), false);
    }

    public function addOrderBy($sort, $order = null)
    {
        return $this->add('orderBy', $sort . ' ' . (! $order ? 'ASC' : $order), true);
    }

    public function where($predicates)
    {
        if (!(func_num_args() == 1 && $predicates instanceof CompositeExpression)) {
            $predicates = new CompositeExpression(CompositeExpression::TYPE_AND, func_get_args());
        }

        return $this->add('where', $predicates, false);
    }

    public function andWhere($where)
    {
        $args = func_get_args();
        $where = $this->getCypherPart('where');

        if ($where instanceof CompositeExpression && $where->getType() === CompositeExpression::TYPE_AND) {
            $where->addMultiple($args);
        } else {
            array_unshift($args, $where);
            $where = new CompositeExpression(CompositeExpression::TYPE_AND, $args);
        }

        return $this->add('where', $where, true);
    }

    public function orWhere($where)
    {
        $args = func_get_args();
        $where = $this->getCypherPart('where');

        if ($where instanceof CompositeExpression && $where->getType() === CompositeExpression::TYPE_OR) {
            $where->addMultiple($args);
        } else {
            array_unshift($args, $where);
            $where = new CompositeExpression(CompositeExpression::TYPE_OR, $args);
        }

        return $this->add('where', $where, true);
    }


    public function optionalWhere($predicates)
    {
        if (!(func_num_args() == 1 && $predicates instanceof CompositeExpression)) {
            $predicates = new CompositeExpression(CompositeExpression::TYPE_AND, func_get_args());
        }

        return $this->add('optionalWhere', $predicates, false);
    }

    public function andOptionalWhere($optionalWhere)
    {
        $args = func_get_args();
        $optionalWhere = $this->getCypherPart('optionalWhere');

        if ($optionalWhere instanceof CompositeExpression && $optionalWhere->getType() === CompositeExpression::TYPE_AND) {
            $optionalWhere->addMultiple($args);
        } else {
            array_unshift($args, $optionalWhere);
            $optionalWhere = new CompositeExpression(CompositeExpression::TYPE_AND, $args);
        }

        return $this->add('optionalWhere', $optionalWhere, true);
    }

    public function orOptionalWhere($optionalWhere)
    {
        $args = func_get_args();
        $optionalWhere = $this->getCypherPart('optionalWhere');

        if ($optionalWhere instanceof CompositeExpression && $optionalWhere->getType() === CompositeExpression::TYPE_OR) {
            $optionalWhere->addMultiple($args);
        } else {
            array_unshift($args, $optionalWhere);
            $optionalWhere = new CompositeExpression(CompositeExpression::TYPE_OR, $args);
        }

        return $this->add('optionalWhere', $optionalWhere, true);
    }

    public function returning($return)
    {
        return $this->add('return', $return);
    }

    public function add($cypherPartName, $cypherPart, $append = false)
    {
//        $isArray = is_array($cypherPart);
//        $isMultiple = is_array($this->cypherParts[$cypherPartName]);
//
//        if ($isMultiple && !$isArray) {
//            $cypherPart = [$cypherPart];
//        }
        $this->state = self::STATE_DIRTY;

        if ($append && is_array($cypherPart)) {
            foreach ($cypherPart as $part) {
                $this->cypherParts[$cypherPartName][] = $part;
            }

            return $this;
        } else {
            $this->cypherParts[$cypherPartName] = $cypherPart;

            return $this;
        }
    }

    public function getCypherPart($cypherPartName)
    {
        return $this->cypherParts[$cypherPartName];
    }

    public function getCypherParts()
    {
        return $this->cypherParts;
    }

    public function getSkip()
    {
        return $this->skip;
    }

    public function setSkip($skip)
    {
        $this->state = self::STATE_DIRTY;
        $this->skip = $skip;

        return $this;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function setLimit($limit)
    {
        $this->state = self::STATE_DIRTY;
        $this->limit = $limit;

        return $this;
    }

    public function getCypher()
    {
        if ($this->cypher !== null && $this->state === self::STATE_CLEAN) {
            return $this->cypher;
        }

        switch ($this->type) {
            case self::CREATE:
            case self::MERGE:
                $cypher = $this->getCypherForCreate();
                break;

            default:
                $cypher = $this->getCypherForMatch();
                break;
        }

        $this->state = self::STATE_CLEAN;
        $this->cypher = $cypher;

        return $cypher;
    }

    private function getCypherForMatch()
    {
        $appendMerge = function ($merge) { return 'MERGE ' . $merge; };

        $cypher = 'MATCH ' . implode(',' . PHP_EOL, $this->cypherParts['match']);
        $cypher .= ($this->cypherParts['where'] !== null ? PHP_EOL . 'WHERE ' . ((string) $this->cypherParts['where']) : '')
            . ($this->cypherParts['optionalMatch'] ? PHP_EOL . 'OPTIONAL MATCH ' . implode(',' . PHP_EOL, $this->cypherParts['optionalMatch']) : '')
            . ($this->cypherParts['optionalWhere'] !== null ? PHP_EOL . 'WHERE ' . ((string) $this->cypherParts['optionalWhere']) : '')
            . ($this->cypherParts['with'] ? PHP_EOL . 'WITH ' . $this->cypherParts['with'] : '')
            . ($this->cypherParts['create'] ? PHP_EOL .  'CREATE ' . implode(',' . PHP_EOL, $this->cypherParts['create']) : '')
            . ($this->cypherParts['merge'] ? PHP_EOL . implode(PHP_EOL, array_map($appendMerge, $this->cypherParts['merge'])) : '')
            . ($this->cypherParts['return'] ? PHP_EOL . 'RETURN ' . $this->cypherParts['return'] : '')
            . ($this->cypherParts['orderBy'] ? PHP_EOL . 'ORDER BY ' . implode(', ', $this->cypherParts['orderBy']) : '')
            . ($this->skip ? PHP_EOL . 'SKIP ' . $this->skip : '')
            . ($this->limit ? PHP_EOL . 'LIMIT ' . $this->limit : '');

        return $cypher;
    }

    private function getCypherForCreate()
    {
        // todo incomplete
        return '';
    }
}