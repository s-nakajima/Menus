<?php
/**
 * MenuHelper::link()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsHelperTestCase', 'NetCommons.TestSuite');

/**
 * MenuHelper::link()のテスト
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Menus\Test\Case\View\Helper\MenuHelper
 */
class MenuHelperLinkTest extends NetCommonsHelperTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.menus.menu_frame_setting',
		'plugin.menus.menu_frames_page',
		'plugin.menus.menu_frames_room',
		'plugin.menus.page4menu',
		'plugin.menus.pages_language4menu',
		'plugin.pages.room4pages',
		'plugin.rooms.rooms_language4test',
	);

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'menus';

/**
 * viewVarsのデータ取得
 *
 * @param int $pageId ページID
 * @return array
 */
	private function __getViewVars($pageId) {
		$MenuFrameSetting = ClassRegistry::init('Menus.MenuFrameSetting');
		$MenuFramesRoom = ClassRegistry::init('Menus.MenuFramesRoom');
		$MenuFramesPage = ClassRegistry::init('Menus.MenuFramesPage');
		$Page = ClassRegistry::init('Pages.Page');

		$roomIds = array('2', '5', '6');
		Current::write('Page.id', $pageId);

		$viewVars = array();
		$viewVars['menus'] = $MenuFramesPage->getMenuData(array(
			'conditions' => array('Page.room_id' => $roomIds)
		));
		$viewVars['menuFrameSetting'] = $MenuFrameSetting->getMenuFrameSetting();
		$menuFrameRooms = $MenuFramesRoom->getMenuFrameRooms(array(
			'conditions' => array('Room.id' => $roomIds)
		));
		$viewVars['menuFrameRooms'] = Hash::combine($menuFrameRooms, '{n}.Room.id', '{n}');
		$viewVars['pageTreeList'] = $Page->generateTreeList(
				array('Page.room_id' => $roomIds), null, null, Page::$treeParser);
		$viewVars['pages'] = $Page->getPages($roomIds);
		$viewVars['parentPages'] = $Page->getPath(Current::read('Page.id'));

		$viewVars['childPageIds'] = [];
		$pageIds = array_keys($viewVars['pageTreeList']);
		foreach ($pageIds as $pageId) {
			$viewVars['childPageIds'][$pageId] = [];
			foreach ($viewVars['pages'][$pageId]['ChildPage'] as $child) {
				$viewVars['childPageIds'][$pageId][] = $child['id'];
			}
		}

		return $viewVars;
	}

/**
 * link()のテスト(アクティブ、子ページあり)
 *
 * @return void
 */
	public function testLinkWithChildPageWithActive() {
		//Helperロード
		$viewVars = $this->__getViewVars('4');
		$requestData = array();
		$params = array();
		$this->loadHelper('Menus.Menu', $viewVars, $requestData, $params);

		//データ生成
		$menu = Hash::get($viewVars['menus']['2'], '9');
		$class = 'menu-tree-1 active';

		//テスト実施
		$this->Menu->parentPageIds = array('1', '9');
		$result = $this->Menu->link($menu, $class);

		//チェック
		$expected = array(
			'title' => 'Page 1',
			'icon' => '<span class="glyphicon glyphicon-menu-down"> </span> ',
			'url' => '/page_1',
			'options' => array(
				'id' => 'MenuFramesPageMenuTree1Active9',
				'escapeTitle' => false,
			),
		);
		$this->assertEquals($expected, $result);
	}

/**
 * link()のテスト(アクティブでない、子ページあり)
 *
 * @return void
 */
	public function testLinkWithChildPageWOActive() {
		//Helperロード
		$viewVars = $this->__getViewVars('10');
		$requestData = array();
		$params = array();
		$this->loadHelper('Menus.Menu', $viewVars, $requestData, $params);

		//データ生成
		$menu = Hash::get($viewVars['menus']['2'], '9');
		$class = 'menu-tree-1';

		//テスト実施
		$this->Menu->parentPageIds = array();
		$result = $this->Menu->link($menu, $class);

		//チェック
		$expected = array(
			'title' => 'Page 1',
			'icon' => '<span class="glyphicon glyphicon-menu-right"> </span> ',
			'url' => '/page_1',
			'options' => array(
				'id' => 'MenuFramesPageMenuTree19',
				'escapeTitle' => false,
			),
		);
		$this->assertEquals($expected, $result);
	}

