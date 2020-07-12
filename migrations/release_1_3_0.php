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
 * Class release_1_3_0
 *
 * @package paybas\quickstyle\migrations
 */
class release_1_3_0 extends migration
{
	/**
	 * @return bool
	 */
	public function effectively_installed()
	{
		return isset($this->config['quickstyle_version']) && version_compare($this->config['quickstyle_version'], '1.3.0', '>=');
	}

	/**
	 * @return array
	 */
	public function update_data()
	{
		return array(
			array('if', array(
				(isset($this->config['primequickstyle_version'])),
				array('config.remove', array('primequickstyle_version')),
			)),

			array('config.add', array('quickstyle_version', '1.3.0')),
			array('config.add', array('quickstyle_default_loc', '1')),
			array('config.add', array('quickstyle_allow_guests', '1')),

			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'QUICK_STYLE'
			)),

			array('module.add', array(
				'acp',
				'QUICK_STYLE',
				array(
					'module_basename'	=> '\paybas\quickstyle\acp\quickstyle_module',
					'modes'	=> array('settings'),
				),
			)),
		);
	}
}
