<?php
namespace NethServer\Module;

/*
 * Copyright (C) 2011 Nethesis S.r.l.
 * 
 * This script is part of NethServer.
 * 
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

use Nethgui\System\PlatformInterface as Validate;

/**
 * Change ntopng settings
 *
 * @author Giacomo Sanchietti<giacomo.sanchietti@nethesis.it>
 */
class Ntopng extends \Nethgui\Controller\AbstractController
{

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        return \Nethgui\Module\SimpleModuleAttributesProvider::extendModuleAttributes($base, 'Configuration', 60);
    }

    public function initialize()
    {
        parent::initialize();

        $validInterfaces = array_keys($this->getNetworkInterfaces());

        $this->declareParameter('status', Validate::SERVICESTATUS, array('configuration', 'ntopng', 'status'));
        $this->declareParameter('Authentication', Validate::SERVICESTATUS, array('configuration', 'ntopng', 'Authentication'));
        $this->declareParameter('TCPPort', Validate::PORTNUMBER, array('configuration', 'ntopng', 'TCPPort'));
        $this->declareParameter('Interfaces', $this->createValidator()->collectionValidator($this->createValidator()->memberOf($validInterfaces)), array('configuration', 'ntopng', 'Interfaces', ','));
    }

    protected function onParametersSaved($changes)
    {
        $this->getPlatform()->signalEvent('nethserver-ntopng-save');
    }

    private function getNetworkInterfaces()
    {
        static $interfaces;

        if (isset($interfaces)) {
            return $interfaces;
        }

        $interfaces = array_filter($this->getPlatform()->getDatabase('networks')->getAll(), function ($record) {
            if ( ! in_array($record['type'], array('ethernet', 'bridge', 'bond', 'vlan'))) {
                return FALSE;
            }

            return TRUE;
        });

        return $interfaces;
    }


    public function prepareView(\Nethgui\View\ViewInterface $view) 
    {
        parent::prepareView($view);

        $domain = $this->getPlatform()->getDatabase('configuration')->getType('DomainName');
        $system = $this->getPlatform()->getDatabase('configuration')->getType('SystemName');
        $view['FQDN'] = "$system.$domain";

        $ds = array();
        foreach ($this->getNetworkInterfaces() as $key => $props) {
            $ds[] = array($key, $key . ' - ' . $props['role']);
        }

        $view['InterfacesDatasource'] = $ds;
    }

}

