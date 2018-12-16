<?php

namespace NijmegenSync\DataSource\Geoserver\Harvesting\WFS;

use NijmegenSync\DataSource\Geoserver\Harvesting\XMLParser;

/**
 * Class WFSResponseXMLParser.
 *
 * Represents the response of the Geoserver application when requesting the GetCapabilities.
 */
class WFSResponseXMLParser extends XMLParser
{
    /**
     * GeoserverXMLParser constructor.
     *
     * @param \SimpleXMLElement $geoserver_response The XML object created from the response
     */
    public function __construct(\SimpleXMLElement $geoserver_response)
    {
        $this->xml = $geoserver_response;

        $this->registerNamespaces();
    }

    /**
     * Finds all FeatureTypes that exist within the Nijmegen Geoserver.
     *
     * @return WFSEntityXMLParser[] Parsers for the entities inside the WFS server
     */
    public function getAllEntities(): array
    {
        $features = $this->xml->xpath('//FeatureTypeList/FeatureType');
        $entities = [];

        foreach ($features as $feature) {
            $entities[] = new WFSEntityXMLParser($feature);
        }

        return $entities;
    }

    /**
     * Finds and returns the name of the contactPoint of the Nijmegen geoserver, will return an
     * empty string if no name is found.
     *
     * @return string The name of the contactPoint
     */
    public function findContactName(): string
    {
        return $this->querySingleValueString(
            'ows:ServiceProvider/ows:ServiceContact/ows:IndividualName'
        );
    }

    /**
     * Finds and returns the organization of the contactPoint of the Nijmegen geoserver, will return
     * an empty string if no organization is found.
     *
     * @return string The organization of the contactPoint
     */
    public function findContactOrganization(): string
    {
        return $this->querySingleValueString(
            'ows:ServiceProvider/ows:ProviderName'
        );
    }

    /**
     * Finds and returns the email of the contactPoint of the Nijmegen geoserver, will return an
     * empty string if no email is found.
     *
     * @return string The email of the contactPoint
     */
    public function findContactEmail(): string
    {
        return $this->querySingleValueString(
            'ows:ServiceProvider/ows:ServiceContact/ows:ContactInfo/ows:Address/ows:ElectronicMailAddress'
        );
    }

    /**
     * Finds and returns the keywords describing the Nijmegen geoserver.
     *
     * @return string[] The keywords describing the Nijmegen geoserver
     */
    public function findKeywords(): array
    {
        $query_results = $this->xml->xpath(
            'ows:ServiceIdentification/ows:Keywords/ows:Keyword'
        );
        $keywords = [];

        if (0 == \count($query_results)) {
            return $keywords;
        }

        foreach ($query_results as $result) {
            $keywords = \strval($result);
        }

        return $keywords;
    }

    /**
     * Finds and returns the access rights of the Nijmegen geoserver, will return an empty string if
     * no rights statement is found.
     *
     * @return string The access rights of the Nijmegen geoserver
     */
    public function findAccessRights(): string
    {
        return $this->querySingleValueString(
            'ows:ServiceIdentification/ows:AccessConstraints'
        );
    }

    /**
     * Finds and returns all the supported output formats of the Nijmegen geoserver.
     *
     * @return string[] The supported output formats
     */
    public function findSupportedOutputTypes(): array
    {
        $elements = $this->xml->xpath(
            '//ows:OperationsMetadata/ows:Operation[@name="GetFeature"]/ows:Parameter[@name="outputFormat"]/ows:Value'
        );
        $formats = [];

        if (0 == \count($elements)) {
            return $formats;
        }

        foreach ($elements as $output_format) {
            $formats[] = \strval($output_format);
        }

        return $formats;
    }

    /**
     * Registers the namespaces of the Nijmegen geoserver for proper parsing of the XML body.
     */
    private function registerNamespaces(): void
    {
        $this->xml->registerXPathNamespace('xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $this->xml->registerXPathNamespace('wfs', 'http://www.opengis.net/wfs/2.0');
        $this->xml->registerXPathNamespace('ows', 'http://www.opengis.net/ows/1.1');
        $this->xml->registerXPathNamespace('gml', 'http://www.opengis.net/gml/3.2');
        $this->xml->registerXPathNamespace('fes', 'http://www.opengis.net/fes/2.0');
        $this->xml->registerXPathNamespace('xlink', 'http://www.w3.org/1999/xlink');
        $this->xml->registerXPathNamespace('xs', 'http://www.w3.org/2001/XMLSchema');
        $this->xml->registerXPathNamespace('xml', 'http://www.w3.org/XML/1998/namespace');
    }
}
