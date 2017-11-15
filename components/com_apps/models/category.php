<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Cache\Exception\CacheExceptionInterface;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/**
 * This models supports retrieving lists of contact categories.
 *
 * @package     Joomla.Site
 * @subpackage  com_apps
 * @since       1.6
 */
class AppsModelCategory extends ListModel
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	public $_context = 'com_apps.category';

	/**
	 * The category context (allows other extensions to derived from this model).
	 *
	 * @var		string
	 */
	protected $_extension = 'com_apps';

	private $_parent = null;

	private $_items = null;

	private $_remotedb = null;

	public $_extensionstitle = null;

	private $_categories = null;

	private $_breadcrumbs = array();

	private $_total = null;

	private $_orderby = 't2.link_rating';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = Factory::getApplication();
		$this->setState('filter.extension', $this->_extension);

		// Get the parent id if defined.
		$parentId = $app->input->getInt('id');
		$this->setState('filter.parentId', $parentId);

		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('filter.published',	1);
		$this->setState('filter.access',	true);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id	A prefix for the store id.
	 *
	 * @return  string  A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.extension');
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.access');
		$id	.= ':'.$this->getState('filter.parentId');

		return parent::getStoreId($id);
	}

	/**
	 * redefine the function an add some properties to make the styling more easy
	 *
	 * @return mixed An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		if (!count($this->_items))
		{
			$app = Factory::getApplication();
			$menu = $app->getMenu();
			$active = $menu->getActive();
			$params = new Registry;

			if ($active)
			{
				$params->loadString($active->params);
			}

			$options = array();
			$options['countItems'] = $params->get('show_cat_items_cat', 1) || !$params->get('show_empty_categories_cat', 0);
			$this->_parent = Categories::getInstance('Contact', $options)->get($this->getState('filter.parentId', 'root'));

			if (is_object($this->_parent))
			{
				$this->_items = $this->_parent->getChildren();
			}
			else
			{
				$this->_items = false;
			}
		}

		return $this->_items;
	}

	public function getParent()
	{
		if (!is_object($this->_parent))
		{
			$this->getItems();
		}
		return $this->_parent;
	}

	/**
	 * @return  bool|AppsModelBase
	 */
	private function getBaseModel()
	{
		return BaseDatabaseModel::getInstance('Base', 'AppsModel');
	}

	private function getCatID()
	{
		return Factory::getApplication()->input->get('id', null, 'int');
	}

	public function getCategories()
	{
		return $this->getBaseModel()->getCategories($this->getCatID());
	}

	public function getBreadcrumbs()
	{
		return $this->getBaseModel()->getBreadcrumbs($this->getCatID());
	}

	public function getPluginUpToDate()
	{
		return $this->getBaseModel()->getPluginUpToDate();
	}

	public function getOrderBy()
	{
		return $this->_orderby;
	}

	public function getExtensions()
	{
		/** @var \Joomla\CMS\Cache\Controller\CallbackController $cache */
		$cache = Factory::getCache('com_apps', 'callback');

		// These calls are always cached
		$cache->setCaching(true);

		// Extract some default values from the component params
		$componentParams = ComponentHelper::getParams('com_apps');

		$defaultLimit = $componentParams->get('default_limit', 8);

		// Extract params from the request
		$input = Factory::getApplication()->input;

		$catid      = $input->getInt('id', 0);
		$limit      = $input->getInt('limit', $defaultLimit);
		$limitstart = $input->getInt('limitstart', 0);
		$order      = $input->get('ordering', $this->getOrderBy());
		$search     = str_replace('_', ' ', urldecode(trim($input->get('filter_search', null))));

		// Params based on the model state
		$orderCol  = $this->getState('list.ordering', $order);
		$orderDirn = $orderCol == 'core_title' ? 'ASC' : 'DESC';

		// Build the request URL here, since this will vary based on params we will use the URL as part of our cache key
		$url = new Uri;

		$url->setScheme('https');
		$url->setHost('extensions.joomla.org');
		$url->setPath('/index.php');
		$url->setVar('option', 'com_jed');
		$url->setVar('controller', 'filter');
		$url->setVar('view', 'extension');
		$url->setVar('format', 'json');
		$url->setVar('limit', $limit);
		$url->setVar('limitstart', $limitstart);
		$url->setVar('filter[approved]', '1');
		$url->setVar('filter[published]', '1');
		$url->setVar('filter[core_catid]', $catid);
		$url->setVar('extend', '0');
		$url->setVar('order', $orderCol);
		$url->setVar('dir', $orderDirn);

		if ($search)
		{
			$url->setVar('searchall', $search);
		}

		try
		{
			// We explicitly define our own ID to keep the cache API from calculating it separately
			$items = $cache->get(array($this, 'fetchCategoryExtensions'), array($url), md5(__METHOD__ . $url->toString()));
		}
		catch (CacheExceptionInterface $e)
		{
			// Cache failure, let's try an HTTP request without caching
			$items = $this->fetchCategoryExtensions($url);
		}
		catch (RuntimeException $e)
		{
			// Other failure, this isn't good
			Log::add(
				'Could not retrieve category extension data from the JED: ' . $e->getMessage(),
				Log::ERROR,
				'com_apps'
			);

			// Throw a "sanitised" Exception but nest the caught Exception for debugging
			throw new RuntimeException('Could not retrieve category extension data from the JED.', $e->getCode(), $e);
		}

		$baseModel = $this->getBaseModel();
		$items     = $items->data;

		// Populate array
		$extensions = array(0 => array(), 1 => array());

		foreach ($items as $item)
		{
			$item->image = $baseModel->getMainImageUrl($item);

			if ($search)
			{
				$extensions[1 - $item->foundintitle][] = $item;
			}
			else
			{
				$extensions[0][] = $item;
			}
		}

		return array_merge($extensions[0], $extensions[1]);
	}

	private function getAllChildren($children, $catid)
	{
		$allchildren = array();
		if (is_array($children[$catid]) and count($children[$catid])) {
			$allchildren = array_merge($allchildren, array_keys($children[$catid]));
			foreach ($children[$catid] as $key => $value) {
				$allchildren = array_merge($allchildren, $this->getAllChildren($children, $key));
			}
		}
		return $allchildren;
	}

	public function getCount()
	{
		return $this->_total;
	}

	public function getPagination() {
		$componentParams 	= ComponentHelper::getParams('com_apps');
		$default_limit		= $componentParams->get('default_limit', 8);
		$input 				= Factory::getApplication()->input;

		$pagination = new stdClass;
		$pagination->limit 		= $input->getUint('limit', $default_limit);
		$pagination->limitstart = $input->getUint('limitstart', 0);
		$pagination->total		= $this->getCount();
		$pagination->next		= $pagination->limitstart + $pagination->limit;

		return $pagination;

	}

	/**
	 * Fetches the category data from the JED
	 *
	 * @param   Uri  $uri  The URI to request data from
	 *
	 * @return  array
	 *
	 * @throws  RuntimeException if the HTTP query fails
	 */
	public function fetchCategoryExtensions(Uri $uri)
	{
		try
		{
			$http = $this->getBaseModel()->getHttpClient();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException('Cannot fetch HTTP client to connect to JED', $e->getCode(), $e);
		}

		$response = $http->get($uri->toString());

		// Make sure we've gotten an expected good response
		if ($response->code !== 200)
		{
			throw new RuntimeException('Unexpected response from the JED', $response->code);
		}

		// The body should be a JSON string, if we have issues decoding it assume we have a bad response
		$categoryData = json_decode($response->body);

		if (json_last_error())
		{
			throw new RuntimeException('Unexpected response from the JED, JSON could not be decoded with error: ' . json_last_error_msg(), 500);
		}

		return $categoryData;
	}
}
