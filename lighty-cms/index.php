<?php
include_once ('./inc/bootstrap.php');

if (!isset($_GET['page'])) {
    include_once ('./inc/main.php');
    include_once ('./tpl/main.tpl.php');
} else if ($_GET['page'] === 'cabinet') {
    include_once ('./inc/cabinet.php');
    include_once ('./tpl/cabinet.tpl.php');
} else if ($_GET['page'] === 'admin') {
    include_once ('./inc/admin.php');
    include_once ('./tpl/admin.tpl.php');
} else {
    include_once ('./inc/common.php');
    include_once ('./tpl/common.tpl.php');
}
?>