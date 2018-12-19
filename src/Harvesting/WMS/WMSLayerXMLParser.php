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
}
