<?php

echo $view->header()->setAttribute('template', $T('ntopng_title'));

echo $view->fieldsetSwitch('status', 'enabled',  $view::FIELDSETSWITCH_CHECKBOX)
    ->setAttribute('template', $T('ntopng_status'))
    ->setAttribute('uncheckedValue', 'disabled')
    ->insert($view->textInput('TCPPort'))
    ->insert($view->textInput('Password'));

$url = "http://".$view['FQDN'].":".$view['TCPPort'];
echo "<p>URL: <a href='$url' target='_blank'>$url</a></p>";
echo "<p>".$T('username_label').": admin";
echo "<p>".$T('Password_label').": ".$view['Password'];

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP);
