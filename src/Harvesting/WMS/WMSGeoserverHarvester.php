<?php

namespace NijmegenSync\DataSource\Geoserver\Harvesting\WMS;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use NijmegenSync\DataSource\Geoserver\Harvesting\IGeoserverHarvester;
use NijmegenSync\DataSource\Harvesting\DataSourceUnavailableHarvestingException;
use NijmegenSync\DataSource\Harvesting\HarvestResult;

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

            foreach ($parsable_response->getAllLayers() as $layer) {
                $data = [
                    'geoserver_service' => 'WMS',
                ];
                $data['identifier'] = \sprintf(
                    '%s/geoservices/wms/extern?service=WMS&version=1.3.0&request=GetMap&layers=%s&styles=default&CRS=EPSG:28992&bbox=176000,419000,193000,436500&width=1000&height=1000&format=image/png',
                    $this->base_url, $layer->findTitle()
                );
                $data['title']       = $layer->findTitle();
                $data['description'] = '
                    [Dataset delen: ja]
                    [Omschrijving template: WMS]
                    [Thema: Natuur en milieu]
                ';
                $data['modificationDate']    = (new \DateTime('now', new \DateTimeZone('Europe/Amsterdam')))
                    ->format('Y-m-d\TH:i:s');
                $data['contact_point_name'] = \sprintf(
                    '%s, %s',
                    $parsable_response->findContactPerson(),
                    $parsable_response->findContactOrganization()
                );

                $harvest_result = new HarvestResult();
                $harvest_result->setResult($data);

                $harvest[] = $harvest_result;
            }
        } catch (GuzzleException $e) {
            throw new DataSourceUnavailableHarvestingException($e->getMessage());
        }

        return $harvest;
    }
}