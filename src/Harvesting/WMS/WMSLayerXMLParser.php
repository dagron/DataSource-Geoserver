<?php

namespace NijmegenSync\DataSource\Geoserver\Harvesting\WMS;

use NijmegenSync\DataSource\Geoserver\Harvesting\XMLParser;

/**
 * Class WMSLayerXMLParser.
 *
 * Allows for the parsing of specific Layers returned by a GetCapabilities API call to a WMS server.
 */
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

    /**
     * Extracts the Name from the given WMS Layer.
     *
     * @return string The extracted Name
     */
    public function findName(): string
    {
        return $this->findChildByName('Name');
    }

    /**
     * Extracts the Title from the given WMS Layer.
     *
     * @return string The extracted Title
     */
    public function findTitle(): string
    {
        return $this->findChildByName('Title');
    }

    /**
     * Extracts the Abstract from the given WMS Layer.
     *
     * @return string The extracted Abstract
     */
    public function findAbstract(): string
    {
        return $this->findChildByName('Abstract');
    }

    /**
     * Extracts the BoundingBox of this WMS Vector to properly render the Vector as an image.
     *
     * @return string The comma separated EPSG:28992 coordinates of the Vector
     */
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

    /**
     * Extracts the string value of the first child matching the given name.
     *
     * @param string $name The name of the child to look for
     *
     * @return string The string value of the found child
     */
    private function findChildByName(string $name): string
    {
        $children = $this->xml->children();

        foreach ($children as $child) {
            if ($child->getName() == $name) {
                return \strval($child);
            }
        }

        return '';
    }
}
