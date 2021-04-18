<?php
/**
 *
 * @package QuickStyle
 * @copyright (c) 2015 PayBas
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * Based on the original Prime Quick Style by Ken F. Innes IV (primehalo)
 *
 */

namespace paybas\quickstyle\event;

use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbb\language\language;

/**
 * Class listener
 *
 * @package paybas\quickstyle\event
 */
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string PHP extension */
	protected $phpEx;

	protected $enabled;
	protected $default_loc;
	protected $allow_guests;

	/**
	 * @var \phpbb\language\language
	 */
	protected $language;

	/**
	 * listener constructor.
	 *
	 * @param \phpbb\config\config              $config
	 * @param \phpbb\language\language          $language
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\request\request_interface  $request
	 * @param \phpbb\template\template          $template
	 * @param \phpbb\user                       $user
	 * @param                                   $root_path
	 * @param                                   $phpEx
	 */
	public function __construct(config $config, language $language, driver_interface $db,
			request_interface $request, template $template, user $user, $root_path, $phpEx)
	{
		$this->config = $config;
		$this->language = $language;
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->phpEx = $phpEx;

		// Setup the common settings
		$this->enabled = ($config['override_user_style'] == true) ? false : true;
		$this->default_loc = isset($config['quickstyle_default_loc']) ? (int) $config['quickstyle_default_loc'] : true;
		$this->allow_guests = isset($config['quickstyle_allow_guests']) ? (int) $config['quickstyle_allow_guests'] : true;
	}

	/**
	 * @return array
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'core.page_header_after'         => 'select_style_form',
			'core.ucp_display_module_before' => 'switch_style',
			'core.user_setup'                => 'set_guest_style',
		);
	}

	/**
	 * handler for core.page_header_after
	 * Assign the style switcher form variables
	 */
	public function select_style_form()
	{
		if ($this->enabled && ($this->allow_guests || $this->user->data['is_registered']))
		{
			$current_style = ($this->user->data['is_registered']) ? $this->user->data['user_style'] : $this->request_cookie('style', $this->user->data['user_style']);
			$style_options = style_select($this->request->variable('style', (int) $current_style));

			if (substr_count($style_options, '<option') > 1)
			{
				$this->language->add_lang('quickstyle', 'paybas/quickstyle');

				$redirect = 'redirect=' . urlencode(str_replace(array('&amp;', '../'), array('&', ''), build_url('style'))); // Build redirect URL
				$action = append_sid("{$this->root_path}ucp.$this->phpEx", 'i=prefs&amp;mode=personal&amp;' . $redirect); // Build form submit URL + redirect
				$action = preg_replace('/(?:&amp;|(\?))style=[^&]*(?(1)&amp;|)?/i', "$1", $action); // Remove style= param if it exists
				$this->template->assign_vars(array(
					'S_QUICK_STYLE_ACTION' => $action,
					'S_QUICK_STYLE_OPTIONS' => ($this->config['override_user_style']) ? '' : $style_options,
					'S_QUICK_STYLE_DEFAULT_LOC' => $this->default_loc,
				));
			}
		}
	}

	/**
	 * handler for core.ucp_display_module_before
	 * The UCP event is only triggered when the user is logged in, so we have to set the guest cookie using some other event
	 */
	public function switch_style()
	{
		if ($this->enabled && $style = $this->request->variable('quick_style', 0))
		{
			$style = ($this->config['override_user_style']) ? $this->config['default_style'] : $style;

			$sql = 'UPDATE ' . USERS_TABLE . ' SET user_style = ' . (int) $style . ' WHERE user_id = ' . (int) $this->user->data['user_id'];
			$this->db->sql_query($sql);

			// Redirect the user back to their last viewed page (non-AJAX requests)
			$redirect = urldecode($this->request->variable('redirect', $this->user->data['session_page']));
			$redirect = reapply_sid($redirect);
			redirect($redirect);
		}
	}

	/**
	 * handler for core.user_setup
	 * Handle style switching by guests (not logged in visitors)
	 * @param $event
	 */
	public function set_guest_style($event)
	{
		if ($this->enabled && $this->allow_guests && $this->user->data['user_id'] == ANONYMOUS)
		{
			$style = $event['style_id'];

			// Set the style to display
			$event['style_id'] = ($style) ? $style : $this->request_cookie('style', (int)($this->user->data['user_style']));

			// Set the cookie (and redirect) when the style is switched
			if ($style = $this->request->variable('quick_style', 0))
			{
				$style = ($this->config['override_user_style']) ? $this->config['default_style'] : $style;

				$this->user->set_cookie('style', $style, 0);

				// Redirect the user back to their last viewed page (non-AJAX requests)
				$redirect = urldecode($this->request->variable('redirect', $this->user->data['session_page']));
				$redirect = reapply_sid($redirect);
				redirect($redirect);
			}
		}
	}

	/**
	 * @param      $name
	 * @param null $default
	 * @return mixed
	 */
	private function request_cookie($name, $default = null)
	{
		$name = $this->config['cookie_name'] . '_' . $name;
		return $this->request->variable($name, $default, false, 3);
	}
}
