<?php
declare(strict_types=1);

namespace MDO\Query;

use MDO\Enum\UnionFunction;

class UnionQuery implements QueryInterface
{
    use WithTrait;
    use LimitTrait;
    use OrderByTrait;

    /**
     * @param SelectQuery[] $unions
     */
    public function __construct(
        private array $unions = [],
        private UnionFunction $unionFunction = UnionFunction::UNION_ALL,
    ) {
    }

    public function getUnions(): array
    {
        return $this->unions;
    }

    public function setUnions(array $unions): self
    {
        $this->unions = $unions;

        return $this;
    }

    public function getUnionFunction(): UnionFunction
    {
        return $this->unionFunction;
    }

    public function setUnionFunction(UnionFunction $unionFunction): UnionQuery
    {
        $this->unionFunction = $unionFunction;

        return $this;
    }

    public function getQuery(): string
    {
        $withString = $this->getWithString();
        $orderString = $this->getOrderString();
        $limitString = $this->getLimitString();
        $unions = [];

        foreach ($this->getUnions() as $union) {
            $unions[] = sprintf('(%s)', $union->getQuery());
        }

        return trim(sprintf(
            '%s (%s)%s%s',
            $withString,
            implode(' ' . $this->getUnionFunction()->value . ' ', $unions),
            $orderString === '' ? '' : ' ORDER BY ' . $orderString,
            $limitString === '' ? '' : ' LIMIT ' . $limitString,
        ));
    }

    public function getParameters(): array
    {
        $parameters = [];

        foreach ($this->getUnions() as $union) {
            $parameters = array_merge($parameters, $union->getParameters());
        }

        return $parameters;
    }
}
