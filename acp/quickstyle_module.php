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

namespace paybas\quickstyle\acp;

/**
 * Class quickstyle_module
 *
 * @package paybas\quickstyle\acp
 */
class quickstyle_module
{
	public $u_action;

	/**
	 * @param $id
	 * @param $mode
	 */
	public function main($id, $mode)
	{
		global $phpbb_container;
        $config = $phpbb_container->get('config');
        $template = $phpbb_container->get('template');
        $request = $phpbb_container->get('request');
        $language = $phpbb_container->get('language');

		$this->tpl_name = 'acp_quickstyle';
		$this->page_title = $language->lang('QUICK_STYLE');

		$form_key = 'acp_quickstyle';
		add_form_key($form_key);

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key($form_key))
			{
				trigger_error('FORM_INVALID');
			}

			$quickstyle_default_loc = $request->variable('quickstyle_default_loc', 0);
			$config->set('quickstyle_default_loc', $quickstyle_default_loc);

			$quickstyle_allow_guests = $request->variable('quickstyle_allow_guests', 0);
			$config->set('quickstyle_allow_guests', $quickstyle_allow_guests);

			trigger_error($language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}

		$template->assign_vars(array(
			'S_QUICKSTYLE_DEFAULT_LOC'  => isset($config['quickstyle_default_loc']) ? $config['quickstyle_default_loc'] : true,
			'S_QUICKSTYLE_ALLOW_GUESTS' => isset($config['quickstyle_allow_guests']) ? $config['quickstyle_allow_guests'] : true,
			'S_OVERRIDE_USER_STYLE'     => isset($config['override_user_style']) ? $config['override_user_style'] : false,
			'U_ACTION'                  => $this->u_action,
		));
	}
}
