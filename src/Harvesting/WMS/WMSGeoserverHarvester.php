<?php

namespace NijmegenSync\DataSource\Geoserver\Harvesting\WMS;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use NijmegenSync\DataSource\Geoserver\Harvesting\IGeoserverHarvester;
use NijmegenSync\DataSource\Harvesting\DataSourceUnavailableHarvestingException;
use NijmegenSync\DataSource\Harvesting\HarvestResult;

/**
 * Class WMSGeoserverHarvester.
 */
class WMSGeoserverHarvester implements IGeoserverHarvester
{
    /** @var string */
    protected $base_url;

    /** @var array */
    protected $resource_resolutions;

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->base_url;
    }

    /**
     * @return array
     */
    public function getResourceResolutions(): array
    {
        return $this->resource_resolutions;
    }

    /**
     * @param string $base_url
     */
    public function setBaseUrl(string $base_url): void
    {
        $this->base_url = $base_url;
    }

    /**
     * @param array $resource_resolutions
     */
    public function setResourceResolutions(array $resource_resolutions): void
    {
        $this->resource_resolutions = $resource_resolutions;
    }

    /**
     * {@inheritdoc}
     */
    public function harvest(): array
    {
        $client  = new Client(['base_uri' => $this->base_url]);
        $harvest = [];

        try {
            $request = $client->request(
                'GET',
                '/geoservices/wms/extern?service=WMS&version=1.1.0&request=GetCapabilities',
                [
                    'accept' => 'application/xml',
                ]
            );

            if (200 !== $request->getStatusCode()) {
                throw new DataSourceUnavailableHarvestingException(
                    \sprintf(
                        'datasource responded with HTTP statuscode %s',
                        $request->getStatusCode()
                    )
                );
            }

            $parsable_response = new WMSResponseXMLParser(
                new \SimpleXMLElement($request->getBody())
            );

            foreach ($parsable_response->getAllLayers() as $layer) {
                $data = [
                    'geoserver_service' => 'WMS',
                ];
                $data['identifier'] = \sprintf(
                    '%s/geoservices/wms/extern?service=WMS&version=1.3.0&request=GetMap&layers=%s',
                    $this->base_url, \rawurlencode($layer->findName())
                );
                $data['title']            = $layer->findTitle();
                $data['description']      = $layer->findAbstract();
                $data['modificationDate'] = (new \DateTime('now', new \DateTimeZone('Europe/Amsterdam')))
                    ->format('Y-m-d\TH:i:s');

                foreach ($this->resource_resolutions as $resource_resolution) {
                    $bounding_box = $layer->findBoundingBox();

                    $resource                = [];
                    $resource['title']       = \sprintf('%sx%s', $resource_resolution[0], $resource_resolution[1]);
                    $resource['description'] = $resource['title'];
                    $resource['accessURL']   = \sprintf(
                        '%s/geoservices/wms/extern?service=WMS&version=1.3.0&request=GetMap&layers=%s&styles=default&transparent=true&CRS=EPSG:28992&bbox=%s&width=%s&height=%s&format=%s',
                        $this->base_url, \str_replace(' ', '%20', $layer->findName()), $bounding_box, $resource_resolution[0], $resource_resolution[1], $parsable_response->findDesiredOutputFormat()
                    );
                    $resource['format']    = $parsable_response->findDesiredOutputFormat();
                    $resource['mediaType'] = $parsable_response->findDesiredOutputFormat();
                    $resource['rights']    = $parsable_response->findAccessRights();

                    $data['resources'][] = $resource;
                }

                $harvest_result = new HarvestResult();
                $harvest_result->setResult($data);

                $harvest[] = $harvest_result;
            }
        } catch (GuzzleException $e) {
            throw new DataSourceUnavailableHarvestingException($e->getMessage());
        }

        return $harvest;
    }

    /**
     * Performs several cleanup actions which results in a presentable title.
     *
     * @param string $title The original title
     *
     * @return string The cleaned up title
     */
    protected function cleanupTitle(string $title): string
    {
        $title = \str_replace('_', ' ', $title);
        $title = \str_replace('.ecw', '', $title);
        $title = \str_replace('lufo', '', $title);
        $title = \str_replace('Luchtfoto', '', $title);
        $title = \str_replace('luchtfoto', '', $title);

        $title = \sprintf('Geografische weergave van Nijmegen %s', $title);

        return $title;
    }
}
