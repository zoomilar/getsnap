<?php

class GetSnap_Loader {
    protected $actions;

    public function __construct() {
        $this->actions = array();
    }

    public function add_action($hook, $component, $callback) {
        $this->actions = $this->add($this->actions, $hook, $component, $callback);
    }

    private function add($hooks, $hook, $component, $callback) {
        // code for adding hooks
        return $hooks;
    }

    public function run() {
        foreach ($this->actions as $hook) {
            add_action($hook['hook'], array($hook['component'], $hook['callback']));
        }
    }
}
