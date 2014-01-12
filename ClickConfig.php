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

use Piwik\Config;
use Piwik\Plugin;

function getConf() {

    if(!Config::getInstance()->Clickheat)
    {
        $clickheatConf = array (
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
    } else {
        $clickheatConf = array (
        'logPath' => @Config::getInstance()->Clickheat['logPath'],
        'cachePath' => @Config::getInstance()->Clickheat['cachePath'],
        'referers' => @Config::getInstance()->Clickheat['referers'],
        'groups' => @Config::getInstance()->Clickheat['groups'],
        'filesize' => @Config::getInstance()->Clickheat['filesize'],
        'adminLogin' => @Config::getInstance()->Clickheat['adminLogin'],
        'adminPass' => @Config::getInstance()->Clickheat['adminPass'],
        'viewerLogin' => @Config::getInstance()->Clickheat['viewerLogin'],
        'viewerPass' => @Config::getInstance()->Clickheat['viewerPass'],
        'memory' => @Config::getInstance()->Clickheat['memory'],
        'step' => @Config::getInstance()->Clickheat['step'],
        'dot' => @Config::getInstance()->Clickheat['dot'],
        'flush' => @Config::getInstance()->Clickheat['flush'],
        'start' => @Config::getInstance()->Clickheat['start'],
        'palette' => @Config::getInstance()->Clickheat['palette'],
        'heatmap' => @Config::getInstance()->Clickheat['heatmap'],
        'hideIframes' => @Config::getInstance()->Clickheat['hideIframes'],
        'hideFlashes' => @Config::getInstance()->Clickheat['hideFlashes'],
        'yesterday' => @Config::getInstance()->Clickheat['yesterday'],
        'alpha' => @Config::getInstance()->Clickheat['alpha']
        ); 
    }

    /** Specific definitions */
    $clickheatConf['__screenSizes'] = array(0 /** Must start with 0 */, 640, 800, 1024, 1280, 1440, 1600, 1800);
    $clickheatConf['__browsersList'] = array('all' => '', 'firefox' => 'Firefox', 'msie' => 'Internet Explorer', 'safari' => 'Safari', 'opera' => 'Opera', 'kmeleon' => 'K-meleon', 'unknown' => '');

    return $clickheatConf;
}

$clickheatConf = getConf();

?>
