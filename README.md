# php_injector

For when web shells do not work due to disabled PHP functions, this is the next best thing. PHP injection allows you to inject custom and templated PHP code to be executed server-side.

In many cases, a typical web shell or PHP reverse shell leverages functions like `system`, `exec`, `shell_exec`, or `passthru` to execute system commands on the server. However, some web servers are configured with security measures that disable these functions, effectively preventing standard shell commands from being executed. This script provides an alternative by allowing you to inject and execute PHP code directly, bypassing the limitations imposed by disabled system command functions. It is particularly useful in penetration testing scenarios where system-level access is restricted but the server still interprets PHP.

## Features

- **Base64-Encoded Command Execution:** Safely inject and execute PHP payloads server-side.
- **Prebuilt Templates:** Includes templates for common tasks:
  - Directory listing and file reading.
  - MySQL database exploration and WordPress user data extraction.
  - File uploads and shell execution testing.
  - Disabled functions enumeration and PHP module listing.
  - Environment variable inspection and localhost port scanning.
- **User-Friendly Interface:**
  - Collapsible sections for commands, templates, and output.
  - Auto-expanding text areas for easier editing and visibility.
- **Error Handling:** Captures errors and outputs them gracefully.
- **Prefill Feature:** Quickly reuse the last executed command.

## Usage

1. Deploy the `php_injector.php` file on the target server.
2. Access the script through a web browser.
3. Enter your custom PHP code or use a prebuilt template.
4. Execute the command and review the output.

## Templates Overview

The following templates are included for streamlined operations:

- **List Directory:** Enumerate files in a specified directory.
- **Read File:** Access the contents of a file, such as `wp-config.php`.
- **MySQL View Databases:** Discover databases and tables.
- **Dump WordPress Users:** Extract WordPress user hashes and roles.
- **PHP Info:** Display the PHP configuration of the server.
- **File Upload:** Upload files to the server.
- **Test File Drop:** Test file creation and deletion.
- **Check Shell Execution:** Assess various shell execution methods.
- **List Disabled Functions:** Identify blacklisted PHP functions.
- **Installed PHP Modules:** Retrieve a list of installed PHP extensions.
- **Environment Variables:** View server environment variables.
- **Scan Localhost:** Scan open ports on localhost.
- **Outbound Web Request:** Test external web requests.

## To Do

- Add password protection to prevent unauthorised use of the script.

## Disclaimer

This tool is intended for authorised testing and research purposes only. Misuse of this tool is prohibited and may violate laws. Always obtain proper permissions before using.

