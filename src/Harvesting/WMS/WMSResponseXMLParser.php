<?php

namespace NijmegenSync\DataSource\Geoserver\Harvesting\WMS;

use NijmegenSync\DataSource\Geoserver\Harvesting\XMLParser;

/**
 * Class WMSResponseXMLParser.
 */
class WMSResponseXMLParser extends XMLParser
{
    /**
     * GeoserverXMLParser constructor.
     *
     * @param \SimpleXMLElement $geoserver_response The XML object created from the response
     */
    public function __construct(\SimpleXMLElement $geoserver_response)
    {
        $this->xml = $geoserver_response;
    }

    /**
     * Finds all the layers which are eligible to turn into datasets.
     *
     * @return WMSLayerXMLParser[] The parsers for the eligible layers
     */
    public function getAllLayers(): array
    {
        $layers        = $this->xml->xpath('//Layer[@queryable=1]/parent::Layer');
        $layer_parsers = [];

        foreach ($layers as $layer) {
            $layer_parsers[] = new WMSLayerXMLParser($layer);
        }

        return $layer_parsers;
    }

    /**
     * Looks for the person or department of the contact point.
     *
     * @return string The search result
     */
    public function findContactPerson(): string
    {
        return $this->querySingleValueString(
            '//ContactInformation/ContactPersonPrimary/ContactPerson'
        );
    }

    /**
     * Looks for the organization behind the contact point.
     *
     * @return string The search result
     */
    public function findContactOrganization(): string
    {
        return $this->querySingleValueString(
            '//ContactInformation/ContactPersonPrimary/ContactOrganization'
        );
    }

    /**
     * Looks for the email address of the contact point.
     *
     * @return string The search result
     */
    public function findContactEmail(): string
    {
        return $this->querySingleValueString(
            '//ContactInformation/ContactElectronicMailAddress'
        );
    }

    /**
     * Looks for any access rights restrictions in the metadata.
     *
     * @return string The search result
     */
    public function findAccessRights(): string
    {
        return $this->querySingleValueString(
            '//Service/AccessConstraints'
        );
    }

    /**
     * Looks for the desired output format of the API.
     *
     * @return string The search result
     */
    public function findDesiredOutputFormat(): string
    {
        $query = $this->querySingleValueString(
            '//Capability/Request/GetMap/Format[text()="image/png"]'
        );

        if ('' === $query) {
            $query = $this->querySingleValueString(
                '//Capability/Request/GetMap/Format[text()="image/jpeg"]'
            );
        }

        return $query;
    }
}
