<?php
function admin_info_tip(string $text, string $label = 'More information'): void
{
    $safeText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $tooltipId = 'info-tip-' . bin2hex(random_bytes(4));

    echo '<span class="info-tip">'
        . '<button class="info-tip-trigger" type="button" aria-label="' . $safeLabel . '" aria-describedby="' . $tooltipId . '">'
        . '<i class="fa-solid fa-circle-info" aria-hidden="true"></i>'
        . '</button>'
        . '<span class="info-tip-content" id="' . $tooltipId . '" role="tooltip">' . $safeText . '</span>'
        . '</span>';
}
