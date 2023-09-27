<?php
declare(strict_types=1);

namespace MDO\Dto\Query;

use MDO\Dto\Query;

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
        $joinsString = '';

        foreach ($this->joins as $join) {
            $joinsString .= sprintf(
                '%s JOIN `%s` ON %s',
                $join->getType()->value,
                $join->getTable()->getTableName(),
                $join->getOn(),
            );
        }

        return $joinsString;
    }
}