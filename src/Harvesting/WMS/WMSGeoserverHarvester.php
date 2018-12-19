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
                    '%s/geoservices/wms/extern?service=WMS&version=1.3.0&request=GetMap&layers=%s',
                    $this->base_url, $layer->findName()
                );
                $data['title']       = $layer->findTitle();
                $data['description'] = '
                    [Delen dataset: ja]
                    [Titel dataset: ' . $layer->findTitle() . ']
                    [Omschrijving template: WMS]
                    [Thema dataset: Natuur en milieu]
                ';
                $data['modificationDate']    = (new \DateTime('now', new \DateTimeZone('Europe/Amsterdam')))
                    ->format('Y-m-d\TH:i:s');
                $data['contact_point_name'] = \sprintf(
                    '%s, %s',
                    $parsable_response->findContactPerson(),
                    $parsable_response->findContactOrganization()
                );

                $resource_types = [
                    [500, 500],
                    [1000, 1000],
                    [1500, 1500],
                    [2000, 2000],
                ];

                foreach ($resource_types as $resource_type) {
                    $bounding_box = $layer->findBoundingBox();

                    $resource                = [];
                    $resource['title']       = \sprintf('%sx%s', $resource_type[0], $resource_type[1]);
                    $resource['description'] = $resource['title'];
                    $resource['accessURL']   = \sprintf(
                        '%s/geoservices/wms/extern?service=WMS&version=1.3.0&request=GetMap&layers=%s&styles=default&CRS=EPSG:28992&bbox=%s&width=%s&height=%s&format=%s',
                        $this->base_url, $layer->findName(), $resource_type[0], $resource_type[1], $bounding_box, $parsable_response->findDesiredOutputFormat()
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
}
