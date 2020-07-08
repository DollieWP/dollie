<div class="dollie-notice">
    <h1><span class="dashicons dashicons-admin-tools"></span> Dollie Tools</h1>
</div>
<div>
    <br><br>
    <label><strong>Synchronize Your Deployed Containers</strong></label><br><br>
    <form method="post"><input type="submit" name="synchronize" class="button" value="Start Sync!"/></form>
    <p>By clicking the button below you can synchronize all containers that have been deployed through this
        installation. This is especially useful if you have accidentally lost data or simply wanted to re-import your
        deployed containers in a fresh Dollie installation.</p>
</div>

<?php if ( ! empty( $containers ) ): ?>
    Synchronized <?php echo count( $containers ); ?> containers<br><br><br>

    <table>
        <tr>
            <th>Name</th>
            <th>URL</th>
            <th>Status</th>
        </tr>

		<?php foreach ( $containers as $container ): ?>
			<?php

			if ( ! $container['uri'] ) {
				continue;
			}

			$full_url        = parse_url( $container['uri'] );
			$stripped_domain = explode( '.', $full_url['host'] );
			$name            = $stripped_domain[0];

			?>

            <tr>
                <td><?php echo $name; ?></td>
                <td><?php echo $container['uri']; ?></td>
                <td><?php echo $container['status']; ?></td>
            </tr>
		<?php endforeach; ?>
    </table>
<?php else: ?>
    <p>No containers found to be synchronized.</p>
<?php endif; ?>
