<?php
/**
 * View/Elements/Menus/header/indexテスト用Viewファイル
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

$this->Menu->parentPageIds = array(Page::PUBLIC_ROOT_PAGE_ID);

?>

View/Elements/Menus/header/index

<?php echo $this->element('Menus.Menus/header/index');
