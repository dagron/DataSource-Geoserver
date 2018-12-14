<?php

namespace NijmegenSync\DataSource\Geoserver\Harvesting;

/**
 * Class XMLParser.
 *
 * Base implementation for the parsing of XML for the Nijmegen geoserver.
 */
abstract class XMLParser
{
    /** @var \SimpleXMLElement */
    protected $xml;

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
    protected function querySingleValueString(string $xpath_query): string
    {
        $result = $this->xml->xpath($xpath_query);

        if (0 == \count($result)) {
            return '';
        }

        return \strval($result[0]);
    }
}
