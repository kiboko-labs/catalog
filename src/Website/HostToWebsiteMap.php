<?php


namespace LizardsAndPumpkins\Website;

interface HostToWebsiteMap
{
    /**
     * @param string $host
     * @return Website
     */
    public function getWebsiteCodeByHost($host);
}