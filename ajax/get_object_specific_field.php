<?php
include_once('../../../inc/includes.php');


if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $object_name = $_POST['object'];
    $field_to_read = $_POST['field'];


    $object = new $object_name();
    $object->getFromDB($id);

    echo htmlspecialchars_decode($object->getField($field_to_read));
}
