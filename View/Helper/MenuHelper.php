<?php
/**
 * MenuHelper
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('AppHelper', 'View/Helper');
App::uses('Room', 'Rooms.Model');
App::uses('Space', 'Rooms.Model');
ClassRegistry::init('Pages.Page');

/**
 * MenuHelper
 *
 * @package NetCommons\Menus\View\Helper
 */
class MenuHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $parentPageIds = array();

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'NetCommons.NetCommonsForm',
		'NetCommons.NetCommonsHtml',
	);

/**
 * メニューの表示
 *
 * @param string $displayType 表示タイプ
 * @return string HTMLタグ
 */
	public function renderMain($displayType = null) {
		if (! $displayType) {
			$displayType = $this->_View->viewVars['menuFrameSetting']['MenuFrameSetting']['display_type'];
		}

		//現在選択しているページの親ページIDs
		$this->parentPageIds = array(Page::PUBLIC_ROOT_PAGE_ID);
		$this->parentPageIds = array_merge(
			$this->parentPageIds,
			Hash::extract($this->_View->viewVars['parentPages'], '{n}.Page.id', array())
		);
		if (! in_array(Current::read('Page.id'), $this->parentPageIds, true)) {
			$this->parentPageIds[] = Current::read('Page.id');
		}
		$this->parentPageIds = array_unique($this->parentPageIds);

		//現在選択しているページの下層ページIDs
		$this->childPageIds = Hash::extract(
			$this->_View->viewVars['pages'], Current::read('Page.id') . '.ChildPage.{n}.id', array()
		);

		// メニューのcss, js読み込み、HTML表示
		$html = $this->NetCommonsHtml->elementDisplayChange($displayType);
		return $html;
	}

/**
 * メニューの表示(さかのぼり:パンくず用)
 *
 * @param string $displayType 表示タイプ
 * @return string HTMLタグ
 */
	public function renderParent($displayType = null) {
		$html = '';
		$parentPageIds = Hash::extract($this->_View->viewVars, 'parentPages.{n}.Page.id');
		$childPages = $this->_View->viewVars['pages'][1]['ChildPage'];
		$childPages = Hash::sort($childPages, '{n}.lft', 'asc');
		$parentPageIds = array_merge(array(Hash::get($childPages, '0.id')), $parentPageIds);
		$parentPageIds = array_unique($parentPageIds);

		foreach ($parentPageIds as $parentPageId) {
			$parentRoomId = Hash::get(
				$this->_View->viewVars['pages'], $parentPageId . '.Page.room_id'
			);
			if (! $parentRoomId) {
				continue;
			}
			$html .= $this->renderPage($parentPageId, $displayType);
		}

		return $html;
	}

/**
 * メニューリストの表示
 *
 * @param array $treePageId TreeページID
 * @param string $displayType 表示タイプ
 * @return string HTMLタグ
 */
	public function renderPage($treePageId, $displayType = null) {
		$html = '';

		if (! $displayType) {
			$displayType = $this->_View->viewVars['menuFrameSetting']['MenuFrameSetting']['display_type'];
		}

		$pageId = trim($treePageId);
		$pages = $this->_View->viewVars['pages'];
		$menus = $this->_View->viewVars['menus'];

		$page = Hash::get($pages, $pageId);
		$menu = Hash::get($menus, $page['Room']['id'] . '.' . $pageId);
		$nest = $this->getIndent($treePageId);
		if (! $this->displayPage($treePageId)) {
			return $html;
		}

		$html .= $this->_View->element('Menus.Menus/' . $displayType . '/list', array(
			'isActive' => $this->isActive($page),
			'nest' => $nest,
			'options' => $this->link($menu, $displayType),
			'menu' => $menu,
			'page' => $page,
		));

		return $html;
	}

