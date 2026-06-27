<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class QuickTrayLinksPlugin
 * @package Grav\Plugin
 */
class QuickTrayLinksPlugin extends Plugin
{
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            // Classic admin quick tray (top-right icons).
            'onAdminMenu' => ['onAdminMenu', 0],
            // Admin2 (admin-next) menubar — the equivalent top-right tray.
            'onApiMenubarItems' => ['onApiMenubarItems', 0],
        ];
    }

    public function onAdminMenu()
    {
        $counter = 0;

        foreach ($this->grav['config']->get('plugins.quick-tray-links.links') as $link) {
            if ($link == null || $link == false) {
                continue;
            }
            $options = [
                'icon' => $link['icon'],
                'route' => $link['link'],
                'hint' => isset($link['tooltip']) ? $link['tooltip'] : '',
                'authorize' => isset($link['authorize']) ? $link['authorize'] : ''

            ];
            if (isset($link['external']) and $link['external'] == true) {
                $options['target'] = '_blank';
            }
            $this->grav['twig']->plugins_quick_tray['QuickTrayLinks-' . $counter++] = $options;
        }
    }

    /**
     * Register the same links with the admin2 menubar so they appear in the
     * new admin's top-right tray. Each link renders as a plain anchor via the
     * menubar `href`/`target` contract (api >= the version that added external
     * link support); plain `route` would route through the SPA and break
     * external URLs.
     */
    public function onApiMenubarItems(Event $e)
    {
        $links = $this->grav['config']->get('plugins.quick-tray-links.links');
        if (!is_array($links)) {
            return;
        }

        $items = $e['items'] ?? [];
        $counter = 0;

        foreach ($links as $link) {
            if ($link == null || $link == false || empty($link['link'])) {
                continue;
            }

            $external = isset($link['external']) && $link['external'] == true;
            $label = !empty($link['tooltip']) ? $link['tooltip'] : $link['link'];

            $item = [
                'id'     => 'quick-tray-links-' . $counter++,
                'plugin' => 'quick-tray-links',
                'label'  => $label,
                'icon'   => $this->normalizeIcon($link['icon'] ?? 'fa-question-circle'),
                'href'   => $link['link'],
            ];

            if ($external) {
                $item['target'] = '_blank';
            }

            $authorize = $this->normalizeAuthorize($link['authorize'] ?? null);
            if ($authorize !== null) {
                $item['authorize'] = $authorize;
            }

            $items[] = $item;
        }

        $e['items'] = $items;
    }

    /**
     * Normalize an iconpicker value ("fa fa-link", "fa-solid fa-link", "link")
     * down to the bare "fa-link" form the admin2 menubar expects.
     */
    protected function normalizeIcon(string $icon): string
    {
        $icon = preg_replace('/^(fa[a-z-]*\s+)+/', '', trim($icon));
        if ($icon === '') {
            $icon = 'question-circle';
        }
        return str_starts_with($icon, 'fa-') ? $icon : 'fa-' . $icon;
    }

    /**
     * The acl_picker stores an access map (['admin.super' => true, ...]). The
     * menubar `authorize` contract wants a string or a flat any-of list of
     * permission names, so reduce the map to the keys granted true.
     */
    protected function normalizeAuthorize($authorize)
    {
        if (is_string($authorize)) {
            return $authorize !== '' ? $authorize : null;
        }
        if (is_array($authorize)) {
            $perms = array_keys(array_filter($authorize));
            return $perms !== [] ? $perms : null;
        }
        return null;
    }
}
