<?php

$host = explode(':',$_SERVER['HTTP_HOST']);
$url = "http://".$host[0].":".$view['TCPPort'];

echo $view->header()->setAttribute('template', $T('ntopng_title'));

echo $view->fieldsetSwitch('status', 'enabled',  $view::FIELDSETSWITCH_CHECKBOX)
    ->setAttribute('template', $T('ntopng_status'))
    ->setAttribute('uncheckedValue', 'disabled')
    ->insert($view->textInput('TCPPort'))
    ->insert($view->fieldsetSwitch('Authentication', 'enabled', $view::FIELDSETSWITCH_EXPANDABLE)
        ->insert($view->textLabel()->setAttribute('template',$T('username_label').": admin"))
        ->insert($view->panel()->insert($view->textLabel()->setAttribute('template',$T('default_password_label').": admin")))
    )
    ->insert($view->fieldsetSwitch('Authentication', 'disabled'))
    ->insert($view->panel()->insert($view->literal("URL: <a href='$url' target='_blank'>$url</a>")->setAttribute('hsc', FALSE)))
;

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP);
