<?php

namespace NijmegenSync\DataSource\Geoserver\Harvesting;

use NijmegenSync\DataSource\Harvesting\HarvestingException;
use NijmegenSync\DataSource\Harvesting\HarvestResult;

interface IGeoserverHarvester
{
    /**
     * @throws HarvestingException
     *
     * @return HarvestResult[]
     */
    public function harvest(): array;
}
