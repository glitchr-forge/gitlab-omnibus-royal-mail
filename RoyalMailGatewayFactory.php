<?php

namespace Omnibus\RoyalMail;

use Omnibus\Core\GatewayFactory;

class RoyalMailGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'omnibus.factory_name' => 'royal-mail',
            'omnibus.factory_title' => 'Royal Mail'
        ]);
    }
}