<?php

namespace NijmegenSync\DataSource\Geoserver\Harvester;

use NijmegenSync\Contracts\IAuthenticationDetails;
use NijmegenSync\DataSource\Harvesting\IDataSourceHarvester;

/**
 * Class GeoserverDataSourceHarvester.
 */
class GeoserverDataSourceHarvester implements IDataSourceHarvester
{
    /**
     * {@inheritdoc}
     */
    public function requiresAuthenticationDetails(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthenticationDetails(IAuthenticationDetails $details): void
    {
        // TODO: Implement setAuthenticationDetails() method.
    }

    /**
     * {@inheritdoc}
     */
    public function harvest(): array
    {
        // TODO: Implement harvest() method.
    }
}
