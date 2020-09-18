<?php

require_once('../../../config.php');
$param = $_SERVER['QUERY_STRING'];

$PAGE->set_context(context_system::instance());

$bodyclasses = array();

// Outputs the standard html doctype
echo $OUTPUT->doctype();

?>

<html lang="port" class="no-js">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $COURSE->fullname; ?></title>
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="author" content="Codely Tecnologia" />

    <link rel="icon" href="<?php echo $PAGE->theme->setting_file_url('favicon', 'favicon'); ?>">

    <!-- google font -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700|Rubik:400,500" rel="stylesheet">

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css">

    <!--Material Icon -->
    <link rel="stylesheet" type="text/css" href="css/materialdesignicons.min.css" />
    <link rel="stylesheet" type="text/css" href="css/fontawesome.css" />

    <!-- selectize css -->
    <link rel="stylesheet" type="text/css" href="css/selectize.css" />

    <!-- E-disciplina Css -->
    <link rel="stylesheet" type="text/css" href="css/edisciplina.css" />

</head>

<body>

    <!-- Navigation Bar-->
    <header id="topnav" class="defaultscroll scroll-active">
        <div class="container">
            <div>
                <a href="javascript: void(0);" class="logo">
                    <img src="images/logo-light.png" alt="" class="logo-light" height="45" />
                    <img src="images/logo-dark.png" alt="" class="logo-dark" height="45" />
                </a>
            </div>
            <div class="menu-extras">

                <div class="menu-item">
                    <!-- Mobile menu toggle-->
                    <a class="navbar-toggle">
                        <div class="lines">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </a>
                    <!-- End mobile menu toggle-->
                </div>
            </div>

            <div id="navigation">
                <ul class="navigation-menu">
                    <li class="active">
                        <a href="<?php echo $CFG->wwwroot; ?>/acessar/disciplinas.php">Disciplinas</a>
                    </li>
                    <li class="lock-icon last-elements">
                        <a href="#"><i class="mdi mdi-lock"></i></a>
                    </li>
                    <a href="<?php echo $CFG->wwwroot; ?>/auth/shibboleth" class="btn btn-custom btn-sm"><i class="mdi mdi-login"></i> Acessar</a>
                </ul>
				
            </div>
			<div class="lock">
				<form role="form" method="post" action="<?php echo $CFG->wwwroot; ?>/login/index.php" >
					<div class="text-center lock-form">
						<input class="registration-input-box btn-sm text-center" type="text" name="username" id="username" placeholder="usuário antigo" class="username" style="width: auto;padding: 3px;">
                        <input class="registration-input-box btn-sm text-center" type="password" name="password" id="password" placeholder="senha antiga" class="password" style="width: auto;padding: 3px;">
                        <button type="submit" class="btn btn-sm btn-custom" style="font-size: 10px;height: 35px;margin-top: -2px; width: 70px;padding: 3px;">Acessar</button>
					</div>
					<span class="lock-close"><i class="fa fa-times"></i></span>
				</form>
			</div>
        </div>
    </header>
	
    <!-- End Navigation Bar-->

    <section class="bg-home">
        <div class="bg-overlay"></div>
		<div class="top-bar d-lg-none pt-1 pb-1">
			<div class="container">
				  <div class="row">       
						<div class="col-md-12">
							<a href="<?php echo $CFG->wwwroot; ?>/auth/shibboleth" class="btn btn-custom btn-sm btn-block"><i class="mdi mdi-login"></i> Acessar</a>
						</div>
				  </div>
			</div>
		</div>
        <div class="home-center">
            <div class="home-desc-center">
                <div class="container">			
                    <div class="row justify-content-center">
                        <div class="col-lg-9">
                            <div class="home-title text-center text-white">
                                <h1 class="mb-4">Disciplinas da USP</h1>
								  <h5 class="small-title text-uppercase f-17 text-white-50 mb-4">Ambiente virtual de apoio à graduação e pós-graduação</h5>
                            </div>
                        </div>
                    </div>
                    <div class="home-form-position">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="home-registration-form p-4 mb-3">
                                    <form class="registration-form" action="disciplinas.php" method="post">
                                        <div class="row">
                                            <div class="col-lg-9 col-md-6">
                                                <div class="registration-form-box">
                                                    <i class="fa fa-search"></i>
                                                    <input name="search" type="text" id="exampleInputName1" class="form-control registration-input-box" placeholder="<?php print get_string('typehere','theme_edis');?>">
                                                </div>
                                            </div>
                                            <div class="col-lg-3 col-md-6">
                                                <div class="registration-form-box">
                                                    <input type="submit" id="submit" name="send" class="submitBnt btn btn-custom btn-block" value="<?php print get_string('search');?>">
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php
    echo get_config('theme_edis', 'unloggedfeature1');
    echo get_config('theme_edis', 'unloggedabout');
    echo get_config('theme_edis', 'unloggedfooter');
    ?>

    <!-- footer-alt start -->
    <section class="footer-alt pt-3 pb-3">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <p class="copyright mb-0">Universidade de São Paulo | e-Disciplinas | <?php echo date("Y"); ?></p>
                </div>
            </div>
        </div>
    </section>
    <!-- footer-alt end -->

    <!-- javascript -->
	<script src="js/jquery.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery.easing.min.js"></script>
	<script src="js/isotope.pkgd.min.js"></script>
    <script src="js/plugins.js"></script>

    <!-- selectize js -->
    <script src="js/selectize.min.js"></script>
    <script src="js/jquery.nice-select.min.js"></script>
	
    <script src="js/app.js"></script>

</body>
</html>