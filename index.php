<?php
include 'app/config.php';
session_start();

// Initialize message array
$message = [];

// Check if "Remember Me" cookie exists and pre-fill the form
if (isset($_COOKIE['remember_username'])) {
    $rememberedUsername = $_COOKIE['remember_username'];
} else {
    $rememberedUsername = '';
}

// Get the selected language from the cookie
$language = $_COOKIE['language'] ?? 'fr';

// Set the cache headers for images and CSS
header('Cache-Control: max-age=31536000, public');
header('Expires: ' . gmdate('D, d M Y H:i:s T', time() + 31536000));

// Define the cache directory
$cacheDir = 'cache/';

// Create the cache directory if it doesn't exist
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}

// Cache images
$imageFiles = array('oie_M5SLKyrEbkDJ.png',);
foreach ($imageFiles as $imageFile) {
    $imagePath = 'assets/image/' . $imageFile;
    $cacheImagePath = $cacheDir . $imageFile;
    if (!file_exists($cacheImagePath)) {
        copy($imagePath, $cacheImagePath);
    }
}

// Cache icon
$imageFiles = array('algerie-poste-logo@logotyp.us.svg');
foreach ($imageFiles as $imageFile) {
    $imagePath = 'assets/icon/' . $imageFile;
    $cacheImagePath = $cacheDir . $imageFile;
    if (!file_exists($cacheImagePath)) {
        copy($imagePath, $cacheImagePath);
    }
}

// Cache CSS
$cssFiles = array('output.css');
foreach ($cssFiles as $cssFile) {
    $cssPath = 'src/' . $cssFile;
    $cacheCssPath = $cacheDir . $cssFile;
    if (!file_exists($cacheCssPath)) {
        copy($cssPath, $cacheCssPath);
    }
}

