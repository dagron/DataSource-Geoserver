<?php

namespace NijmegenSync\DataSource\Geoserver\Harvesting;

use NijmegenSync\Contracts\IAuthenticationDetails;
use NijmegenSync\DataSource\Geoserver\Harvesting\WFS\WFSGeoserverHarvester;
use NijmegenSync\DataSource\Geoserver\Harvesting\WMS\WMSGeoserverHarvester;
use NijmegenSync\DataSource\Harvesting\IDataSourceHarvester;

/**
 * Class GeoserverDataSourceHarvester.
 *
 * Performs the actual harvesting of the Nijmegen geoserver. It only harvests the publicly available
 * datasets, as such it requires no authentication details for performing its tasks.
 */
class GeoserverDataSourceHarvester implements IDataSourceHarvester
{
    /** @var string[] */
    protected $base_uri;

    /** @var string */
    protected $layers_uri;

    /** @var array */
    protected $wms_resource_resolutions;

    /**
     * {@inheritdoc}
     *
     * The GeoserverDataSourceHarvester requires no AuthenticationDetails.
     */
    public function requiresAuthenticationDetails(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * No operation is performed as the GeoserverDataSourceHarvester requires no
     * AuthenticationDetails.
     */
    public function setAuthenticationDetails(IAuthenticationDetails $details): void
    {
        // Geoserver harvester requires no AuthenticationDetails, so we ignore any that are given.
    }

    /**
     * Setter for the base_uri property.
     *
     * @param string[] $uris The uri to set
     */
    public function setBaseURI(array $uris): void
    {
        $this->base_uri = $uris;
    }

    /**
     * Getter for the base_uri property, may return null.
     *
     * @return string[] The base_uri value
     */
    public function getBaseUri(): array
    {
        return $this->base_uri;
    }

    /**
     * Setter for the layers_uri property.
     *
     * @param string $layers_uri The value to set
     */
    public function setLayersURI(string $layers_uri): void
    {
        $this->layers_uri = $layers_uri;
    }

    /**
     * Getter for the layers property, may return an empty array.
     *
     * @return string The layers property
     */
    public function getLayersURI(): string
    {
        return $this->layers_uri;
    }

    /**
     * @return array
     */
    public function getWmsResourceResolutions(): array
    {
        return $this->wms_resource_resolutions;
    }

    /**
     * @param array $wms_resource_resolutions
     */
    public function setWmsResourceResolutions(array $wms_resource_resolutions): void
    {
        $this->wms_resource_resolutions = $wms_resource_resolutions;
    }

    /**
     * {@inheritdoc}
     */
    public function harvest(): array
    {
        $wfs_harvester = new WFSGeoserverHarvester();
        $wfs_harvester->setBaseUrl($this->base_uri['WFS']);
        $wfs_harvester->setLayersUri($this->layers_uri);

        $wms_harvester = new WMSGeoserverHarvester();
        $wms_harvester->setBaseURL($this->base_uri['WMS']);
        $wms_harvester->setResourceResolutions($this->wms_resource_resolutions);

        return \array_merge(
            $wfs_harvester->harvest(),
            $wms_harvester->harvest()
        );
    }
}
