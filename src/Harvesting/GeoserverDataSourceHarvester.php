<?php

namespace NijmegenSync\DataSource\Geoserver\Harvesting;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use NijmegenSync\Contracts\IAuthenticationDetails;
use NijmegenSync\DataSource\Harvesting\DataSourceUnavailableHarvestingException;
use NijmegenSync\DataSource\Harvesting\HarvestingException;
use NijmegenSync\DataSource\Harvesting\HarvestResult;
use NijmegenSync\DataSource\Harvesting\IDataSourceHarvester;

/**
 * Class GeoserverDataSourceHarvester.
 *
 * Performs the actual harvesting of the Nijmegen geoserver. It only harvests the publicly available
 * datasets, as such it requires no authentication details for performing its tasks.
 */
class GeoserverDataSourceHarvester implements IDataSourceHarvester
{
    /** @var string */
    protected $base_uri;

    /** @var string */
    protected $layers_uri;

    /** @var string[] */
    protected $layers;

    /**
     * GeoserverDataSourceHarvester constructor.
     */
    public function __construct()
    {
        $this->layers = [];
    }

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
     * @param string $uri The uri to set
     */
    public function setBaseURI(string $uri): void
    {
        $this->base_uri = $uri;
    }

    /**
     * Getter for the base_uri property, may return null.
     *
     * @return null|string The base_uri value
     */
    public function getBaseUri(): ?string
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
     * {@inheritdoc}
     */
    public function harvest(): array
    {
        $this->loadLayers();
        $client  = new Client(['base_uri' => $this->base_uri]);
        $harvest = [];

        try {
            foreach ($this->layers as $layer) {
                $request = $client->request(
                    'GET',
                    \sprintf('/geoservices/%s/wfs?service=WFS&request=GetCapabilities', $layer),
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

                $parsable_response = new GeoserverXMLParser(new \SimpleXMLElement($request->getBody()));

                foreach ($parsable_response->getAllEntities() as $entity) {
                    $data                        = [];
                    $data['geoserver_layers']    = $this->layers;
                    $data['identifier']          = \sprintf(
                        '%s/geoserver/%s/wfs?service=WFS&request=GetFeature&typeName=%s',
                        $this->base_uri, $layer, $entity->findTitle()
                    );
                    $data['title']               = \str_replace('_', ' ', $entity->findTitle());
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
                        $entity->findGlobalKeywords(),
                        $entity->findKeywords()
                    );

                    foreach ($parsable_response->findSupportedOutputTypes() as $output_type) {
                        $resource                = [];
                        $resource['title']       = $output_type;
                        $resource['description'] = $output_type;
                        $resource['format']      = $output_type;
                        $resource['mediaType']   = $output_type;
                        $resource['accessURL']   = \sprintf(
                            '%s/geoserver/%s/wfs?service=WFS&request=GetFeature&typeName=%s',
                            $this->base_uri, $layer, $entity->findTitle()
                        );
                        $resource['downloadURL'][] = \sprintf(
                            '%s/geoserver/%s/wfs?service=WFS&request=GetFeature&typeName=%s&outputFormat=%s',
                            $this->base_uri, $layer, $entity->findTitle(), $output_type
                        );
                        $resource['rights'] = $parsable_response->findAccessRights();

                        $data['resources'][] = $resource;
                    }

                    $harvest_result = new HarvestResult();
                    $harvest_result->setResult($data);

                    $harvest[] = $harvest_result;
                }
            }

            return $harvest;
        } catch (GuzzleException $e) {
            throw new DataSourceUnavailableHarvestingException($e);
        }
    }

    /**
     * Loads the defined layers from the Drupal taxonomy on the Nijmegen portal.
     *
     * @throws HarvestingException Thrown if the layers_uri cannot be reached
     */
    private function loadLayers(): void
    {
        try {
            $client  = new Client();
            $request = $client->request('GET', $this->layers_uri);

            $response_as_xml = new \DOMDocument();
            @$response_as_xml->loadHTML($request->getBody());

            $traversable_xml = new \DOMXPath($response_as_xml);
            $workspaces      = $traversable_xml->query('//li[@class="geoserver-workspace"]/a');

            foreach ($workspaces as $workspace) {
                /* @var $workspace \DOMNode */
                $this->layers[] = $workspace->nodeValue;
            }
        } catch (GuzzleException $e) {
            throw new HarvestingException('unable to determine layers to harvest');
        }
    }
}
