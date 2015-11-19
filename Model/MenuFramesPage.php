<?php
/**
 * MenuFramesPage Model
 *
 * @property Page $Page
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MenusAppModel', 'Menus.Model');

/**
 * Summary for MenuFramesPage Model
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Menus\Model
 */
class MenuFramesPage extends MenusAppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array();

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Page' => array(
			'className' => 'Pages.Page',
			'foreignKey' => 'page_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * Called during validation operations, before validation. Please note that custom
 * validation rules can be defined in $validate.
 *
 * @param array $options Options passed from Model::save().
 * @return bool True if validate operation should continue, false to abort
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#beforevalidate
 * @see Model::save()
 */
	public function beforeValidate($options = array()) {
		$this->validate = Hash::merge($this->validate, array(
			'frame_key' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'message' => __d('net_commons', 'Invalid request.'),
					//'allowEmpty' => false,
					//'required' => false,
					//'last' => false, // Stop validation after this rule
					//'on' => 'create', // Limit validation to 'create' or 'update' operations
				),
			),
		));

		return parent::beforeValidate($options);
	}

/**
 * メニューデータ取得処理
 *
 * @param array $options Findのオプション
 * @return array Menu data
 */
	public function getMenuData($options = array()) {
		$this->loadModels([
			'LanguagesPage' => 'Pages.LanguagesPage',
		]);

		//Menuデータ取得
		$options = Hash::merge(array(
			'recursive' => -1,
			'fields' => array(
				$this->Page->alias . '.*',
				$this->LanguagesPage->alias . '.*',
				$this->alias . '.*',
			),
			'conditions' => array(
				$this->Page->alias . '.room_id' => Current::read('Room.id'),
				//$this->LanguagesPage->alias . '.language_id' => Current::read('Language.id'),
				//'OR' => array(
				//	$this->alias . '.is_hidden' => false,
				//	$this->alias . '.is_hidden IS NULL',
				//)
			),
			'joins' => array(
				array(
					'table' => $this->LanguagesPage->table,
					'alias' => $this->LanguagesPage->alias,
					'type' => 'INNER',
					'conditions' => array(
						$this->Page->alias . '.id' . ' = ' . $this->LanguagesPage->alias . ' .page_id',
						$this->LanguagesPage->alias . '.language_id' => Current::read('Language.id'),
					),
				),
				array(
					'table' => $this->table,
					'alias' => $this->alias,
					'type' => 'LEFT',
					'conditions' => array(
						$this->Page->alias . '.id' . ' = ' . $this->alias . ' .page_id',
						$this->alias . '.frame_key' => Current::read('Frame.key')
					),
				),
			),
			'order' => array(
				$this->Page->alias . '.lft' => 'asc',
			)
		), $options);

		$menus = $this->Page->find('all', $options);
		return $menus;
	}

}