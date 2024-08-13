# Elementor & Customize Backup Plugin

## Overview

The **Elementor & Customize Backup** plugin is a WordPress plugin designed to create a backup of your Elementor designs and Customize settings. The backup is saved as a JSON file, which can later be used to restore the settings or transfer them to another site.

## Features

- **Backup Elementor Data**: Save all posts, post metadata, and options related to Elementor into a JSON file.
- **Backup Customize Settings**: Save all customization settings related to the active theme into the same JSON file.
- **Simple User Interface**: A dedicated page in the WordPress admin dashboard allows you to create the backup with a single click.

## How It Works

### Backup Process

1. **Elementor Data**:
   - Retrieves all posts and metadata where Elementor is used.
   - Collects all options related to Elementor.

2. **Customize Data**:
   - Retrieves all theme modifications from the active theme's Customize settings.

3. **JSON File Creation**:
   - The collected data is converted into a structured JSON file.
   - The JSON file is saved in the `wp-content/uploads` directory.

### How to Use

1. Install and activate the plugin in your WordPress site.
2. Go to the WordPress admin dashboard.
3. Navigate to **Tools > Backup Elementor**.
4. Click the **Create Backup** button.
5. A JSON file containing all your Elementor and Customize data will be generated and available for download.

### Installation

1. Download the plugin files and upload them to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to **Tools > Backup Elementor** to start using the plugin.

### License

This plugin is licensed under the [MIT License](LICENSE).

## Support

If you have any issues with the plugin, feel free to open an issue on [GitHub](https://github.com/your-repository-link).

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Credits

- **Author**: Your Name
- **Version**: 1.0
- **License**: MIT

