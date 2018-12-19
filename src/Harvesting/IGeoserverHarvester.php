<?php

namespace NijmegenSync\DataSource\Geoserver\Harvesting;

use NijmegenSync\DataSource\Harvesting\HarvestingException;
use NijmegenSync\DataSource\Harvesting\HarvestResult;

/**
 * Interface IGeoserverHarvester.
 */
interface IGeoserverHarvester
{
    /**
     * Harvests a specific component of the Nijmegen geoserver.
     *
     * @throws HarvestingException thrown on any error while harvesting the DataSource
     *
     * @return HarvestResult[] The harvested datasets
     */
    public function harvest(): array;
}