/**
 * リンクの表示
 *
 * @param array $menu リンクデータ
 * @param string $displayType 表示タイプ
 * @return array NetCommonsHtml->linkの引数
 */
	public function link($menu, $displayType = null) {
		if (! $menu) {
			return array();
		}
		if (! $displayType) {
			$displayType = $this->_View->viewVars['menuFrameSetting']['MenuFrameSetting']['display_type'];
		}

		$setting = '';
		if (Current::isSettingMode()) {
			$setting = Current::SETTING_MODE_WORD . '/';
		}
		$room = Hash::get($this->_View->viewVars['menuFrameRooms'], $menu['Page']['room_id']);

		$viewPage = $this->_View->viewVars['pages'];
		if ($room['Room']['page_id_top'] === $menu['Page']['id'] &&
				$room['Room']['id'] === Space::getRoomIdRoot(Space::PUBLIC_SPACE_ID)) {
			$url = '';
		} elseif (Hash::get($viewPage, $menu['Page']['id'] . '.Page.full_permalink')) {
			$url = h(Hash::get($viewPage, $menu['Page']['id'] . '.Page.full_permalink'));
		} else {
			$url = h($menu['Page']['permalink']);
		}

		$title = $this->__getTitle($menu, $displayType);

		$domId = $this->getLinkDomId($displayType, $menu['Page']['id']);
		$domIdIcon = $domId . 'Icon';
		$options = array('id' => $domId, 'escapeTitle' => false);
		$toggle = (int)in_array($menu['Page']['id'], $this->parentPageIds, true);

		$hasChildPage = $this->hasChildPage($menu, false);

		if (Hash::get($menu, 'MenuFramesPage.folder_type') && $hasChildPage) {
			$childPageIds = array();
			$childPageIds = $this->getRecursiveChildPageId(
				$menu['Page']['room_id'], $menu['Page']['id'], $childPageIds
			);
			$childDomIds = array_map(function ($value) use($displayType) {
				return $this->getLinkDomId($displayType, $value);
			}, $childPageIds);

			$options['ng-init'] = $domIdIcon . '=' . $toggle . ';' .
				' initialize(\'' . $domId . '\', ' . json_encode($childDomIds) . ', ' . $toggle . ')';
			$options['ng-click'] = $domIdIcon . '=!' . $domIdIcon . ';' .
								' switchOpenClose($event, \'' . $domId . '\')';
			$url = '#';
		} else {
			$url = '/' . $setting . $url;
		}

		return array(
			'title' => $title['title'],
			'icon' => $title['icon'],
			'url' => $url,
			'options' => $options
		);
	}

/**
 * リンクのタイトル表示
 *
 * @param array $menu リンクデータ
 * @param string $displayType 表示タイプ
 * @return array array(タイトル, アイコン)
 */
	private function __getTitle($menu, $displayType) {
		$room = Hash::get($this->_View->viewVars['menuFrameRooms'], $menu['Page']['room_id']);

		$title = '';
		if ($room && Hash::get($room['Room'], 'page_id_top', false) === $menu['Page']['id'] &&
				$room['Room']['id'] !== Space::getRoomIdRoot(Space::PUBLIC_SPACE_ID)) {
			$title .= h(Hash::get($room, 'RoomsLanguage.name', ''));
		} else {
			$title .= h(Hash::get($menu, 'PagesLanguage.name', ''));
		}

		$domId = $this->getLinkDomId($displayType, $menu['Page']['id']);
		$domIdIcon = $domId . 'Icon';
		$toggle = (int)in_array($menu['Page']['id'], $this->parentPageIds, true);

		$hasChildPage = $this->hasChildPage($menu, false);

		if (Hash::get($menu, 'MenuFramesPage.folder_type') && $hasChildPage) {
			$icon = '<span class="glyphicon glyphicon-menu-right"' .
						' ng-class="{' .
							'\'glyphicon-menu-right\': !' . $domIdIcon . ', ' .
							'\'glyphicon-menu-down\': ' . $domIdIcon . '' .
						'}"> ' .
					'</span> ';

		} elseif ($hasChildPage) {
			if ($toggle) {
				$icon = '<span class="glyphicon glyphicon-menu-down"> </span> ';
			} else {
				$icon = '<span class="glyphicon glyphicon-menu-right"> </span> ';
			}
		} else {
			$icon = null;
		}

		return array('title' => $title, 'icon' => $icon);
	}

