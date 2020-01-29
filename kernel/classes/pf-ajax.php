<?php

class PF_Ajax {
    private $private_actions;
    private $action;
    private $callback;
    public function __construct($action, $callback) {
        $this->private_actions = [];
        $this->action = $action;
        $this->callback = $callback;
        add_action('wp_ajax_' . $action, [$this, 'callback']);
        add_action('wp_ajax_nopriv_' . $action, [$this, 'private_action_error']);
    }
   
    public function pf_generate_supplier_order() {
        wp_send_json_success();
        die();
    }
    public function callback() {
        if (!$this->validate_ajax_action()) {
            die();
        }
        call_user_func($this->callback);
    }

    public function private_action_error() {
        wp_send_json_error(['message' => 'you need to be logged to execute this action']);
    }
    private function validate_ajax_action() {
        if (!wp_verify_nonce($_POST['nonce'], 'pictifly_admin')) {
            wp_send_json_error(['message' => 'Invalid Ajax Action']);
            die();
        }
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return true;
        } else {
            wp_send_json_error(['message' => 'Invalid Ajax Action']);
            die();
        }
    }

}