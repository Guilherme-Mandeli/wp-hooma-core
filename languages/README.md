# Localization Instructions

This plugin uses WP-CLI to manage translations. The `languages` directory contains the master POT file and the PO/MO files for each language.

## Prerequisites

-   **WP-CLI**: Ensure you have WP-CLI installed.
-   **PHP**: Ensure PHP is available in your system path.
-   **mbstring Extension**: The `mbstring` extension must be enabled in your `php.ini` for reliable string extraction.

## Workflow

### 1. Generate POT File
To update the master `.pot` file from the source code:

```bash
# General command
wp i18n make-pot . languages/hooma.pot

# If you need to explicitly enable mbstring (e.g., on Windows with a custom PHP setup):
php -d extension=mbstring path/to/wp-cli.phar i18n make-pot . languages/hooma.pot
```

### 2. Update PO Files
After updating the POT file, merge the changes into your existing PO files (e.g., `hooma-es_ES.po`, `hooma-pt_BR.po`). You can use a tool like **Poedit** (Catalog > Update from POT file) or WP-CLI.

### 3. Compile MO Files
To generate the binary `.mo` files used by WordPress:

```bash
# Compile a specific file
wp i18n make-mo languages/hooma-es_ES.po
wp i18n make-mo languages/hooma-pt_BR.po

# Or with the explicit php configuration:
php -d extension=mbstring path/to/wp-cli.phar i18n make-mo languages/hooma-es_ES.po
```

## Directory Structure
-   `hooma.pot`: The template file containing all translatable strings.
-   `hooma-es_ES.po` / `.mo`: Spanish (Spain) translation.
-   `hooma-pt_BR.po` / `.mo`: Portuguese (Brazil) translation.
