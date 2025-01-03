<?php
declare(strict_types=1);

namespace MDO\Query;

use MDO\Dto\Query\With;

trait WithTrait
{
    /**
     * @var With[]
     */
    private array $withs = [];

    /**
     * @return With[]
     */
    public function getWiths(): array
    {
        return $this->withs;
    }

    /**
     * @param With[] $withs
     */
    public function setWiths(array $withs): self
    {
        foreach ($withs as $with) {
            $this->setWith($with);
        }

        return $this;
    }

    public function setWith(With $with): self
    {
        $this->withs[$with->getName()] = $with;

        return $this;
    }

    public function getWithString(): string
    {
        if (count($this->withs) === 0) {
            return '';
        }

        $withs = [];

        foreach ($this->withs as $with) {
            $withs[] = sprintf('`%s` AS (%s)', $with->getName(), $with->getQuery()->getQuery());
        }

        return sprintf('WITH %s', implode(', ', $withs));
    }
}
