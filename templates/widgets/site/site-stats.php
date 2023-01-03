<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

wp_print_scripts( 'chartjs' );
?>
<div class="shadow-lg rounded-lg overflow-hidden">
    <canvas class="p-10" id="<?php echo esc_attr( $chart_id );?>"></canvas>
</div>

<!-- Chart line -->
<script>
    var chartLine = new Chart(
        document.getElementById("<?php echo esc_attr( $chart_id );?>"),
        {
            type: "line",
            data: {
                labels: <?php echo $labels; ?>,
                datasets: <?php echo $datasets; ?>
            },
            options: {},
        }
    );
</script>
