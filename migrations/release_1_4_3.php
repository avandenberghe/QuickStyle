<?php
/**
 *
 * @package Quick Style
 * @copyright (c) 2015 PayBas
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * Based on the original Prime Quick Style by Ken F. Innes IV (primehalo)
 *
 */

namespace paybas\quickstyle\migrations;

use phpbb\db\migration\migration;

/**
 * Class release_1_4_3
 *
 * @package paybas\quickstyle\migrations
 */
class release_1_4_3 extends migration
{

	/**
	 * @return bool
	 */
	public function effectively_installed()
	{
		return isset($this->config['quickstyle_version']) && version_compare($this->config['quickstyle_version'], '1.4.3', '>=');
	}

	/**
	 * @return array
	 */
	static public function depends_on()
	{
		return array(
			'\paybas\quickstyle\migrations\release_1_4_1',
		);
	}

	/**
	 * @return array
	 */
	public function update_data()
	{
		return array(
			array('config.update', array('quickstyle_version', '1.4.3')),
		);
	}
}
