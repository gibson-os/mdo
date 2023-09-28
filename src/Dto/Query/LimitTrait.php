<?php
declare(strict_types=1);

namespace MDO\Dto\Query;

trait LimitTrait
{
    private int $offset = 0;

    private int $rowCount = 0;

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function setOffset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    public function setRowCount(int $rowCount): self
    {
        $this->rowCount = $rowCount;

        return $this;
    }

    public function setLimit(int $rowCount = 0, int $offset = 0): self
    {
        $this->rowCount = $rowCount;
        $this->offset = $offset;

        return $this;
    }

    protected function getLimitString(): string
    {
        if ($this->offset > 0) {
            return sprintf('%d, %d', $this->offset, $this->rowCount);
        }

        if ($this->rowCount > 0) {
            return (string) $this->rowCount;
        }

        return '';
    }
}
