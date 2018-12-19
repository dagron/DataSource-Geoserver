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

    public function findName(): string
    {
        return $this->xml->Name;
    }

    public function findTitle(): string
    {
        return $this->xml->Title;
    }

    public function findBoundingBox(): string
    {
        foreach ($this->xml->BoundingBox as $box) {
            if ('EPSG:28992' == $box->attributes()['SRS']) {
                $min_x = \substr($box->attributes()['minx'], 0, 6);
                $min_y = \substr($box->attributes()['miny'], 0, 6);
                $max_x = \substr($box->attributes()['maxx'], 0, 6);
                $max_y = \substr($box->attributes()['maxy'], 0, 6);

                return \sprintf('%s,%s,%s,%s', $min_x, $min_y, $max_x, $max_y);
            }
        }

        return '';
    }
}
