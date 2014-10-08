<?php
/**
 * LanguagesPageFixture
 *
 * @author      Noriko Arai <arai@nii.ac.jp>
 * @author      Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link        http://www.netcommons.org NetCommons Project
 * @license     http://www.netcommons.org/license.txt NetCommons License
 * @copyright   Copyright 2014, NetCommons Project
 */

/**
 * MenuPageFixure
 *
 * @author      Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package     Menus\Test\Fixture
 */
class LanguagesPageFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @author   Shohei Nakajima <nakajimashouhei@gmail.com>
 * @var      array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'page_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'language_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'name' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @author   Shohei Nakajima <nakajimashouhei@gmail.com>
 * @var      array
 */
	public $records = array(
		array(
			'id' => 1,
			'page_id' => 1,
			'language_id' => 1,
			'name' => 'Page1',
			'created_user_id' => 1,
			'created' => '2014-08-04 04:47:08',
			'modified_user_id' => 1,
			'modified' => '2014-08-04 04:47:08'
		),
		array(
			'id' => 2,
			'page_id' => 1,
			'language_id' => 2,
			'name' => 'ページ1',
			'created_user_id' => 2,
			'created' => '2014-08-04 04:47:08',
			'modified_user_id' => 2,
			'modified' => '2014-08-04 04:47:08'
		),
		array(
			'id' => 3,
			'page_id' => 2,
			'language_id' => 2,
			'name' => 'ページ2',
			'created_user_id' => 2,
			'created' => '2014-08-04 04:47:08',
			'modified_user_id' => 2,
			'modified' => '2014-08-04 04:47:08'
		),
	);

}
