<?php
namespace TenUp\A1D_Monitoring_and_Management\Core;
// Restrict this page to admins only
// TODO add granular access to editors, authors
if ( ! current_user_can( 'manage_options' ) && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
  wp_redirect( site_url() ); 
  exit;
}
// Include ajax functions
include_once( __DIR__ . '/../functions/monitor-ajax.php' );
// Include single monitor functions
include_once( __DIR__ . '/../functions/custom-single-page.php' );

wp_head() ?>
</head>
<body>
  <div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
    <?php a1dmonitor_build_sidenav( a1dmonitor_monitors() ); ?>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
      This is the main content
    </div>
  </div>


<?php wp_footer(); ?>
</body>
</html>
