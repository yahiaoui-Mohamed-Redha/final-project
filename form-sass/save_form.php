<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = $_POST['formData'];
    file_put_contents('saved_form.txt', $formData);
    echo "Form saved successfully!";
}