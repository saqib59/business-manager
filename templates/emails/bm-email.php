<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php echo wp_kses_post( $message ); ?>
</body>
</html>
