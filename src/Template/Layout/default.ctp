<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
    
	<link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
	<link rel="icon" type="image/png" sizes="96x96" href="assets/img/favicon.png">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

	<title><?= $this->fetch('title'); ?></title>

	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <meta name="viewport" content="width=device-width" />


    <!-- Bootstrap core CSS     -->
<!--    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />-->

    <!-- Animation library for notifications   -->
<!--    <link href="assets/css/animate.min.css" rel="stylesheet"/>-->

    <!--  Paper Dashboard core CSS    -->
<!--    <link href="assets/css/paper-dashboard.css" rel="stylesheet"/>-->

    <!--  CSS for Demo Purpose, don't include it in your project     -->
    <!--    <link href="assets/css/demo.css" rel="stylesheet" />-->

    <!--  Fonts and icons     -->
    <link href="http://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Muli:400,300' rel='stylesheet' type='text/css'>
<!--    <link href="assets/css/themify-icons.css" rel="stylesheet">-->
    <?//= $this->Html->meta('icon') ?>

    <?= $this->Html->css('/yummy/css/bootstrap.min.css') ?>

    <?= $this->Html->css('/yummy/css/animate.min.css') ?>

    <?= $this->Html->css('/yummy/css/paper-dashboard.css') ?>

    <?= $this->Html->css('/yummy/css/themify-icons.css') ?>

    <?= $this->fetch('meta') ?>

    <?php
        $this->Html->script('/yummy/js/jquery-1.10.2.js', array('inline' => false, 'block' => 'script_top'));
        $this->Html->script('/yummy/js/bootstrap.min.js', array('inline' => false, 'block' => 'script_bottom'));
        $this->Html->script('/yummy/js/bootstrap-checkbox-radio.js', array('inline' => false, 'block' => 'script_bottom'));
        $this->Html->script('/yummy/js/chartist.min.js', array('inline' => false, 'block' => 'script_bottom'));
        $this->Html->script('/yummy/js/bootstrap-notify.js', array('inline' => false, 'block' => 'script_bottom'));
        $this->Html->script('/yummy/js/paper-dashboard.js', array('inline' => false, 'block' => 'script_bottom'));
        //$this->Html->script('https://maps.googleapis.com/maps/api/js', array('inline' => false, 'block' => 'script_bottom'));
        echo $this->fetch('script_top');
    ?>

</head>
<body>

<div class="wrapper">
	<div class="sidebar" data-background-color="white" data-active-color="danger">

    <!--
		Tip 1: you can change the color of the sidebar's background using: data-background-color="white | black"
		Tip 2: you can change the color of the active button using the data-active-color="primary | info | success | warning | danger"
	-->

    	<div class="sidebar-wrapper">
            <div class="logo">
                <a href="http://www.creative-tim.com" class="simple-text">
                    Yummy
                </a>
            </div>

            <?php echo $this->element('Template/nav'); ?>
    	</div>
    </div>

    <div class="main-panel">
		<nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar bar1"></span>
                        <span class="icon-bar bar2"></span>
                        <span class="icon-bar bar3"></span>
                    </button>
                    <a class="navbar-brand" href="#"><?= $this->fetch('title') ?></a>
                </div>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav navbar-right">
                        <li>
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="ti-panel"></i>
								<p>Stats</p>
                            </a>
                        </li>
                        <li class="dropdown">
                              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="ti-bell"></i>
                                    <p class="notification">5</p>
									<p>Notifications</p>
									<b class="caret"></b>
                              </a>
                              <ul class="dropdown-menu">
                                <li><a href="#">Notification 1</a></li>
                                <li><a href="#">Notification 2</a></li>
                                <li><a href="#">Notification 3</a></li>
                                <li><a href="#">Notification 4</a></li>
                                <li><a href="#">Another notification</a></li>
                              </ul>
                        </li>
						<li>
                            <a href="#">
								<i class="ti-settings"></i>
								<p>Settings</p>
                            </a>
                        </li>
                    </ul>

                </div>
            </div>
        </nav>


        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <?= $this->Flash->render() ?>
                    <?= $this->fetch('content') ?>
                </div>
            </div>
        </div>


        <footer class="footer">
            <div class="container-fluid">
                <nav class="pull-left">
                    <ul>
                        <li>
                            <a href="/yummy">
                                Home
                            </a>
                        </li>
                    </ul>
                </nav>
				<div class="copyright pull-right">
                    &copy; <script>document.write(new Date().getFullYear())</script>, Developed by <a href="http://www.cnizz.com">Chris Nizzardini</a> - not affiliated with Creative Tim
                </div>
            </div>
        </footer>

    </div>
</div>


</body>
    
    <?= $this->fetch('script_bottom') ?>
    
</html>
