<?php

/**
 * ExtraTools
 *
 * @link https://github.com/digitalist-se/extratools
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExtraTools;

use Piwik\Plugins\ExtraTools\Lib\Archivers;
use Piwik\Common;
use Piwik\Db;
use Piwik\Piwik;

/**
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    private static $rawPrefix = 'segment';

    protected function getTable()
    {
        return Common::prefixTable(self::$rawPrefix);
    }


    public function index()
    {
        Piwik::checkUserHasSomeViewAccess();
        $info = "<p>ExtraTools is an open source plugin for Matomo, developed and maintained by
          <a href='https://www.digitalist.se'>Digitalist Open Tech</a> and the Matomo Community.
          ExtraTools adds a lot of functionality to your Matomo instance, focusing on
          automation in the backend, like installing, handling segments,
          sites and more.</p>
          <p>We are providing <a href='https://www.digitalist.se/blogg/our-matomo-saas'>professional services</a>,
          <a href='https://github.com/digitalist-se/MatomoPlugins'>open source and licensed plugins</a>.</p>";
        return $this->renderTemplate('index', array(
            'info' => $info
        ));
    }

    public function docs()
    {
        Piwik::checkUserHasSomeViewAccess();
        $info = "ExtraTools Docs";
        return $this->renderTemplate('docs', array(
            'info' => $info
        ));
    }


    public function phpinfo()
    {
        Piwik::checkUserHasSomeAdminAccess();
        $api = new API();
        // Get phpinfo
        $info = $api->getPhpInfo();
        return $this->renderTemplate('phpinfo', array(
            'info' => $info
        ));
    }
    public function invalidatedarchives()
    {
        Piwik::checkUserHasSomeAdminAccess();
        $result = [];
        $archivers = $this->getArchivers();
        foreach ($archivers as $out) {
            if ($out['name'] == 'done') {
                $out['name'] = 'All visits';
            } else {
                $hash = preg_replace('/^done/', '', $out['name']);
                $name = $this->getSegmentName($hash);
                $out['name'] = $name['name'];
            }
            switch ($out['period']) {
                case 1:
                    $out['period'] = 'day';
                    break;
                case 2:
                    $out['period'] = 'week';
                    break;
                case 3:
                    $out['period'] = 'month';
                    break;
                case 4:
                    $out['period'] = 'year';
                    break;
            }

            $result[] = $out;
        }
        return $this->renderTemplate('invalidatedarchives', array(
            'archivers' => $result
        ));
    }


    public function getArchivers()
    {
        $list = new Archivers();
        $out = $list->getAllInvalidations();
        return $out;
    }

    public function getSegmentName($hash)
    {
        try {
            $sql = "SELECT `name` FROM " . $this->getTable() . " WHERE `hash` = '" . $hash . "';";
            $name = $this->getDb()->fetchRow($sql);
            return $name;
        } catch (\Exception $e) {
            return false;
        }
    }
    private function getDb()
    {
        return Db::get();
    }
}
