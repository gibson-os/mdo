<?php
declare(strict_types=1);

namespace MDO\Dto\Query;

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
        return sprintf('(%s)', implode(') AND (', $this->wheres));
    }

    public function getParameters(): array
    {
        return array_merge(array_map(
            static fn (Where $where): array => $where->getParameters(),
            $this->getWheres()
        ));
    }
}