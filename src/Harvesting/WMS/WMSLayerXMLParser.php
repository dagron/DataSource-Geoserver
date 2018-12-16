<?php

namespace NijmegenSync\DataSource\Geoserver\Harvesting\WMS;

use NijmegenSync\DataSource\Geoserver\Harvesting\XMLParser;

class WMSLayerXMLParser extends XMLParser
{
    /**
     * WMSLayerXMLParser constructor.
     *
     * @param \SimpleXMLElement $geoserver_response The XML object created from the response
     */
    public function __construct(\SimpleXMLElement $geoserver_response)
    {
        $this->xml = $geoserver_response;
    }

    public function findTitle(): string
    {
        return $this->querySingleValueString(
            '//Title'
        );
    }

    public function findAllQueryableLayers(): array
    {
        $layers    = $this->xml->xpath('//Layer[@queryable=1]');
        $resources = [];

        for ($i = 0; $i < \count($layers); ++$i) {
            $resources[] = $this->xml->xpath(
                \sprintf('//Layer[@queryable=1][%s]/Name', $i)
            );
        }

        return $resources;
    }
}
