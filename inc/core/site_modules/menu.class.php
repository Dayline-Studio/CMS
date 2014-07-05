<?php

class Menu extends MainModule
{

    public $subfrom = 0;

    public function render()
    {
        $te = new TemplateEngine('site/modules/menu_show');
        $sites = $this->get_site_list($this->subfrom);
        if (sizeof($sites) < 1) {
            $sites[] = array('title' => 'No subsites here');
        }
        $te->addArr('list', $sites);
        return $te->render();
    }

    public function render_admin()
    {
        $te = new TemplateEngine('site/modules/menu_admin');
        $te->addArr('list', $this->get_site_list(-1),$this->subfrom);
        return $te->render();
    }

    private function get_site_list($from, $select = -1) {
        $sm = new SiteManager('*');
        $list = [];
        foreach ($sm->sites as $site) {
            if ($site->subfrom == $from || $from < 0) {
                $select = $site->id == $select ? 'selected' : '';
                $list[] = array('title' => $site->title, 'id' => $site->id, 'select' => $select);
            }
        }
        return $list;
    }
}