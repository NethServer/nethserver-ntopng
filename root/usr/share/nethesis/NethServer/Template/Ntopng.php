<?php

echo $view->header()->setAttribute('template', $T('ntopng_title'));

echo $view->fieldsetSwitch('status', 'enabled',  $view::FIELDSETSWITCH_CHECKBOX)
    ->setAttribute('template', $T('ntopng_status'))
    ->setAttribute('uncheckedValue', 'disabled')
    ->insert($view->textInput('TCPPort'))
    ->insert($view->fieldsetSwitch('Authentication', 'enabled', $view::FIELDSETSWITCH_EXPANDABLE)
        ->insert($view->textLabel()->setAttribute('template',$T('username_label').": admin"))
        ->insert($view->textInput('Password'))
    )
    ->insert($view->fieldsetSwitch('Authentication', 'disabled'));


$url = "http://".$view['FQDN'].":".$view['TCPPort'];
echo "<p style='margin-bottom: 5px'>URL: <a href='$url' target='_blank'>$url</a></p>";

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP);
