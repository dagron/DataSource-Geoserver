<?php

namespace NijmegenSync\DataSource\Geoserver\Harvesting;

/**
 * Class GeoserverXMLParser.
 *
 * Represents the, or part of the, XML response given by the Nijmegen Geoserver.
 */
class GeoserverXMLParser
{
    /** @var \SimpleXMLElement */
    protected $xml;

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
     * @return GeoserverXMLParser[] Localized GeoserverXMLParsers for the found FeatureTypes
     */
    public function getAllEntities(): array
    {
        $children = $this->xml->children();

        foreach ($children as $child) {
            if ('FeatureTypeList' == $child->getName()) {
                $features = $child->children();
                $entities = [];

                foreach ($features as $feature) {
                    $entities[] = new self($feature);
                }

                return $entities;
            }
        }

        return [];
    }

    /**
     * Finds and returns the title of a FeatureType, will return an empty string if nothing is found.
     *
     * @return string The title of the FeatureType
     */
    public function findTitle(): string
    {
        $children = $this->xml->children();

        foreach ($children as $child) {
            if ('Title' == $child->getName()) {
                return \strval($child);
            }
        }

        return '';
    }

    /**
     * Finds and returns the abstract of a FeatureType, will return an empty string if nothing is
     * found.
     *
     * @return string The abstract of the FeatureType
     */
    public function findAbstract(): string
    {
        $children = $this->xml->children();

        foreach ($children as $child) {
            if ('Abstract' == $child->getName()) {
                return \strval($child);
            }
        }

        return '';
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
     * Finds and returns the keywords describing the Nijmegen geoserver.
     *
     * @return string[] The keywords describing the Nijmegen geoserver
     */
    public function findGlobalKeywords(): array
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
     * Finds and returns the keywords describing a FeatureType of the Nijmegen geoserver.
     *
     * @return string[] The keywords describing the FeatureType
     */
    public function findKeywords(): array
    {
        $query_results = $this->xml->xpath(
            'ows:Keywords/ows:Keyword'
        );
        $keywords = [];

        if (0 == \count($query_results)) {
            return $keywords;
        }

        foreach ($query_results as $result) {
            $keywords[] = \strval($result);
        }

        return $keywords;
    }

    /**
     * Finds and returns all the supported output formats of the Nijmegen geoserver.
     *
     * @return string[] The supported output formats
     */
    public function findSupportedOutputTypes(): array
    {
        $elements = $this->xml->xpath(
            '//ows:OperationsMetadata/ows:Operation[@name="GetFeature"]/ows:Parameter[@name="outputFormat"]/ows:AllowedValues/ows:Value'
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
     * Performs a single-value query on the XML body. If more than one object is returned by the
     * query, the first one will be returned.
     *
     * When no results are found an empty string is returned.
     *
     * @param string $xpath_query The query to execute
     *
     * @return string The search result based on the given query
     */
    private function querySingleValueString(string $xpath_query): string
    {
        $result = $this->xml->xpath($xpath_query);

        if (0 == \count($result)) {
            return '';
        }

        return \strval($result[0]);
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
