<?php

namespace NijmegenSync\DataSource\Geoserver\Harvesting\WMS;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use NijmegenSync\DataSource\Geoserver\Harvesting\IGeoserverHarvester;
use NijmegenSync\DataSource\Harvesting\DataSourceUnavailableHarvestingException;

class WMSGeoserverHarvester implements IGeoserverHarvester
{
    /** @var string */
    protected $base_url;

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->base_url;
    }

    /**
     * @param string $base_url
     */
    public function setBaseUrl(string $base_url): void
    {
        $this->base_url = $base_url;
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

            \var_dump(\count($parsable_response->findLayers()));
        } catch (GuzzleException $e) {
            throw new DataSourceUnavailableHarvestingException($e->getMessage());
        }

        return $harvest;
    }
}
