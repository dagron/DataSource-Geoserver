<?php

namespace NijmegenSync\DataSource\Geoserver\Harvesting\WFS;

use NijmegenSync\DataSource\Geoserver\Harvesting\XMLParser;

/**
 * Class WFSEntityXMLParser.
 *
 * Allows for the parsing of specific FeatureTypes present in the GetCapabilities API call to a
 * geoserver application.
 */
class WFSEntityXMLParser extends XMLParser
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
     * Finds and returns the name of a FeatureType, will return an empty string if nothing is found.
     *
     * @return string The name of the FeatureType
     */
    public function findName(): string
    {
        $children = $this->xml->children();

        foreach ($children as $child) {
            if ('Name' == $child->getName()) {
                $node_as_string   = \strval($child);
                $divider_position = \strpos($node_as_string, ':');

                return (false !== $divider_position)
                    ? \substr($node_as_string, $divider_position + 1)
                    : $node_as_string;
            }
        }

        return '';
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
