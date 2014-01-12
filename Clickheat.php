<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Clickheat
 */
namespace Piwik\Plugins\Clickheat;

use Piwik\Menu\MenuMain;
use Piwik\Config;
use Piwik\Plugin;

/**
 *
 * @package Clickheat
 */
class Clickheat extends \Piwik\Plugin
{
    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'Menu.Reporting.addItems' => 'addMenu'
        );
        return $hooks;
    }
	
    /**
     * Add menu items
     */
    function addMenu()
    {
		MenuMain::getInstance()->add('Clickheat', '', array('module' => 'Clickheat', 'action' => 'view'), true, 27);
    }

	public function install()
	{
		/** Create config */
	    Config::getInstance()->Clickheat = array(
            'logPath' => PIWIK_INCLUDE_PATH.'/tmp/cache/clickheat/logs/',
            'cachePath' => PIWIK_INCLUDE_PATH.'/tmp/cache/clickheat/cache/',
            'referers' => true,
            'groups' => true,
            'filesize' => 0,
            'adminLogin' => '',
            'adminPass' => '',
            'viewerLogin' => '',
            'viewerPass' => '',
            'memory' => 50,
            'step' => 5,
            'dot' => 19,
            'flush' => 40,
            'start' => 'm',
            'palette' => false,
            'heatmap' => true,
            'hideIframes' => true,
            'hideFlashes' => true,
            'yesterday' => false,
            'alpha' => 80
        );
        Config::getInstance()->forceSave();
		
		/** Create main cache paths */
		$dir = PIWIK_USER_PATH.'/tmp/cache/clickheat/';
		if (!is_dir($dir.'logs'))
		{
			mkdir($dir.'logs', 0777, true);
		}
		if (!is_dir($dir.'cache'))
		{
			mkdir($dir.'cache', 0777, true);
		}
	}
	
}
