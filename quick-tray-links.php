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
            'onAdminMenu' => ['onAdminMenu', 0]
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
}
