<?php

declare(strict_types=1);

function render_file_uploader(string $action = '/upload.php', string $serverId = ''): void
{
    ?>
    <form action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="server_id" value="<?= htmlspecialchars($serverId, ENT_QUOTES, 'UTF-8') ?>">
        <label for="minecraft-file">Minecraft file</label>
        <input id="minecraft-file" name="file" type="file" required>
        <button type="submit">Upload file</button>
    </form>
    <?php
}
