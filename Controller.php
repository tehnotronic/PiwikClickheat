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

use Piwik\Common;
use Piwik\Config;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Translate;
use Piwik\View;

require_once PIWIK_INCLUDE_PATH . '/core/Config.php';

/**
 * Clickheat controller
 *
 * @package Clickheat
 */
class Controller extends \Piwik\Plugin\Controller
{

	function init()
	{
		$__languages = array('bg', 'cz', 'de', 'en', 'es', 'fr', 'hu', 'id', 'it', 'ja', 'nl', 'pl', 'pt', 'ro', 'ru', 'sr', 'tr', 'uk', 'zh');

		if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] !== '')
		{
			$realPath = &$_SERVER['REQUEST_URI'];
		}
		elseif (isset($_SERVER['SCRIPT_NAME']) && $_SERVER['SCRIPT_NAME'] !== '')
		{
			$realPath = &$_SERVER['SCRIPT_NAME'];
		}
		else
		{
			exit(Piwik::translate('UNKNOWN_DIR'));
		}

		/** First of all, check if we are inside Piwik */
		$dirName = dirname($realPath);
		if ($dirName === '/')
		{
			$dirName = '';
		}

		define('CLICKHEAT_PATH', $dirName.'/plugins/Clickheat/libs/');
		define('CLICKHEAT_INDEX_PATH', 'index.php?module=Clickheat&');
		define('CLICKHEAT_ROOT', PIWIK_INCLUDE_PATH.'/plugins/Clickheat/libs/');
		define('IS_PIWIK_MODULE', true);

		if (Piwik::isUserIsSuperUser())
		{
			define('CLICKHEAT_ADMIN', true);
		}
		else
		{
			define('CLICKHEAT_ADMIN', false);
		}

		define('CLICKHEAT_LANGUAGE', Translate::getLanguageToLoad());
		$clickheatConf = $this->conf();
	}

	function conf() {
		require_once PIWIK_INCLUDE_PATH . '/plugins/Clickheat/ClickConfig.php';
		return getConf();
	}
	

    /**
     * Default function
     */
    public function index()
    {
        return $this->view();
		//echo '';
    }
	
	
	/**
	 * Main method
	 */
	function view()
	{
        $view = new View('@Clickheat/view');
        $this->setBasicVariablesView($view);
	
		/** List of available groups */
		$groups = array();
		$conf = $this->conf();
		$d = dir($conf['logPath']);

		/** Fix by Kowalikus: get the list of sites the current user has view access to */
		$idSite = (int) Common::getRequestVar('idSite');
		if (Piwik::isUserHasViewAccess($idSite) === false)
		{
			return false;
		}

		while (($dir = $d->read()) !== false)
		{
			if ($dir[0] === '.' || !is_dir($d->path.$dir))
			{
				continue;
			}
			$pos = strpos($dir, ',');
			if ($pos === false)
			{
				continue;
			}
			$site = (int) substr($dir, 0, $pos);
			/** Fix by Kowalikus: check if current user has view access */
			if ($site !== $idSite)
			{
				continue;
			}
			$groups[$dir] = ($pos === false ? $dir : substr($dir, $pos + 1));
		}
		$d->close();
		/** Sort groups in alphabetical order */
		$__selectGroups = sort($groups);
		
		$__selectScreens = array();
		for ($i = 0; $i < count($conf['__screenSizes']); $i++)
		{
		    $sNr = $conf['__screenSizes'][$i];
			$__selectScreens[$sNr] = ($sNr === 0 ? Piwik::translate("ALL") : $sNr.'px'); 
		}

		/** Browsers */
		$__selectBrowsers = array();
		foreach ($conf['__browsersList'] as $label => $name)
		{
			$__selectBrowsers[$label] = ($label === 'all' ? Piwik::translate("ALL") : ($label === 'unknown' ? Piwik::translate("UNKNOWN") : $name));
		}

		/** Date */
		$date = strtotime(Common::getRequestVar('date', 'today', 'string'));
		if ($date === false)
		{
			if ($conf['yesterday'] === true)
			{
				$date = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
			}
			else
			{
				$date = time();
			}
		}
		$__day = (int) date('d', $date);
		$__month = (int) date('m', $date);
		$__year = (int) date('Y', $date);

		$range = Common::getRequestVar('period', 'day', 'string');
		$range = $range[0];

		if (!in_array($range, array('d', 'm', 'w')))
		{
			$range = 'd';
		}
		if ($range === 'w')
		{
			$startDay = $conf['start'] === 'm' ? 1 : 0;
			while (date('w', $date) != $startDay)
			{
				$date = mktime(0, 0, 0, date('m', $date), date('d', $date) - 1, date('Y', $date));
			}
			$__day = (int) date('d', $date);
			$__month = (int) date('m', $date);
			$__year = (int) date('Y', $date);
		}
		elseif ($range === 'm')
		{
			$__day = 1;
		}

		$pageURL = 'http';
        if (@$_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
        $pageURL .= "://".dirname($_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"])."/plugins/Clickheat/lib/";
		
		$view->clickheat_host = 'http://'.$_SERVER['SERVER_NAME'];
		$view->clickheat_server = $pageURL;
		$view->ch_empty = $pageURL."clickempty.html?idSite=".$idSite;
		$view->clickheat_path = CLICKHEAT_PATH;
		$view->clickheat_index = CLICKHEAT_INDEX_PATH;
		$view->clickheat_group = Piwik::translate('GROUP');
		$view->clickheat_groups = $__selectGroups;
		$view->clickheat_browser = Piwik::translate('BROWSER');
		$view->clickheat_browsers = $__selectBrowsers;
		$view->clickheat_screen = Piwik::translate('SCREENSIZE');
		$view->clickheat_screens = $__selectScreens;
		$view->clickheat_heatmap = Piwik::translate('HEATMAP');
		$view->clickheat_loading = str_replace('\'', '\\\'', Piwik::translate('ERROR_LOADING'));
		$view->clickheat_cleaner = str_replace('\'', '\\\'', Piwik::translate('CLEANER_RUNNING'));
		$view->clickheat_admincookie = str_replace('\'', '\\\'', Piwik::translate('JAVASCRIPT_ADMIN_COOKIE'));
		$view->clickheat_alpha = $conf['alpha'];
		$view->clickheat_iframes = $conf['hideIframes'] === true ? 'true' : 'false';
		$view->clickheat_flashes = $conf['hideFlashes'] === true ? 'true' : 'false';
		$view->clickheat_force_heatmap = $conf['heatmap'] === true ? ' checked="checked"' : '';
		$view->clickheat_day = $__day;
		$view->clickheat_month = $__month;
		$view->clickheat_year = $__year;
		$view->clickheat_range = $range;

        return $view->render();
	}

	function iframe()
	{
		$group = isset($_GET['group']) ? str_replace('/', '', $_GET['group']) : '';
		$conf = $this->conf();
		$webPage = array( CLICKHEAT_PATH . 'clickempty.html' );
		if (is_dir($conf['logPath'].$group))
		{
			if (file_exists($conf['logPath'].$group.'/url.txt'))
			{
				$f = @fopen($conf['logPath'].$group.'/url.txt', 'r');
				if ($f !== false)
				{
					$webPage = explode('>', trim(fgets($f, 1024)));
					fclose($f);
				}
			}
		}
		echo $webPage[0]; 
	}

	function javascript()
	{
		require_once(CLICKHEAT_ROOT.'javascript.php');
	}

	function layout()
	{
		require_once(CLICKHEAT_ROOT.'layout.php');
	}

	function generate()
	{
		require_once(CLICKHEAT_ROOT.'generate.php');
	}

	function png()
	{
		$conf = $this->conf();
		$imagePath = $conf['cachePath'].(isset($_GET['file']) ? str_replace('/', '', $_GET['file']) : '**unknown**');

		header('Content-Type: image/png');
		if (file_exists($imagePath))
		{
			readfile($imagePath);
		}
		else
		{
			readfile(CLICKHEAT_ROOT.'images/warning.png');
		}
	}

	function layoutupdate()
	{
		$group = isset($_GET['group']) ? str_replace('/', '', $_GET['group']) : '';
		$url = isset($_GET['url']) ? $_GET['url'] : '';
		if (strpos($url, 'http') !== 0)
		{
			$url = 'http://'.$_SERVER['SERVER_NAME'].'/'.ltrim($url, '/');
		}
		/** Improved security for PHP injection (PMV2.3b3 bug) */
		$url = parse_url(str_replace(array('<', '>'), array('', ''), $url));
		$left = isset($_GET['left']) ? (int) $_GET['left'] : 0;
		$center = isset($_GET['center']) ? (int) $_GET['center'] : 0;
		$right = isset($_GET['right']) ? (int) $_GET['right'] : 0;

		$conf = $this->conf();
		if (!is_dir($conf['logPath'].$group) || !isset($url['host']) || !isset($url['path']))
		{
			exit('Error');
		}

		if ($url['scheme'] !== 'http' && $url['scheme'] !== 'https')
		{
			$url['scheme'] = 'http';
		}
		if (isset($url['query']))
		{
			$url = $url['scheme'].'://'.$url['host'].$url['path'].'?'.$url['query'];
		}
		else
		{
			$url = $url['scheme'].'://'.$url['host'].$url['path'];
		}
		$f = fopen($conf['logPath'].$group.'/url.txt', 'w');
		fputs($f, $url.'>'.$left.'>'.$center.'>'.$right);
		fclose($f);

		exit('OK');
	}

	function cleaner()
	{
		require_once(CLICKHEAT_ROOT.'cleaner.php');
	}
	
}
