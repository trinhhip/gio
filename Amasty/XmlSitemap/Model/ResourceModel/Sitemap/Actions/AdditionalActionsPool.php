<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\ResourceModel\Sitemap\Actions;

class AdditionalActionsPool implements \IteratorAggregate
{
    const SORT_ORDER = 'sortOrder';
    const ACTION = 'action';

    /**
     * @var array[]
     *
     * @example [
     *      'save' => [
     *          [
     *              'sortOrder' => 12,
     *              'action' => $action
     *          ]
     *      ]
     * ]
     */
    private $actions;

    public function __construct(
        $actions = []
    ) {
        $this->actions = $this->sortActions($actions);
    }

    private function sortActions($actions): array
    {
        usort($actions, function (array $configA, array $configB) {
            $sortOrderA = $configA[self::SORT_ORDER] ?? 0;
            $sortOrderB = $configB[self::SORT_ORDER] ?? 0;

            return $sortOrderA <=> $sortOrderB;
        });

        return $actions;
    }

    public function getIterator(): iterable
    {
        $actions = [];

        foreach ($this->actions as $actionConfig) {
            $action = $actionConfig[self::ACTION] ?? null;
            $actions[] = $action;
        }

        return new \ArrayIterator($actions);
    }
}
