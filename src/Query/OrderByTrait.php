<?php
declare(strict_types=1);

namespace MDO\Query;

use MDO\Enum\OrderDirection;

trait OrderByTrait
{
    /**
     * @var array<string, OrderDirection>
     */
    private array $orders = [];

    /**
     * @return array<string, OrderDirection>
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    public function setOrder(string $fieldName, OrderDirection $direction = OrderDirection::ASC): self
    {
        $this->orders[$fieldName] = $direction;

        return $this;
    }

    /**
     * @param array<string, OrderDirection> $orders
     */
    public function setOrders(array $orders): self
    {
        $this->orders = $orders;

        return $this;
    }

    protected function getOrderString(): string
    {
        $orders = [];

        foreach ($this->orders as $fieldName => $direction) {
            $orders[] = sprintf('%s %s', $fieldName, $direction->value);
        }

        return implode(', ', $orders);
    }
}
