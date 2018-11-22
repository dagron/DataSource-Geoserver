<?php

namespace NijmegenSync\DataSource\Geoserver\Harvester;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use NijmegenSync\Contracts\IAuthenticationDetails;
use NijmegenSync\DataSource\Harvesting\DataSourceUnavailableHarvestingException;
use NijmegenSync\DataSource\Harvesting\HarvestResult;
use NijmegenSync\DataSource\Harvesting\IDataSourceHarvester;

/**
 * Class GeoserverDataSourceHarvester.
 */
class GeoserverDataSourceHarvester implements IDataSourceHarvester
{
    /** @var string */
    protected $base_uri;

    /**
     * {@inheritdoc}
     */
    public function requiresAuthenticationDetails(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function harvest(): array
    {
        $client  = new Client(['base_uri' => $this->base_uri]);
        $harvest = [];

        try {
            $request = $client->request(
                'GET',
                'extern_kaartviewer/wfs?request=GetCapabilities',
                [
                    'accept' => 'application/xml',
                ]
            );

            if (200 !== $request->getStatusCode()) {
                throw new DataSourceUnavailableHarvestingException(
                    \sprintf('datasource responded with HTTP statuscode %s', $request->getStatusCode())
                );
            }

            $response_as_xml = $this->createSimpleXMLElementFromResponse($request->getBody());

            foreach ($response_as_xml->FeatureTypeList->FeatureType as $crop) {
                $data                       = [];
                $data['title']              = \str_replace('_', ' ', $crop->Title);
                $data['contact_point_name'] = \sprintf(
                    '%s, %s',
                    \strval($response_as_xml->xpath('ows:ServiceProvider/ows:ServiceContact/ows:IndividualName')[0]),
                    \strval($response_as_xml->xpath('ows:ServiceProvider/ows:ProviderName')[0])
                );

                foreach ($crop->xpath('ows:Keywords/ows:Keyword') as $keyword) {
                    $data['keyword'][] = \strval($keyword);
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

    private function createSimpleXMLElementFromResponse(string $response): \SimpleXMLElement
    {
        $response_as_xml = new \SimpleXMLElement($response);
        $response_as_xml->registerXPathNamespace('xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $response_as_xml->registerXPathNamespace('wfs', 'http://www.opengis.net/wfs/2.0');
        $response_as_xml->registerXPathNamespace('ows', 'http://www.opengis.net/ows/1.1');
        $response_as_xml->registerXPathNamespace('gml', 'http://www.opengis.net/gml/3.2');
        $response_as_xml->registerXPathNamespace('fes', 'http://www.opengis.net/fes/2.0');
        $response_as_xml->registerXPathNamespace('xlink', 'http://www.w3.org/1999/xlink');
        $response_as_xml->registerXPathNamespace('xs', 'http://www.w3.org/2001/XMLSchema');
        $response_as_xml->registerXPathNamespace('xml', 'http://www.w3.org/XML/1998/namespace');

        return $response_as_xml;
    }
}
