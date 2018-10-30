<?php

namespace GeoserverDataSource\Harvester;

use NijmegenSync\Contracts\Exceptions\AuthenticationDetailException;
use NijmegenSync\Contracts\IAuthenticationDetails;
use NijmegenSync\DataSource\Harvesting\HarvestingException;
use NijmegenSync\DataSource\Harvesting\HarvestResult;
use NijmegenSync\DataSource\Harvesting\IDataSourceHarvester;


/**
 * Class GeoserverDataSourceHarvester
 *
 * @package GeoserverDataSource\Harvester
 */
class GeoserverDataSourceHarvester implements IDataSourceHarvester {

    /** @var string */
    protected $data_overview_url;

    /** @var string */
    protected $data_retrieval_url;

    /**
     * Defines the URL behind which the geoserver will expose which data it contains.
     *
     * @param string $url The url to set
     */
    public function setDataOverviewURL(string $url): void
    {
        $this->data_overview_url = $url;
    }

    /**
     * Defines the URL pattern for retrieving specific data from the geoserver.
     *
     * @param string $url The URL pattern
     */
    public function setDataRetrievalURL(string $url): void
    {
        $this->data_retrieval_url = $url;
    }

    /**
     * Indicates whether or not the implementer requires authentication details to function.
     *
     * @return bool True or false depending on if the implementer requires the details
     */
    public function requiresAuthenticationDetails(): bool
    {
        return false;
    }

    /**
     * Provides the authentication details the implementer should use for its authentication
     * requirements.
     *
     * @param IAuthenticationDetails $details The authentication credentials
     * @throws AuthenticationDetailException Thrown when authentication details are missing
     * @throws AuthenticationDetailException Thrown when authentication details are wrong
     */
    public function setAuthenticationDetails(IAuthenticationDetails $details): void
    {
        # The Nijmegen geoserver requires no authentication.
        return;
    }

    /**
     * Harvests the DataSource and returns the harvest as a HarvestResults.
     *
     * @return HarvestResult[] The data and/or notices of the harvesting process
     * @throws HarvestingException Thrown when any error occurs while harvesting the DataSource
     */
    public function harvest(): array
    {
        throw new HarvestingException();
    }

}
