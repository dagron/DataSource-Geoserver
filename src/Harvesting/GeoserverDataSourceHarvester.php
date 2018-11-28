<?php

namespace NijmegenSync\DataSource\Geoserver\Harvesting;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use NijmegenSync\Contracts\IAuthenticationDetails;
use NijmegenSync\DataSource\Harvesting\DataSourceUnavailableHarvestingException;
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
     * {@inheritdoc}
     */
    public function harvest(): array
    {
        $client  = new Client(['base_uri' => $this->base_uri]);
        $harvest = [];

        try {
            $request = $client->request(
                'GET',
                '/geoservices/extern_kaartviewer/wfs?request=GetCapabilities',
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
                $data['title']               = \str_replace('_', ' ', $entity->findTitle());
                $data['description']         = $entity->findAbstract();
                $data['contact_point_email'] = $parsable_response->findContactEmail();
                $data['contact_point_name']  = \sprintf(
                    '%s, %s',
                    $parsable_response->findContactName(),
                    $parsable_response->findContactOrganization()
                );
                $data['access_rights'] = $parsable_response->findAccessRights();
                $data['keyword'][]     = \array_merge(
                    $entity->findGlobalKeywords(),
                    $entity->findKeywords()
                );

                foreach ($parsable_response->findSupportedOutputTypes() as $output_type) {
                    $resource                = [];
                    $resource['title']       = $output_type;
                    $resource['description'] = $output_type;
                    $resource['format']      = $output_type;
                    $resource['mediaType']   = $output_type;
                    $resource['url']         = \sprintf(
                        '%s/wfs?request=GetFeature&typeName=%s',
                        $this->base_uri, $entity->findTitle()
                    );
                    $resource['download_url'][] = \sprintf(
                        '%s/wfs?request=GetFeature&typeName=%s&outputFormat=%s',
                        $this->base_uri, $entity->findTitle(), $output_type
                    );
                    $resource['rights'] = $parsable_response->findAccessRights();

                    $data['resources'][] = $resource;
                }

                $harvest_result = new HarvestResult();
                $harvest_result->setResult($data);

                $harvest[] = $harvest_result;
            }

            return $harvest;
        } catch (GuzzleException $e) {
            throw new DataSourceUnavailableHarvestingException($e);
        }
    }
}
