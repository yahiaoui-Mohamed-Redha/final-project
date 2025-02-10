<?php
include 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:index.php'); // Redirect to login if not logged in
    exit();
}

$userId = $_SESSION['user_id'];

// Handle form submission to create a new form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_name'])) {
    $formName = $_POST['form_name'];
    $stmt = $pdo->prepare("INSERT INTO form (user_id, form_name) VALUES (:user_id, :form_name)");
    $stmt->execute([':user_id' => $userId, ':form_name' => $formName]);
    $formId = $pdo->lastInsertId(); // Get the ID of the newly created form
}

// Fetch existing forms for the user
$stmt = $pdo->prepare("SELECT * FROM form WHERE user_id = :user_id");
$stmt->execute([':user_id' => $userId]);
$existingForms = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Form Builder</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .input-button {
            margin-right: 10px;
            margin-bottom: 10px;
            padding: 10px 15px;
            cursor: pointer;
            border: none;
            background-color: #007BFF;
            color: white;
            border-radius: 5px;
        }
        .input-button:hover {
            background-color: #0056b3;
        }
        .draggable {
            cursor: move;
            margin-bottom: 10px;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .placeholder {
            border: 1px dashed #007BFF;
            background-color: #e9e9ff;
            margin-bottom: 10px;
            height: 40px; /* Adjust height for visibility */
        }
        .form-preview {
            border: 1px solid #ccc;
            padding: 10px;
            margin-top: 20px;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <h1>Simple Form Builder</h1>
    

    <form id="inputForm" method="POST" action="index.php">
        <select id="inputType" name="inputType" onchange="showInputArea()">
            <option value="">Select Input Type</option>
            <option value="checkbox">Checkbox</option>
            <option value="text">Text Input</option>
            <option value="radio">Radio Button</option>
            <option value="multiple">Multiple Choice</option>
        </select>

        <div id="inputArea" style="display:none;">
            <textarea id="inputText" name="inputText" rows="3" cols="50" placeholder="Enter your question or option here..."></textarea><br>
            <button type="submit" class="input-button">Add to Form</button>
            <button type="button" class="input-button" onclick="clearForm()">Clear Form</button>
        </div>
    </form>

    <div id="output">
        <?php
        session_start();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $inputType = $_POST['inputType'];
            $inputText = $_POST['inputText'];

            if (!empty($inputText)) {
                // Create a unique identifier for each form element
                $formElement = "<div class='draggable' draggable='true' ondragstart='dragStart(event)' ondragover='dragOver(event)' ondrop='drop(event)' ondragend='dragEnd(event)'>";
                $formElement .= "<button onclick='deleteElement(this)'>Delete</button>";

                if ($inputType === 'checkbox') {
                    $formElement .= "<input type='checkbox' id='$inputText'><label for='$inputText'>$inputText</label>";
                } elseif ($inputType === 'text') {
                    $formElement .= "<label>$inputText</label><input type='text' placeholder='$inputText'>";
                } elseif ($inputType === 'radio') {
                    $formElement .= "<input type='radio' name='options' id='$inputText'><label for='$inputText'>$inputText</label>";
                } elseif ($inputType === 'multiple') {
                    $formElement .= "<label>$inputText</label><input type='text' placeholder='Option'>";
                }

                $formElement .= "</div>";

                // Store in session to persist data across requests
                if (!isset($_SESSION['form_elements'])) {
                    $_SESSION['form_elements'] = [];
                }
                $_SESSION['form_elements'][] = $formElement;
            }
        }

        // Display stored form elements
        if (isset($_SESSION['form_elements'])) {
            foreach ($_SESSION['form_elements'] as $element) {
                echo $element;
            }
        }
        ?>
    </div>

    <h2>Form Preview</h2>
    <button onclick="window.location.href='shows_form.php'">View Loaded Form</button>

    <script>
        function showInputArea() {
            const inputType = document.getElementById('inputType').value;
            document.getElementById('inputArea').style.display = inputType ? 'block' : 'none';
        }

        function clearForm() {
            document.getElementById('output').innerHTML = '';
            document.getElementById('formPreview').innerHTML = '';
            // Clear session data
            fetch('clear_session.php');
        }

        function deleteElement(button) {
            const element = button.parentElement;
            element.parentElement.removeChild(element);
        }

        function dragStart(event) {
            currentDragElement = event.target;
            event.dataTransfer.effectAllowed = "move";
            event.target.classList.add('dragging');
            const placeholder = document.createElement('div');
            placeholder.classList.add('placeholder');
            event.target.parentNode.insertBefore(placeholder, event.target.nextSibling);
        }

        function dragOver(event) {
            event.preventDefault();
            event.dataTransfer.dropEffect = "move";
        }

        function drop(event) {
            event.preventDefault();
            const dropTarget = event.target;

            if (dropTarget.classList.contains('draggable') && dropTarget !== currentDragElement) {
                dropTarget.insertAdjacentElement('beforebegin', currentDragElement);
            }

            const placeholder = document.querySelector('.placeholder');
            if (placeholder) {
                placeholder.parentNode.removeChild(placeholder);
            }
        }

        function dragEnd(event) {
            event.target.classList.remove('dragging');
            const placeholder = document.querySelector('.placeholder');
            if (placeholder) {
                placeholder.parentNode.removeChild(placeholder);
            }
        }
    </script>
</body>
</html>