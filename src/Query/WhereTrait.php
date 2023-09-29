<?php
declare(strict_types=1);

namespace MDO\Query;

use MDO\Dto\Query\Where;

trait WhereTrait
{
    /**
     * @var Where[]
     */
    private array $wheres = [];

    /**
     * @return Where[]
     */
    public function getWheres(): array
    {
        return $this->wheres;
    }

    /**
     * @param Where[] $wheres
     */
    public function setWheres(array $wheres): self
    {
        $this->wheres = $wheres;

        return $this;
    }

    public function addWhere(Where $where): self
    {
        $this->wheres[] = $where;

        return $this;
    }

    protected function getWhereString(): string
    {
        if (count($this->wheres) === 0) {
            return '';
        }

        $whereConditions = array_map(
            static fn (Where $where): string => $where->getCondition(),
            $this->wheres,
        );

        return sprintf('(%s)', implode(') AND (', $whereConditions));
    }

    public function getParameters(): array
    {
        return array_merge(array_map(
            static fn (Where $where): array => $where->getParameters(),
            $this->getWheres(),
        ));
    }
}
