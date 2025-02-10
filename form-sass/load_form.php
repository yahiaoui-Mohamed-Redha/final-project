<?php
if (file_exists('saved_form.txt')) {
    $formData = file_get_contents('saved_form.txt');
    $formElements = explode('||', $formData);
    foreach ($formElements as $element) {
        echo $element;
    }
} else {
    echo "<p>No form elements found. Please create a form.</p>";
}