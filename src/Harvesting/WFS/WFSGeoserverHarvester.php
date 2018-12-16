<?php

namespace NijmegenSync\DataSource\Geoserver\Harvesting\WFS;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use NijmegenSync\DataSource\Geoserver\Harvesting\IGeoserverHarvester;
use NijmegenSync\DataSource\Harvesting\DataSourceUnavailableHarvestingException;
use NijmegenSync\DataSource\Harvesting\HarvestingException;
use NijmegenSync\DataSource\Harvesting\HarvestResult;

/**
 * Class WFSGeoserverHarvester.
 *
 * This implementation allows for the harvesting of datasets from a WFS endpoint of a Geoserver
 * application. It extracts metadata from the Geoserver by analyzing the contents of the
 * GetCapabilities API call.
 */
class WFSGeoserverHarvester implements IGeoserverHarvester
{
    /** @var string */
    protected $base_url;

    /** @var string */
    protected $layers_uri;

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->base_url;
    }

    /**
     * @return string
     */
    public function getLayersUri(): string
    {
        return $this->layers_uri;
    }

    /**
     * @param string $base_url
     */
    public function setBaseUrl(string $base_url): void
    {
        $this->base_url = $base_url;
    }

    /**
     * @param string $layers_uri
     */
    public function setLayersUri(string $layers_uri): void
    {
        $this->layers_uri = $layers_uri;
    }

    /**
     * {@inheritdoc}
     */
    public function harvest(): array
    {
        $layers  = $this->loadLayers();
        $client  = new Client(['base_uri' => $this->base_url]);
        $harvest = [];

        try {
            foreach ($layers as $layer) {
                $request = $client->request(
                    'GET',
                    \sprintf(
                        '/geoservices/%s/ows?service=WFS&version=1.1.0&request=GetCapabilities',
                        $layer
                    ),
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

                $parsable_response = new WFSResponseXMLParser(
                    new \SimpleXMLElement($request->getBody())
                );

                foreach ($parsable_response->getAllEntities() as $entity) {
                    $data = [
                        'geoserver_service' => 'WFS',
                        'geoserver_layers'  => $layers,
                        'geoserver_layer'   => $layer,
                    ];

                    $data['identifier']          = \sprintf(
                        '%s/geoservices/%s/ows?service=WFS&version=1.1.0&request=GetFeature&typeName=%s',
                        $this->base_url, $layer, $entity->findName()
                    );
                    $data['title']               = \ucfirst(\strtolower(\str_replace('_', ' ', $entity->findTitle())));
                    $data['description']         = $entity->findAbstract();
                    $data['modificationDate']    = (new \DateTime('now', new \DateTimeZone('Europe/Amsterdam')))
                        ->format('Y-m-d\TH:i:s');
                    $data['contact_point_email'] = $parsable_response->findContactEmail();
                    $data['contact_point_name']  = \sprintf(
                        '%s, %s',
                        $parsable_response->findContactName(),
                        $parsable_response->findContactOrganization()
                    );
                    $data['accessRights'] = $parsable_response->findAccessRights();
                    $data['keyword']      = \array_merge(
                        $parsable_response->findKeywords(),
                        $entity->findKeywords()
                    );

                    foreach ($parsable_response->findSupportedOutputTypes() as $output_type) {
                        $resource                = [];
                        $resource['title']       = $output_type;
                        $resource['description'] = $output_type;
                        $resource['accessURL']   = \sprintf(
                            '%s/geoservices/%s/ows?service=WFS&version=1.1.0&request=GetFeature&typeName=%s&outputFormat=%s',
                            $this->base_url, $layer, $entity->findName(), \urlencode($output_type)
                        );
                        $resource['format']      = $output_type;
                        $resource['mediaType']   = $output_type;
                        $resource['rights']      = $parsable_response->findAccessRights();

                        $data['resources'][] = $resource;
                    }

                    $harvest_result = new HarvestResult();
                    $harvest_result->setResult($data);

                    $harvest[] = $harvest_result;
                }
            }
        } catch (GuzzleException $e) {
            throw new DataSourceUnavailableHarvestingException($e->getMessage());
        }

        return $harvest;
    }

    /**
     * Loads the defined layers from the Drupal taxonomy on the Nijmegen portal.
     *
     * @throws HarvestingException Thrown if the layers_uri cannot be reached
     *
     * @return string[] The retrieved layers to harvest
     */
    private function loadLayers(): array
    {
        try {
            $client  = new Client();
            $request = $client->request('GET', $this->layers_uri);

            $response_as_xml = new \DOMDocument();
            @$response_as_xml->loadHTML($request->getBody());

            $traversable_xml = new \DOMXPath($response_as_xml);
            $workspaces      = $traversable_xml->query('//li[@class="geoserver-workspace"]/a');
            $layers          = [];

            foreach ($workspaces as $workspace) {
                /* @var $workspace \DOMNode */
                $layers[] = $workspace->nodeValue;
            }

            return $layers;
        } catch (GuzzleException $e) {
            throw new HarvestingException('unable to determine layers to harvest');
        }
    }
}
