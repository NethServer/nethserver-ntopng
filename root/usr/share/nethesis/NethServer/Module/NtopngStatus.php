<?php

/*
 * Copyright (C) 2017 Nethesis S.r.l.
 * http://www.nethesis.it - nethserver@nethesis.it
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License,
 * or any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see COPYING.
 */

namespace NethServer\Module;

class NtopngStatus extends \Nethgui\Controller\AbstractController implements \Nethgui\Component\DependencyConsumer
{
    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        return new \NethServer\Tool\CustomModuleAttributesProvider($base, array(
            'languageCatalog' => array('NethServer_Module_Ntopng'),
            'category' => 'Report')
        );
    }

    private function formatSince($s)
    {
        preg_match('/(?:(?P<d>\d+) d[a-z]*)?[\s,]*(?:(?P<h>\d+) h[a-z]*)?[\s,]+(?:(?P<m>\d+) m[a-z]*)?[\s,]+(?:(?P<s>\d+) s[a-z]*)?/i', $s, $matches);
        $out = '';
        foreach(array('d', 'h', 'm', 's') as $u) {
            if(isset($matches[$u]) && $matches[$u] > 0) {
                $emit = TRUE;
            }

            if($emit) {
                $out .= sprintf("%d$u ", $matches[$u]);
            }
        }
        return $out;
    }

    private function readTalkers($view)
    {
        if( ! function_exists('curl_version')) {
            $this->getLog()->error(sprintf("%s: %s", __CLASS__, 'PHP curl extension seems not available'));
            return FALSE;
        }

        $tcpPort = $this->getPlatform()->getDatabase('configuration')->getProp('ntopng', 'TCPPort');

        $ch = curl_init("http://localhost:$tcpPort/lua/get_hosts_data.lua?mode=local&currentPage=1&sortColumn=column_thpt&sortOrder=desc&long_names=1");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, "/tmp/ntopng-cookies");
        curl_setopt($ch, CURLOPT_COOKIEFILE, "/tmp/ntopng-cookies");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);

        $response = curl_exec($ch);
        if($response === FALSE) {
            $this->getLog()->error(sprintf("%s: %s", __CLASS__, curl_error($ch)));
            return FALSE;
        } elseif($response[0] !== '{') {
            // Make sure response is JSON formatted after cookies are set
            $response = curl_exec($ch);
        }

        curl_close($ch);

        $urlPrefix = $this->getPlatform()->getDatabase('configuration')->getProp('ntopng', 'alias');

        $data = json_decode($response, TRUE);
        if($data === FALSE || ! is_array($data['data'])) {
            $this->getLog()->error(sprintf("%s: %s", __CLASS__, 'could not decode ntopng response'));
            return FALSE;
        }

        $rows = array();
        foreach($data['data'] as $row) {
            if($urlPrefix) {
                $showUrl = $row['column_url'];
            } else {
                $showUrl = "http://{$_SERVER['SERVER_NAME']}:$tcpPort{$row['column_url']}";
            }

            $rows[] = array(
                array('columns' => array(), 'rowCssClass' => ''),
                strip_tags($row['column_name']),
                str_replace('&nbsp;', '', strip_tags($row['column_ip'])),
                strtoupper(str_replace('bit/s', '', strip_tags($row['column_thpt']))),
                strip_tags($row['column_traffic']),
                $this->formatSince(strip_tags($row['column_since'])),
                array(
                    array($view->translate('Show'), $view->getModuleUrl() . '?act=Show&url=' . urlencode($showUrl))
                ),
            );
        }
        return $rows;
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        if($this->getPlatform()->getDatabase('configuration')->getProp('ntopng', 'status') === 'enabled') {
            $isServiceEnabled = TRUE;
        } else {
            $this->notifications->warning($view->translate('NtopngStatus_ntopng_disabled_message'));
            $isServiceEnabled = FALSE;
        }

        $act = $this->getRequest()->getParameter('act');
        if($act === 'TopTalkers' && $isServiceEnabled) {
            $view['TopTalkers'] = $this->readTalkers($view);
            if($view['TopTalkers'] === FALSE) {
                $this->notifications->error($view->translate('NtopngStatus_ntopng_error_message'));
            }
        } elseif ($act === 'Show') {
            $view->getCommandList('TopTalkers')->redirect($this->getRequest()->getParameter('url'));
        } elseif ($act === 'Configure') {
            $view->getCommandList('TopTalkers')->redirect($view->getModuleUrl('/Ntopng'));
        }
    }

    public function getDependencySetters()
    {
        return array(
            'UserNotifications' => array($this, 'setUserNotifications'),
        );
    }

    public function setUserNotifications(\Nethgui\Model\UserNotifications $n)
    {
        $this->notifications = $n;
        return $this;
    }

}