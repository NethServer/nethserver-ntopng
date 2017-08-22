<?php
    $tableId = $view->getUniqueId('TopTalkers');
    $tableTarget = $view->getClientEventTarget('TopTalkers');
    $ajaxUrl = json_encode($view->getModuleUrl() . '?act=TopTalkers');
    $view
        ->rejectFlag($view::INSET_FORM)
        ->includeFile('Nethgui/Js/jquery.nethgui.datatable.js')
        ->useFile('js/file-size.js')
        ->useFile('js/ip-address.js')
    ;

    echo $view->header()->setAttribute('template', $T('NtopngStatus_Header'));
    echo $view->buttonList($view::BUTTON_HELP)
        ->insert($view->button('Configure', $view::BUTTON_LINK)->setAttribute('value', $view->getModuleUrl() . '?act=Configure'))
    ;
?>
<div class="DataTable small-dataTable <?php echo $tableTarget ?>"><table id="<?php echo $tableId ?>">
<thead>
    <th data-options='{"formatter":"default"}'><?php echo $T('ntopng_column_name_label') ?></th>
    <th data-options='{"columnDefs":{"type":"ip-address"}}'><?php echo $T('ntopng_column_ip_label') ?></th>
    <th data-options='{"columnDefs":{"type":"file-size"}}'><?php echo $T('ntopng_column_thpt_label') ?></th>
    <th data-options='{"columnDefs":{"type":"file-size"}}'><?php echo $T('ntopng_column_traffic_label') ?></th>
    <th data-options='{"formatter":"default"}'><?php echo $T('ntopng_column_since_label') ?></th>
    <th data-options='{"formatter":"fmtButtonset"}'><?php echo $T('ntopng_column_actions_label') ?></th>
</thead>
<tbody></tbody>
</table>
</div>

<?php

$view->includeJavascript("
(function( \$ ) {
    var round = 0;
    $.Nethgui.Server.ajaxMessage({url: $ajaxUrl});
    $('.${tableTarget}').on('nethguiredirect', function(e, url) {
        window.location = url;
    }).on('nethguiupdateview', function(e, value) {
        if(round == 0) {
            $('#${tableId}').dataTable().fnSort([2, 'desc']);
        } else {
            $('#${tableId}').dataTable().fnDraw();
        }
        round ++;
        if($.isArray(value)) {
            window.setTimeout(function () {
                $.Nethgui.Server.ajaxMessage({url: $ajaxUrl});
            }, 5000);
        }
    });
}( jQuery ));
");