if (isset($_POST['submit'])) {
    $login_input = $_POST['login_input'];
    $login_input = filter_var($login_input, FILTER_SANITIZE_STRING);
    $pass = md5($_POST['password']);
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);

    $select = $conn->prepare("SELECT u.*, r.role_nom FROM `Users` u INNER JOIN `Roles` r ON u.role_id = r.role_id WHERE (u.email = ? OR u.username = ?) AND u.password = ?");
    $select->execute([$login_input, $login_input, $pass]);
    $row = $select->fetch(PDO::FETCH_ASSOC);

    if ($select->rowCount() > 0) {
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['user_role'] = $row['role_nom'];

        // Handle "Remember Me" functionality
        if (isset($_POST['rememberMe'])) {
            // Set cookie to remember the username for 30 days
            setcookie('remember_username', $login_input, time() + (30 * 24 * 60 * 60), '/');
        } else {
            // Delete the cookie if "Remember Me" is not checked
            setcookie('remember_username', '', time() - 3600, '/');
        }

        // Redirect to the corresponding page based on the user's role
        if ($row['role_nom'] == 'Admin') {
            header('location: dist/admin_page.php');
            exit();
        } elseif ($row['role_nom'] == 'Technicien') {
            header('location: dist/technicien_page.php');
            exit();
        } elseif ($row['role_nom'] == 'Receveur') {
            header('location: dist/receveur_page.php');
            exit();
        } else {
            $message[] = 'no_user_found';
        }
    } else {
        $message[] = 'incorrect_email_or_password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="cache/algerie-poste-logo@logotyp.us.svg">
    <title>Login - Algérie Poste</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/3.26.0/tabler-icons.min.css" integrity="sha512-k9iJhTcDc/0fp2XLBweIJjHuQasnXicVPXbUG0hr5bB0/JqoTYEFeCdQj4aJTg50Gw6rBJiHfWJ8Y+AeF1Pd3A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="cache/output.css">
    <style>
        /* RTL support for Arabic */
        [dir="rtl"] .text-right {
            text-align: left;
        }
    </style>
</head>

<body class="font-bodyfont bg-[#f8f8f8]">
    <div class="font-[sans-serif]">
        <div class="grid lg:grid-cols-2 gap-4 max-lg:gap-12 bg-gradient-to-r from-sky-800 via-sky-600 to-sky-800 sm:px-8 px-4 py-12 h-[320px]">
            <div>
                <div class="flex items-center justify-center h-max w-max bg-white rounded-xl px-6 pt-3 pb-2 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.3)]">
                    <a href="javascript:void(0)">
                        <img src="cache/oie_M5SLKyrEbkDJ.png" alt="logo" class="w-40" />
                    </a>
                </div>
                <div class="max-w-lg mt-16 max-lg:hidden">
                    <h3 id="welcomeTitle" class="text-3xl font-bold text-white">Sign in</h3>
                    <p id="welcomeText" class="text-sm mt-4 text-white">Embark on a seamless journey as you sign in to your account. Unlock a realm of opportunities and possibilities that await you.</p>
                </div>
            </div>

            <div class="bg-white rounded-xl sm:px-6 px-4 py-8 max-w-md w-full h-max shadow-[0_2px_10px_-3px_rgba(6,81,237,0.3)] max-lg:mx-auto">
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="mb-8">
                        <h3 id="loginTitle" class="text-3xl font-extrabold text-gray-800">Sign in</h3>
                    </div>

                    <!-- Username/Email Field -->
                    <div>
                        <label id="usernameLabel" class="text-gray-800 text-sm mb-2 block">User   name</label>
                        <div class="relative flex items-center">
                            <input name="login_input" type="text" required class="w-full text-sm text-gray-800 border border-gray-300 px-4 py-3 rounded-md outline-blue-600" placeholder="Enter your username or email" value="<?php echo isset($_COOKIE['remember_username']) ? htmlspecialchars($_COOKIE['remember_username']) : ''; ?>" />
                            <svg id="usernameIcon" xmlns="http://www.w3.org/2000/svg" fill="#bbb" stroke="#bbb" class="w-[18px] h-[18px] absolute right-4" viewBox="0 0 24 24">
                                <circle cx="10" cy="7" r="6" data-original="#000000"></circle>
                                <path d="M14 15H6a5 5 0 0 0-5 5 3 3 0 0 0 3 3h12a3 3 0 0 0 3-3 5 5 0 0 0-5-5zm8-4h-2.59l.3-.29a1 1 0 0 0-1.42-1.42l-2 2a1 1 0 0 0 0 1.42l2 2a1 1 0 0 0 1.42 0 1 1 0 0 0 0-1.42l-.3-.29H22a1 1 0 0 0 0-2z" data-original="#000000"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="mt-4">
                        <label id="passwordLabel" class="text-gray-800 text-sm mb-2 block">Password</label>
                        <div class="relative flex items-center">
                            <input name="password" type="password" required class="w-full text-sm text-gray-800 border border-gray-300 px-4 py-3 rounded-md outline-blue-600" placeholder="Enter password" />
                            <svg id="togglePassword" xmlns="http://www.w3.org/2000/svg" fill="#bbb" stroke="#bbb" class="w-[18px] h-[18px] absolute right-4 cursor-pointer" viewBox="0 0 128 128">
                                <path d="M64 104C22.127 104 1.367 67.496.504 65.943a4 4 0 0 1 0-3.887C1.367 60.504 22.127 24 64 24s62.633 36.504 63.496 38.057a4 4 0 0 1 0 3.887C126.633 67.496 105.873 104 64 104zM8.707 63.994C13.465 71.205 32.146 96 64 96c31.955 0 50.553-24.775 55.293-31.994C114.535 56.795 95.854 32 64 32 32.045 32 13.447 56.775 8.707 63.994zM64 88c-13.234 0-24-10.766-24-24s10.766-24 24-24 24 10.766 24 24-10.766 24-24 24zm0-40c-8.822 0-16 7.178-16 16s7.178 16 16 16 16-7.178 16-16-7.178-16-16-16z" data-original="#000000"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="mt-4 flex items-center justify-between">
                        <div class="flex items-center">
                            <input type="checkbox" id="rememberMe" name="rememberMe" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?php echo isset($_COOKIE['remember_username']) ? 'checked' : ''; ?> />
                            <label id="rememberMeLabel" class="ml-2 mr-2 text-sm text-gray-800">Remember me</label>
                        </div>
                        <div>
                            <a id="forgotPasswordLink" href="javascript:void(0);" class="text-blue-600 text-sm font-semibold hover:underline">Forgot your password?</a>
                        </div>
                    </div>

                    <!-- Login Button -->
                    <div class="mt-8">
                        <button type="submit" name="submit" id="loginButton" class="w-full shadow-xl py-2.5 px-4 text-sm font-semibold rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">Log in</button>
                    </div>
                </form>

                <!-- Language Selector -->
                <div class="mt-6 text-center">
                    <select id="languageSelector" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="en">English</option>
                        <option value="fr">Français</option>
                        <option value="ar">العربية</option>
                        <option value="ru">Русский</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Functionality -->
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
        // Language Translations
        const translations = {
            en: {
                welcomeTitle: "Sign in",
                welcomeText: "Embark on a seamless journey as you sign in to your account. Unlock a realm of opportunities and possibilities that await you.",
                loginTitle: "Sign in",
                usernameLabel: "User   name",
                passwordLabel: "Password",
                rememberMeLabel: "Remember me",
                forgotPasswordLink: "Forgot your password?",
                loginButton: "Log in",
                usernamePlaceholder: "Enter your username or email",
                passwordPlaceholder: "Enter password",
                incorrect_email_or_password: "Incorrect email or password!",
                no_user_found: "No user found!",
            },
            fr: {
                welcomeTitle: "Connexion",
                welcomeText: "Entamez un voyage sans encombre en vous connectant à votre compte. Débloquez un univers d'opportunités et de possibilités qui vous attendent.",
                loginTitle: "Connexion",
                usernameLabel: "Nom d'utilisateur",
                passwordLabel: "Mot de passe",
                rememberMeLabel: "Se souvenir de moi",
                forgotPasswordLink: "Mot de passe oublié ?",
                loginButton: "Se connecter",
                usernamePlaceholder: "Entrez votre nom d'utilisateur ou email",
                passwordPlaceholder: "Entrez votre mot de passe",
                incorrect_email_or_password: "Email ou mot de passe incorrect !",
                no_user_found: "Aucun utilisateur trouvé !",
            },
            ar: {
                welcomeTitle: "تسجيل الدخول",
                welcomeText: "ابدأ رحلة سلسة عند تسجيل الدخول إلى حسابك. افتح عالمًا من الفرص والإمكانيات التي تنتظرك.",
                loginTitle: "تسجيل الدخول",
                usernameLabel: "اسم المستخدم",
                passwordLabel: "كلمة المرور",
                rememberMeLabel: "تذكرني",
                forgotPasswordLink: "هل نسيت كلمة المرور؟",
                loginButton: "تسجيل الدخول",
                usernamePlaceholder: "أدخل اسم المستخدم أو البريد الإلكتروني",
                passwordPlaceholder: "أدخل كلمة المرور",
                incorrect_email_or_password: "البريد الإلكتروني أو كلمة المرور غير صحيحة !",
                no_user_found: "لم يتم العثور على أي مستخدم !",
            },
            ru: {
                welcomeTitle: "Вход",
                welcomeText: "Войдите в свою учетную запись, чтобы открыть мир возможностей и перспектив.",
                loginTitle: "Вход",
                usernameLabel: "Имя пользователя",
                passwordLabel: "Пароль",
                rememberMeLabel: "Запомнить меня",
                forgotPasswordLink: "Забыли пароль?",
                loginButton: "Войти",
                usernamePlaceholder: "Введите имя пользователя или email",
                passwordPlaceholder: "Введите пароль",
                incorrect_email_or_password: "Неверный email или пароль!",
                no_user_found: "Пользователь не найден!",
            },
        };

        // Function to update the language
        function updateLanguage(lang) {
            const isArabic = lang === "ar";
            document.documentElement.dir = isArabic ? "RTL" : "ltr";

            // Update text content
            document.getElementById("welcomeTitle").textContent = translations[lang].welcomeTitle;
            document.getElementById("welcomeText").textContent = translations[lang].welcomeText;
            document.getElementById("loginTitle").textContent = translations[lang].loginTitle;
            document.getElementById("usernameLabel").textContent = translations[lang].usernameLabel;
            document.getElementById("passwordLabel").textContent = translations[lang].passwordLabel;
            document.getElementById("rememberMeLabel").textContent = translations[lang].rememberMeLabel;
            document.getElementById("forgotPasswordLink").textContent = translations[lang].forgotPasswordLink;
            document.getElementById("loginButton").textContent = translations[lang].loginButton;
            document.querySelector('input[name="login_input"]').placeholder = translations[lang].usernamePlaceholder;
            document.querySelector('input[name="password"]').placeholder = translations[lang].passwordPlaceholder;

            // Adjust SVG positions for RTL
            const usernameIcon = document.getElementById("usernameIcon");
            const togglePassword = document.getElementById("togglePassword");
            if (isArabic) {
                usernameIcon.classList.remove("right-4");
                usernameIcon.classList.add("left-4");
                togglePassword.classList.remove("right-4");
                togglePassword.classList.add("left-4");
            } else {
                usernameIcon.classList.remove("left-4");
                usernameIcon.classList.add("right-4");
                togglePassword.classList.remove("left-4");
                togglePassword.classList.add("right-4");
            }
        }

        // Function to set the language cookie
        function setLanguageCookie(lang) {
            const date = new Date();
            date.setTime(date.getTime() + (30 * 24 * 60 * 60 * 1000)); // 30 days
            document.cookie = `language=${lang}; expires=${date.toUTCString()}; path=/`;
        }

        // Function to get the language cookie
        function getLanguageCookie() {
            const cookies = document.cookie.split(";");
            for (let i = 0; i < cookies.length; i++) {
                const cookie = cookies[i].trim();
                if (cookie.startsWith("language=")) {
                    return cookie.substring(9);
                }
            }
            return "fr"; // Default language
        }

        // Language Selector Event Listener
        document.getElementById("languageSelector").addEventListener("change", function (e) {
            const selectedLanguage = e.target.value;
            updateLanguage(selectedLanguage);
            setLanguageCookie(selectedLanguage);
        });

        // View Password Toggle
        document.getElementById("togglePassword").addEventListener("click", function () {
            const passwordInput = document.querySelector('input[name="password"]');
            const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
            passwordInput.setAttribute("type", type);
        });

        // Initialize with fr as default
        const storedLanguage = getLanguageCookie();
        updateLanguage(storedLanguage);
        document.getElementById("languageSelector").value = storedLanguage;

        // Display messages
        <?php if (isset($_POST['submit']) && !empty($message)) : ?>
            const messages = <?php echo json_encode($message); ?>;
            const language = '<?php echo $language; ?>';
            messages.forEach((msg) => {
                const messageText = translations[language][msg];
                const messageElement = document.createElement("div");
                messageElement.classList.add("message", "fixed", "top-4", "right-4", "bg-red-500", "text-white", "px-4", "py-2", "rounded-md", "shadow-md", "flex", "items-center");
                messageElement.innerHTML = `<span>${messageText}</span><i class="fas fa-times ml-2 cursor-pointer" onclick="this.parentElement.remove();"></i>`;
                document.body.appendChild(messageElement);
            });
        <?php endif; ?>
    </script>
</body>

</html>