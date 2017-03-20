<?php

require_once(__DIR__.'/class.preamble.php');

call_user_func(function() {
    // Don't do this when invoked by wp-cli
    if (!class_exists('\WP_CLI\Runner')) {
        $modifier_factory = new \wpe\function_modifier\FunctionModifierFactory();
        $function_modifier = $modifier_factory->getFunctionModifier();

        $wpe_preamble = \wpe\plugin\Preamble::instance();
        $wpe_preamble->redefine($function_modifier, $_SERVER['REQUEST_URI']);
    }
});

