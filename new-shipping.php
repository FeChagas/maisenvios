<!DOCTYPE html>
<!--
Template Name: Tinker - HTML Admin Dashboard Template
Author: Left4code
Website: http://www.left4code.com/
Contact: muhammadrizki@left4code.com
Purchase: https://themeforest.net/user/left4code/portfolio
Renew Support: https://themeforest.net/user/left4code/portfolio
License: You must have a valid license purchased only from themeforest(the above link) in order to legally use the theme for your project.
-->
<html lang="en" class="light">
    <!-- BEGIN: Head -->
    <?php include 'includes/head.php'; ?>
    
    <!-- END: Head -->
    <body class="main">
        <div class="flex overflow-hidden">
            <!-- BEGIN: Side Menu -->
            <?php include 'includes/sidebar.php'; ?>
            <!-- END: Side Menu -->
            <!-- BEGIN: Content -->
            <div class="content ">
                <!-- BEGIN: Top Bar -->
                <div class="top-bar -mx-4 px-4 md:mx-0 md:px-0">
                    <!-- BEGIN: Breadcrumb -->
                    <div class="-intro-x breadcrumb mr-auto hidden sm:flex"> <a href="">Mais envios</a> <i data-feather="chevron-right" class="breadcrumb__icon"></i> <a href="" class="breadcrumb--active">Nova transportadora</a> </div>
                    <!-- END: Breadcrumb -->
                </div>
                <!-- END: Top Bar -->
                <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
                    <h2 class="text-lg font-medium mr-auto">
                      Mais envios
                    </h2>
                    <!-- <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                        <button class="btn btn-primary shadow-md mr-2">Novo usuário</button>
                    </div> -->
                </div>
                <!-- BEGIN: HTML Table Data -->
                <?php include 'middle/new-shipping.php'; ?>
                <!-- END: HTML Table Data -->
            </div>
            <!-- END: Content -->
        </div>
        <!-- BEGIN: JS Assets-->
        <?php include 'includes/scripts.php'; ?>
        <script src="dist/routes/new-shipping.js?<?php echo $_ENV['HOST_URL']; ?>"></script>
        <!-- END: JS Assets-->
    </body>
</html>