/**
 * ChildPageのIdを取得する(再帰的に)
 *
 * @param int $roomId Room.id
 * @param int $pageId Page.id
 * @param array $result 再帰の結果配列
 * @return string HTMLタグ
 */
	public function getRecursiveChildPageId($roomId, $pageId, $result) {
		$childPageIds = Hash::extract(
			$this->_View->viewVars['pages'], $pageId . '.ChildPage.{n}.id', array()
		);
		foreach ($childPageIds as $childPageId) {
			$result[] = $childPageId;

			$prefixInput = $roomId . '.' . $childPageId . '.MenuFramesPage.folder_type';
			if (! Hash::get($this->_View->viewVars['menus'], $prefixInput, false)) {
				$result = $this->getRecursiveChildPageId($roomId, $childPageId, $result);
			}
		}
		return $result;
	}

/**
 * 表示するかどうか
 *
 * @param string $treePageId Tree(Tabコード付き)のページID
 * @return bool
 */
	public function displayPage($treePageId) {
		$pageId = trim($treePageId);
		$pages = $this->_View->viewVars['pages'];
		$menus = $this->_View->viewVars['menus'];

		$page = Hash::get($pages, $pageId);
		$menu = Hash::get($menus, $page['Room']['id'] . '.' . $pageId);

		if (! $menu['PagesLanguage']['name'] ||
				! $menu ||
				! isset($this->_View->viewVars['treeList4Disp'][$pageId])) {
			return false;
		}
		if ($pageId === Page::PUBLIC_ROOT_PAGE_ID) {
			return false;
		}

		return true;
	}

/**
 * アクティブかどうか
 *
 * @param array $page ページデータ配列
 * @return bool
 */
	public function isActive($page) {
		return Current::read('Page.permalink') === (string)$page['Page']['permalink'];
	}

/**
 * 子ページを持っているかどうか
 *
 * @param int $menu メニューデータ
 * @param bool $hasTreeList $treeList4Dispに持っているかどうか
 * @return bool
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function hasChildPage($menu, $hasTreeList = false) {
		$childPage = Hash::extract(
			$this->_View->viewVars['pages'], $menu['Page']['id'] . '.ChildPage.{n}.id', array()
		);

		if ($hasTreeList) {
			$pageTreeList = $this->_View->viewVars['treeList4Disp'];
		} else {
			$pageTreeList = $this->_View->viewVars['pageTreeList'];
		}

		$hasChildPage = false;
		foreach ($childPage as $id) {
			if (isset($pageTreeList[$id])) {
				$hasChildPage = true;
				break;
			}
		}

		if ($hasTreeList) {
			$result = $hasChildPage;
		} else {
			if (Hash::get($menu, 'MenuFramesPage.folder_type') && $hasChildPage) {
				$result = true;
			} elseif ($hasChildPage) {
				$result = true;
			} else {
				$result = false;
			}
		}

		return $result;
	}

/**
 * インデント数の取得
 *
 * @param string $treePageId Tree(Tabコード付き)のページID
 * @return int
 */
	public function getIndent($treePageId) {
		$pageId = trim($treePageId);
		$pages = $this->_View->viewVars['pages'];
		$page = Hash::get($pages, $pageId);

		$indent = substr_count($treePageId, Page::$treeParser);
		if ($page['Page']['root_id'] === Page::PUBLIC_ROOT_PAGE_ID) {
			$indent--;
		}

		return $indent;
	}

/**
 * リンクのdomIdを取得する
 *
 * @param string $displayType 表示タイプ
 * @param int $pageId ページID
 * @return string
 */
	public function getLinkDomId($displayType, $pageId) {
		$domDisplayType = preg_replace('/-/', '_', $displayType);
		return $this->domId(
			'MenuFramesPage.' . $domDisplayType . '.' . Current::read('Frame.id') . '.' . $pageId
		);
	}

}
