<?php
declare(strict_types=1);

namespace MDO\Query;

use MDO\Dto\Query\Join;

trait JoinTrait
{
    /**
     * @var Join[]
     */
    private array $joins = [];

    /**
     * @return Join[]
     */
    public function getJoins(): array
    {
        return $this->joins;
    }

    /**
     * @param Join[] $joins
     */
    public function setJoins(array $joins): self
    {
        $this->joins = $joins;

        return $this;
    }

    public function setJoin(Join $join): self
    {
        $this->joins[] = $join;

        return $this;
    }

    protected function getJoinsString(): string
    {
        $joins = [];

        foreach ($this->joins as $join) {
            $joins[] = sprintf(
                '%s JOIN `%s` `%s` ON %s',
                $join->getType()->value,
                $join->getTable()->getTableName(),
                $join->getAlias(),
                $join->getOn(),
            );
        }

        return implode(' ', $joins);
    }
}