/**
 * link()のテスト(トップページ)
 *
 * @return void
 */
	public function testLinkTopPage() {
		//Helperロード
		$viewVars = $this->__getViewVars('4');
		$requestData = array();
		$params = array();
		$this->loadHelper('Menus.Menu', $viewVars, $requestData, $params);

		//データ生成
		$menu = Hash::get($viewVars['menus']['2'], '4');
		$class = 'menu-tree-1';

		//テスト実施
		$this->Menu->parentPageIds = array();
		$result = $this->Menu->link($menu, $class);

		//チェック
		$expected = array(
			'title' => 'Home ja',
			'icon' => '',
			'url' => '/',
			'options' => array(
				'id' => 'MenuFramesPageMenuTree14',
				'escapeTitle' => false,
			),
		);
		$this->assertEquals($expected, $result);
	}

/**
 * link()のテスト(ルームトップ)
 *
 * @return void
 */
	public function testLinkRoomTop() {
		//Helperロード
		$viewVars = $this->__getViewVars('5');
		$requestData = array();
		$params = array();
		$this->loadHelper('Menus.Menu', $viewVars, $requestData, $params);

		//データ生成
		$menu = Hash::get($viewVars['menus']['5'], '5');
		$class = 'menu-tree-1';

		//テスト実施
		$this->Menu->parentPageIds = array();
		$result = $this->Menu->link($menu, $class);

		//チェック
		$expected = array(
			'title' => 'サブルーム１',
			'icon' => '',
			'url' => '/test2',
			'options' => array(
				'id' => 'MenuFramesPageMenuTree15',
				'escapeTitle' => false,
			),
		);
		$this->assertEquals($expected, $result);
	}

/**
 * link()のテスト(トグル)
 *
 * @return void
 */
	public function testLinkToggle() {
		//Helperロード
		$viewVars = $this->__getViewVars('10');
		$viewVars['menus'] = Hash::insert($viewVars['menus'], '2.9.MenuFramesPage.folder_type', true);
		$requestData = array();
		$params = array();
		$this->loadHelper('Menus.Menu', $viewVars, $requestData, $params);

		//データ生成
		$menu = Hash::get($viewVars['menus']['2'], '9');
		$class = 'menu-tree-1';

		//テスト実施
		$this->Menu->parentPageIds = array();
		$result = $this->Menu->link($menu, $class);

		//チェック
		$expected = array(
			'title' => 'Page 1',
			'icon' => '<span class="glyphicon glyphicon-menu-right" ' .
							'ng-class="{\'glyphicon-menu-right\': !MenuFramesPageMenuTree19Icon, \'glyphicon-menu-down\': MenuFramesPageMenuTree19Icon}"> ' .
						'</span> ',
			'url' => '#',
			'options' => array(
				'id' => 'MenuFramesPageMenuTree19',
				'escapeTitle' => false,
				'ng-init' => 'MenuFramesPageMenuTree19Icon=0; initialize(\'MenuFramesPageMenuTree19\', ["MenuFramesPageMenuTree111","MenuFramesPageMenuTree112"], 0)',
				'ng-click' => 'MenuFramesPageMenuTree19Icon=!MenuFramesPageMenuTree19Icon; switchOpenClose($event, \'MenuFramesPageMenuTree19\')',
			),
		);
		$this->assertEquals($expected, $result);
	}

/**
 * link()のテスト(セッティングモードON)
 *
 * @return void
 */
	public function testLinkWithSettingMode() {
		//Helperロード
		Current::setSettingMode(true);
		$viewVars = $this->__getViewVars('4');
		$requestData = array();
		$params = array();
		$this->loadHelper('Menus.Menu', $viewVars, $requestData, $params);

		//データ生成
		$menu = Hash::get($viewVars['menus']['2'], '9');
		$class = 'menu-tree-1 active';

		//テスト実施
		$this->Menu->parentPageIds = array('1', '9');
		$result = $this->Menu->link($menu, $class);

		//チェック
		$expected = array(
			'title' => 'Page 1',
			'icon' => '<span class="glyphicon glyphicon-menu-down"> </span> ',
			'url' => '/setting/page_1',
			'options' => array(
				'id' => 'MenuFramesPageMenuTree1Active9',
				'escapeTitle' => false,
			),
		);
		$this->assertEquals($expected, $result);

		Current::setSettingMode(false);
	}

}
