# Includes

Place commonly reused files and templates in this directory.

Examples include shared PHP setup, navigation, headers, footers, forms, and other partial templates. Keep page-specific markup and logic in the files that use these includes.

`file-uploader.php` contains the reusable upload form. Upload validation and storage are handled by `public/upload.php`; do not bypass that endpoint from a template.
