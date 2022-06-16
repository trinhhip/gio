<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Indexer\Template\Action;

use Amasty\AltTagGenerator\Model\ResourceModel\TemplateIndex\TableWorker;
use Exception;

class ExecuteFull
{
    /**
     * @var DoReindex
     */
    private $doReindex;

    /**
     * @var TableWorker
     */
    private $tableWorker;

    public function __construct(DoReindex $doReindex, TableWorker $tableWorker)
    {
        $this->doReindex = $doReindex;
        $this->tableWorker = $tableWorker;
    }

    /**
     * @throws Exception
     */
    public function execute(): void
    {
        $this->tableWorker->clearReplica();
        $this->tableWorker->createTemporaryTable();

        $this->doReindex->execute();

        $this->tableWorker->syncDataFull();
        $this->tableWorker->switchTables();
    }
}
